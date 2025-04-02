<?php

namespace App\Jobs;

use App\Extension\AssemblyAiApi;
use App\Extension\GenAIConfig;
use App\Extension\HelperFunctions;
use App\Http\Controllers\NvoqNetworkController;
use App\Jobs\Middleware\GenAIAuth;
use App\Models\AssemblyAiTranscribeRequest;
use App\Models\Enums\GenAIRequestState;
use App\Models\Enums\GenAiState;
use App\Models\GenAIRequest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class ProcessMultispeakerDictation implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted (after/by release)
     *
     * @var int
     */
    public $tries = GenAIConfig::JOB_QUEUE_RETRY_COUNT;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     * Fails immediately - goes to failed_jobs table
     *
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * The number of seconds the job can run before timing out.
     * Job goes to failed immediately after time out
     *
     * @var int
     */
    public $timeout = GenAIConfig::JOB_QUEUE_TIMEOUT;

    protected GenAIRequest $genAIRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(GenAIRequest $genAIRequest)
    {
        $this->genAIRequest = $genAIRequest;
    }

    /**
     * Execute the job.
     * @note https://www.assemblyai.com/docs/api-reference/transcripts/submit
     */
    public function handle(): void
    {
//        $job = $this->genAIRequest;
//        $this->release();
//        $this->_fail("test fail " . Carbon::now());

//        throw new \Exception("test");
//        $this->_release( 4, "test R" . Carbon::now());
//        $this->_fail("test F" . Carbon::now());

//        return;

        $job = $this->genAIRequest;
        // use $this->genAIRequest->getAttributes() not the one in args

        //region Pre-conditions
        /* starting from state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_2_DOWNLOADED_TRX_AUDIO */
        if($job->state->value < GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_1_TRX_ID_PARSED->value)
        {
            // fail
            $this->_fail("Job state is not (1 - TRX_ID_PARSED)");
            return;
        }
        /* else */

        //endregion

        /** 2. Try to re-download audio file if it doesn't exist */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_1_TRX_ID_PARSED)
        {
            /* check if audio is already downloaded */
            $isLocalAudioFileExists = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->exists($job->localAudioFile);
            if(!$isLocalAudioFileExists)
            {
                // try to re-download audio file
                $nvoqTrxAudioLocation = $job->audioLocation;

                $resultPath = HelperFunctions::DownloadGenAiAudio($nvoqTrxAudioLocation,
                    true,
                    $job->country,
                    $job->trxId);

                /** 2.a. handle downloaded file response */
                if($resultPath == null)
                {
                    $this->_fail("Failed to download audio file from queue | url: " . $job->localAudioFile);
                    return;
                }
                else if($resultPath instanceof JsonResponse)
                {
                    /* error occurred */
                    $this->_fail("Failed to download audio file from queue | url: " . $job->localAudioFile .
                        " | Error response: " . json_encode($resultPath)
                    );
                    return;
                }
                else // else - continue
                {
                    // save local audio file path
                    $job->localAudioFile = $resultPath;
                    $job->save();
                }
            }

            /* update state */
            $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_2_DOWNLOADED_TRX_AUDIO;
            $job->save();
        }

        /* preconditions pass - continue with job processing */
        /** 3. upload audio to assembly AI */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_2_DOWNLOADED_TRX_AUDIO)
        { // audio downloaded, proceed with uploading audio to AI multi-speaker transcript provider (current: assembly AI)

            /** double check audio file exists */
            $isLocalAudioFileExists = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->exists($job->localAudioFile);

            $fileContents = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->get($job->localAudioFile);

            if(!$isLocalAudioFileExists)
            {
                $this->_fail("Local audio file no longer exist");
                return;
            }

            $response = Http::withBody($fileContents)
                ->withHeaders(
                    [
                        'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                        'Content-Type' => 'application/octet-stream'
                    ]
                )
                ->post(AssemblyAiApi::AUTH_POST_UPLOAD_AUDIO);


            /* bug: laravel attach doesn't work correctly now using Http::withBody instead (above) */
//            $response = Http::attach(
//                'file', $fileContents
//            )
//                ->withHeaders([
//                    'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
////                    'Content-Type' => 'audio/ogg'
////                    'Content-Type' => 'application/octet-stream'
//                ])
//                ->post(AssemblyAiApi::AUTH_POST_UPLOAD_AUDIO);

            if ($response->successful())
            {
                $json = $response->json();
                if(isset($json['upload_url']))
                {
                    $uploadUrl = $json['upload_url'];
                    $job->audioUrl = $uploadUrl;
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_3_UPLOADED_TRX_AUDIO;
                    $job->save();

                    // delete audio file from storage
                    $this->_deleteLocalAudioFileIfExist();
                }
                else
                {
                    // Handle error response
//                    $this->_release($response->status() . " | " . $response->body());
                    $this->_fail($response->status() . " | " . $response->body());
                    return;
                }
            }
            else
            {
                // Handle error response
                $this->_release($response->status() . " | " . $response->body(), GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
//                $this->_fail($response->status() . " | " . $response->body());
                return;
            }
        }

        /** 4. Request Transcription */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_3_UPLOADED_TRX_AUDIO)
        {
            // 3. send to AssemblyAI for audio to text transcription (multi speaker)
            // Assuming you have an instance of AssemblyAiRequest filled with data
            $request = new AssemblyAiTranscribeRequest([
                'audio_url' => $job->audioUrl,
            ]);

            $response = Http::withHeaders([
                'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                'Content-Type' => 'application/json',
            ])
            ->post(AssemblyAiApi::AUTH_POST_TRANSCRIBE_AUDIO, $request->toArray());


            if ($response->successful())
            {
                // Handle successful response
                $json = $response->json();
                if(!isset($json['id']))
                {
                    $this->_fail("failed to retrieve assembly AI job ID");
                    return;
                }
                else
                {
                    // get assembly AI job ID
                    $assemblyAIJobId = $json['id'];

                    /* save to DB for further usage */
                    $job->assemblyAIJobId = $assemblyAIJobId;
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_4_REQUESTED_TRANSCRIPTION;
                    $job->save();
                }
            }
            else
            {
                // retry later
                $this->_release($response->status() . " | " . $response->body(), GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
                return;
            }
        }


        /** steps 5 to 8 */
        if(
            $job->state->value >= GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_4_REQUESTED_TRANSCRIPTION->value &&
            $job->state->value < GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_7_AI_JOB_SET_AS_NEW->value
        )
        { // retrieve transaction data from nvoq

            /** 5. Get Transaction Data */
            $nvoqTransaction = NvoqNetworkController::GenAI_GetTransactionById($job->trxId);
            if($nvoqTransaction == null)
            {
                /* delete from DB permanently */
//                $job->forceDelete(); // todo delete later

//                $job->error += 1; // increment error
//                $job->save();

                $this->_release("couldn't find transaction", GenAiConfig::JOB_QUEUE_RETRY_DELAY_S);
                return;

//                if($job->error > GenAIConfig::JOB_QUEUE_RETRY_COUNT)
//                {
//                    // fail
//                    /* return API error */
//                    $this->fail("couldn't find transaction");
//                    return;
//                }
//                else
//                {
//                    // re-queue with delay
////                    ProcessMultispeakerDictation::dispatchSync($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
//                    return; // exit current job
//                }

//                return HelperFunctions::ErrorJsonResponse("couldn't find transaction", ResponseAlias::HTTP_NOT_FOUND);
            }
            else
            {
                if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_4_REQUESTED_TRANSCRIPTION) // avoid rewinds
                {
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_5_RETRIEVED_TRX_DATA;
                    $job->save();
                }
            }

            /** 6. Set as Gen AI Job */
            if ($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_5_RETRIEVED_TRX_DATA)
            {
                $result = $nvoqTransaction->SetAsGenAIJob();
                if (!$result) {

//                    $this->fail("failed to set as Gen AI job (5)");
                    $this->_release("failed to set as Gen AI job (5)", GenAIConfig::JOB_QUEUE_RETRY_MINI_DELAY_S);
                    return;

                }
                else
                { // passed
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_6_JOB_SET_AS_AI;
                    $job->save();
                }
            }

            /** 7. Set as Gen AI Job State as NEW (0) */
            if ($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_6_JOB_SET_AS_AI)
            {
                $result = $nvoqTransaction->SetGenAiJobState(GenAiState::GEN_AI_NEW);
                if (!$result)
                {

                    $this->_release("failed to set gen ai job state to new (6)", GenAIConfig::JOB_QUEUE_RETRY_MINI_DELAY_S);
                    return;
                    //                return HelperFunctions::ErrorJsonResponse("failed to set gen ai job state to new (6)", ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
                }
                else
                { // passed
                    $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_7_AI_JOB_SET_AS_NEW;
                    $job->save();
                }
            }

        } // end if


        /** step 8 get body from assembly AI */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_7_AI_JOB_SET_AS_NEW)
        { // poll for audio transcription results (current provider: assembly AI), when done save body to nvoq trx data

            $transcript_id = $job->assemblyAIJobId;

            /** fetch nvoq transaction */
            $nvoqTransaction = NvoqNetworkController::GenAI_GetTransactionById($job->trxId);
            if($nvoqTransaction == null)
            {
                $this->_fail("Transaction no longer exists on nvoq servers");
                return;
            }

//            // Job processing logic
            $request = null;

            $response = Http::withHeaders([
                'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                'Content-Type' => 'application/json',
            ])->withUrlParameters(
                [
                    'transcript_id' => $transcript_id
                ]
            )
            ->get(AssemblyAiApi::AUTH_GET_TRANSCRIPT);


            if (
                $response->successful() &&
                isset($response->json()['status'])
            )
            {
                $transcription_result = $response->json();

                /* check result */
                if ($transcription_result['status'] === "completed")
                {
//                    $transcript = $transcription_result['text'];

                    /* handle parsing transcript response */
                    $transcript = $this->_parseAssemblyAiTranscript($transcription_result);
                    if($transcript == null)
                    {
                        $this->_fail("Transcript failed to parse");
                    }

                    /** save text body to nvoq */
                    $result = $nvoqTransaction->SetGenAiJobBody($transcript);
                    if(!$result)
                    {
                        $this->_release("failed to save job body to nvoq servers", GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
                        return;
                    }
                    else
                    {
                        // job is done successfully

                        // body saved, set job new status
                        $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_8_UPDATED_JOB_BODY;
                        $job->save();
                    }
                }
                else if ($transcription_result['status'] === "error")
                {
                    /* donetodo report to nvoq transaction - handled internally */
                    $this->_fail("Transcription failed: " . $transcription_result['error']);
                    return;
                }
                else
                { // transcription is still pending/processing, delay

                    /* delay for re-poll */
                    ProcessMultispeakerDictation::dispatch($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
                    return; // exit this job, clearing retries and attempts
                }
            }
            else
            {
                // Handle error response
                $this->_release($response->status() . " | " . $response->body(), GenAIConfig::JOB_QUEUE_RETRY_DELAY_S);
//                $this->fail($response->status() . " | " . $response->body());
                return;
            }
        }

        /** step 9 delete data from AI server */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_8_UPDATED_JOB_BODY)
        {
             $response = Http::withHeaders([
                 'Authorization' => config('app.gen_ai_data.assembly_ai_token'),
                 'Content-Type' => 'application/json',
             ])->withUrlParameters(
                 [
                     'transcript_id' => $transcript_id
                 ]
             )
             ->delete(AssemblyAiApi::AUTH_DELETE_TRANSCRIPT);


            if ($response->successful())
            {
                /* update job state */
                $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_9_DELETED_DATA_FROM_AI_SERVER;
                $job->save();
            }
            else
            {
                $this->_release("failed to delete transcript from assemblyAI servers, retrying", GenAIConfig::JOB_QUEUE_RETRY_MINI_DELAY_S);
                return;
            }
        }

        /** step 10 set job as ready */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_9_DELETED_DATA_FROM_AI_SERVER)
        { // set job as ready on nvoq, remove from queue, delete request

            $result = $nvoqTransaction->SetGenAiJobState(GenAiState::GEN_AI_READY);
            if($result)
            {
                $job->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_10_AI_JOB_SET_AS_READY;

                /* clear errors - cleanup db? */
                $job->error = 0; // clear errors

                /* save job */
                $job->save();

                // unterminated - continue below
            }
            else
            {
                $this->_release("failed to set gen AI job state to 'ready'", GenAIConfig::JOB_QUEUE_RETRY_MINI_DELAY_S);
                return;
            }
        }

        /** finishing up */
        if($job->state == GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_10_AI_JOB_SET_AS_READY)
        {
            // all done - cleanup
            $this->_cleanup();
        }
        else
        { // possibly uncaught error
            $this->_release("possible unhandled situation, current state: " . $job->state->value);
            return;
        }
    }

    /** used by GenAIAuth.php
     * @see \App\Jobs\Middleware\GenAIAuth
     * */
    public function getGenAIRequest(): GenAIRequest
    {
        return $this->genAIRequest;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new GenAIAuth()];
//        return [];
    }

    /**
     * Determine the time at which the job should timeout.
     */
//    public function retryUntil(): DateTime
//    {
//        return now()->addMinutes(10);
//    }



    //region Private Helpers

    /*private function _retryOrFail(string $msg)
    {

    }*/

    /**
     * Parses Assembly AI transcript text
     * if multi-speaker is enabled (speaker_labels = true), utterances array will
     * be parsed to create the full transcription text
     *
     * @param $assemblyAiResponse
     * @return string|null Transcription text or null if failed
     */
    private function _parseAssemblyAiTranscript($assemblyAiResponse) : string|null
    {
        $result = "";

        if(isset($assemblyAiResponse["speaker_labels"]))
        {
            if (isset($assemblyAiResponse['utterances']))
            {
                foreach ($assemblyAiResponse['utterances'] as $utterance)
                {
                    $result .= "Speaker " . $utterance['speaker'] . "\n" . $utterance['text'] . "\n";
                }

                return $result;
            }
//            else
//            {
                // Handle unknown JSON format
                // Ignored to drop to next code branch
//            }
        }

        /* general return if utterances failed / not found / regular text */
        return $assemblyAiResponse['text'] ?? null;
    }

    private function _fail(string $msg)
    {
        /* save error to job */
        $job = $this->genAIRequest;

        $this->_logError(
            "Last state: (" . $job->state->value .  ") | Error: " . $msg . '\n' . $job->error_msg,
            false
        );

        // mark job as failed
        $this->fail($msg);
    }

    /**
     * releases job back to queue to re-attempt max retries is set by @see $tries
     *
     * @param int $delay_in_s delay in seconds
     * @return void
     */
    private function _release(string $msg = "", int $delay_in_s = 0): void
    {
        /* log retry */
        $this->_logRetry($msg);

        // release job to be retried immediately/with delay
        $this->release($delay_in_s);
    }

    /**
     * Logs a retry attempt to DB
     *
     * @param $retry_msg string retry message to be logged
     * @return void
     */
    private function _logRetry($retry_msg)
    {
        $job = $this->genAIRequest;
        $job->retries += 1;
        $job->error_msg = "Last state: (" . $job->state->value .  ") | Release Msg: " . $retry_msg . '\n' . $job->error_msg;
        $job->save();
    }

    /**
     * Logs an error to DB (and) to nvoq
     *
     * @param $error_msg string error message to be logged
     * @param bool $softError <br/>
     * - true: error message is added <br/>
     * - false: error message is added, nvoq transaction status is updated to GEN_AI_ERROR
     * @return void
     */
    private function _logError(string $error_msg, bool $softError = false)
    {
        $job = $this->genAIRequest;
        if(!$softError) $job->error += 1;
        $job->error_msg = "Last state: (" . $job->state->value .  ") | Error: " . $error_msg . '\n' . $job->error_msg;
        $job->save();

        if(!$softError)
        {
//            if($job->state->value >= GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_5_RETRIEVED_TRX_DATA)
//            {
                // has nvoq transaction, fetch and update error state
                $nvoqTransaction = NvoqNetworkController::GenAI_GetTransactionById($job->trxId);
                $nvoqTransaction?->SetGenAiJobState(GenAiState::GEN_AI_ERROR);
//            }
        }
    }

    private function _cleanup()
    {
        /**  delete local file */

        $job = $this->genAIRequest;
        $job->error = 0;
        $job->save();

        $this->_deleteLocalAudioFileIfExist();

        /** delete DB entry */
        $job->delete(); // soft delete
//        $job->forceDelete(); // complete deletion
    }

    private function _deleteLocalAudioFileIfExist()
    {
        $job = $this->genAIRequest;

        $isLocalAudioFileExists = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->exists($job->localAudioFile);
        if($isLocalAudioFileExists)
        {
            Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->delete($job->localAudioFile);
        }
    }

    //endregion



    //region Events and Overrides
    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();

        $this->_logError($msg);
    }

    //endregion


}
