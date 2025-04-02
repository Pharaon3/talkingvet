<?php

namespace App\Jobs;

use App\Extension\AssemblyAiApi;
use App\Extension\GenAIConfig;
use App\Models\AssemblyAiTranscribeRequest;
use App\Models\Encounter;
use App\Models\GenAIRequest;
use App\Models\Enums\GenAIRequestState;
use App\Models\Prompt;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Models\GenAiInternalRequest;
use Throwable;

class ProcessEncounterTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = GenAIConfig::JOB_QUEUE_RETRY_COUNT;
    public $max_exception = 1;
    public $timeout = GenAIConfig::JOB_QUEUE_TIMEOUT;
    public $gotten_transcript;

    protected GenAiInternalRequest $gen_ai_request;

    /**
     * Create a new job instance.
     */
    public function __construct(GenAiInternalRequest $gen_ai_request)
    {
        $this->gen_ai_request = $gen_ai_request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = $this->gen_ai_request;

        if ($job->state->value < GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_1_TRX_ID_PARSED->value){
            $this->_fail("Job state is not right.");
            return;
        }

        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_1_TRX_ID_PARSED){
            $is_local_audio_file_exists = Storage::exists($job->local_audio_file);
            if(!$is_local_audio_file_exists){
                $this->_fail('Local audio file no longer exists: ' . $job->local_audio_file);
                return;
            }

            $file_contents = Storage::get($job->local_audio_file);

            // Step 1: Upload audio to Assembly AI
            $response = Http::withBody($file_contents)
                ->withHeaders([
                    'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                    'Content-Type' => 'application/octet-stream'
                ])
                ->post(AssemblyAiApi::AUTH_POST_UPLOAD_AUDIO);

            if($response->successful()){
                $json = $response->json();
                if(isset($json['upload_url'])){
                    $upload_url = $json['upload_url'];
                    $job->audio_url = $upload_url;
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_3_UPLOADED_TRX_AUDIO;
                    $job->save();

                    $this->_deletelocal_audio_fileIfExist();
                }
                else{
                    $this->_fail($response->status() . " | " . $response->body());
                    return;
                }
            }else{
                $this->_release($response->status() . "|" . $response->body(), GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
                return;
            }
        }



        // Step 2: Request Transcription
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_3_UPLOADED_TRX_AUDIO){
            $request = new AssemblyAiTranscribeRequest([
               'audio_url' => $job->audio_url
            ]);

            $response = Http::withHeaders([
               'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
               'Content-Type' => 'application/json'
            ])->post(AssemblyAiApi::AUTH_POST_TRANSCRIBE_AUDIO, $request->toArray());

            if($response->successful()){
                $json = $response->json();
                if(!isset($json['id'])){
                    $this->_fail("failed to receive assembly AI job ID");
                    return;
                }else{
                    $assembly_ai_job_id = $json['id'];
                    $job->assembly_ai_job_id = $assembly_ai_job_id;
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_4_REQUESTED_TRANSCRIPTION;
                    $job->save();
                }

            }else{
                $this->_release($response->status() . '|' . $response->body(), GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
                return;
            }
        }

        // Step 3: Fetch Transcription Result

        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_4_REQUESTED_TRANSCRIPTION){
            $transcript_id = $job->assembly_ai_job_id;
            Log::debug("start");
            Log::debug($job->assembly_ai_job_id);
            $response = Http::withHeaders([
                'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                'Content-Type' => 'application/json'
            ])->withUrlParameters(['transcript_id' => $transcript_id])
                ->get(AssemblyAiApi::AUTH_GET_TRANSCRIPT);
            Log::debug($response);
            if($response->successful() && isset($response->json()['status'])){
                $transcription_result = $response->json();

                if($transcription_result['status'] === 'completed'){
                    Log::debug('completed');
                    $transcript = $this->_parseAssemblyAiTranscript($transcription_result);

                    if(!$transcript){
                        $this->_fail('Transcript failed to parse');
                    }
                    $this->gotten_transcript = $transcript;
                    $encounter = Encounter::find($job->encounter_id);

                    if($encounter){
                        $existing_transcripts = json_decode($encounter->transcripts, true);

                        if(!is_array($existing_transcripts)){
                            $existing_transcripts = [];
                        }

                        $new_transcript = [
                          'id' => count($existing_transcripts),
                          'transcript' => $transcript,
                          'date_created' => $encounter->encounter_id
                        ];

                        $existing_transcripts[] = $new_transcript;

                        $encounter->transcripts = json_encode($existing_transcripts);
                        $encounter->save();
                    }

                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_8_UPDATED_JOB_BODY;
                    $job->save();
                } else if($transcription_result['status'] === 'error'){
                    Log::debug('error');
                    $this->_release("Transcription failed, retrying");
                    return;
                } else{
                    ProcessEncounterTranscription::dispatch($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
                    return;
                }
            } else{
                $this->_release($response->status() . '|' . $response->body(), GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
                return;
            }
        }

        // Step 4: Delete data from AI server
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_8_UPDATED_JOB_BODY){
            $response = Http::withHeaders([
                'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                'Content-Type' => 'application/json'
            ])->withUrlParameters([
                'transcript_id' => $transcript_id
            ])->delete(AssemblyAiApi::AUTH_DELETE_TRANSCRIPT);

            if($response->successful()){
                $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_9_DELETED_DATA_FROM_AI_SERVER;
            } else{
                $this->_release("Failed to delete transcript from AssemblyAI servers, retrying", GenAIConfig::JOB_QUEUE_RETRY_MINI_DELAY_S);
                return;
            }
        }

        // Step 5: Set job as ready
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_9_DELETED_DATA_FROM_AI_SERVER){
            $job->state = GenAIRequestState::INTERNALL_GEN_AI_REQ_STATE_11_SUMMARY_REQUEST;
            $job->save();
        }

        if($job->state == GenAIRequestState::INTERNALL_GEN_AI_REQ_STATE_11_SUMMARY_REQUEST){
            $encounter = Encounter::find($job->encounter_id);
            $prompt = Prompt::find($encounter->default_prompt_id);
            $prompt_text = $prompt->prompt;
            $full_prompt = $prompt_text . "\n\nTranscript to analyze:\n" . $this->gotten_transcript;
            try {
                // Make the API call to OpenAI

                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a veterinary AI assistant specialized in creating summary from transcripts.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $full_prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2048
                ]);


                if (isset($response->choices) && !empty($response->choices)) {
					$summary = '';
					foreach ($response->choices as $choice) {
						$message = $choice->message->content;
						$summary .= $message;
						Log::info('Processed message: ' . $message);
					}
                    //$summary = $response->choices[0]->message->content;
                    Log::debug('OpenAI response content: ' . $summary);
                    $encounter = Encounter::find($job->encounter_id);
                    $encounter->summary = $summary;
                    $encounter->save();
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_10_AI_JOB_SET_AS_READY;
                    $job->save();
                } else {
                    Log::debug('OpenAI response missing expected content');
                    ProcessEncounterTranscription::dispatch($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
                    return;
                }

            } catch (\Exception $e) {
                Log::debug('OpenAI generate summary: ' . $e->getMessage());
                ProcessEncounterTranscription::dispatch($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
                return;
            }

        }

        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_10_AI_JOB_SET_AS_READY){
            $this->_cleanup();
        }else{
            $this->_release("Possible unhandled situation, current state: " . $job->state);
        }
    }

    private function _parseAssemblyAiTranscript($assemblyAiResponse): ?string
    {
        $result = "";

        if (isset($assemblyAiResponse["speaker_labels"])) {
            if (isset($assemblyAiResponse['utterances'])) {
                foreach ($assemblyAiResponse['utterances'] as $utterance) {
                    $result .= "Speaker " . $utterance['speaker'] . "\n" . $utterance['text'] . "\n";
                }
                return $result;
            }
        }
        return $assemblyAiResponse['text'] ?? null;
    }

    private function _fail(string $msg)
    {
        $job = $this->gen_ai_request;
        $this->_logError("Last state: (" . $job->state->value . ") | Error: " . $msg . '\n' . $job->error_msg, false);
        $this->fail($msg);
    }

    private function _release(string $msg = "", int $delay_in_s = 0): void
    {
        $this->_logRetry($msg);
        $this->release($delay_in_s);
    }

    private function _logRetry($retry_msg)
    {
        $job = $this->gen_ai_request;
        $job->retries += 1;
        $job->error_msg = "Last state: (" . $job->state->value . ") | Release Msg: " . $retry_msg . '\n' . $job->error_msg;
        $job->save();
    }

    private function _logError(string $error_msg, bool $softError = false)
    {
        $job = $this->gen_ai_request;
        if (!$softError) $job->error += 1;
        $job->error_msg = "Last state: (" . $job->state->value . ") | Error: " . $error_msg . '\n' . $job->error_msg;
        $job->save();
    }

    private function _cleanup()
    {
        $job = $this->gen_ai_request;
        $job->error = 0;
        $job->save();
        $this->_deletelocal_audio_fileIfExist();
        $job->delete();
    }

    private function _deletelocal_audio_fileIfExist()
    {
        $job = $this->gen_ai_request;
        $islocal_audio_fileExists = Storage::exists($job->local_audio_file); // Use default disk
        if ($islocal_audio_fileExists) {
            Storage::delete($job->local_audio_file); // Use default disk
        }
    }

    public function failed(?Throwable $exception): void
    {
        $msg = $exception->getMessage();
        $this->_logError($msg);
    }
}
