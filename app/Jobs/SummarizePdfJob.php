<?php

namespace App\Jobs;

use App\Extension\GenAIConfig;
use App\Models\Encounter;
use App\Models\Prompt;
use App\Models\SummaryPdfRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Enums\SummaryPdfRequestState;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Smalot\PdfParser\Parser;

class SummarizePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = GenAIConfig::JOB_QUEUE_RETRY_COUNT;
    public $max_exception = 1;
    public $timeout = GenAIConfig::JOB_QUEUE_TIMEOUT;

    protected SummaryPdfRequest $summary_pdf_request;
    /**
     * Create a new job instance.
     */
    public function __construct(SummaryPdfRequest $summary_pdf_request)
    {
        $this->summary_pdf_request = $summary_pdf_request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = $this->summary_pdf_request;

        if($job->state->value < SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_0_INIT->value){
            $this->_fail('Job state is not right.');
            return;
        }

        if($job->state == SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_0_INIT){
            $pdf_location = $job->pdf_location;
            if(is_dir($pdf_location)){
                $pdf_files = glob($pdf_location . '/*.pdf');

                if(!empty($pdf_files)){
                    $whole_text = '';
                    foreach ($pdf_files as $pdf_file){
                        $parser = new Parser();
                        $pdf = $parser->parseFile($pdf_file);
                        $text = $pdf->getText();
                        $whole_text .= $text;
                    }
                    $job->parsed_text = $whole_text;
//                    Log::debug('History Summary - parsed text: ' . $whole_text);
                    $job->state = SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_1_PDF_PARSED;
                    $job->save();
                }
                else{
                    $this->_fail('No PDF files found in the directory.');
                    return;
                }
            }else{
                $this->_fail('The directory does not exist.');
                return;
            }
        }

        if($job->state == SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_1_PDF_PARSED){
            $encounter = Encounter::find($job->encounter_id);
            $prompt = "Summarize the attached reports and output the format using the following headings: Summary, Referrers, Physical Exams, Prior Issues, Prior Vaccines, Prior Medications, Additional Information";
            $full_prompt = $prompt . "\n\nThe texts to analyze:\n" . $job->parsed_text;

            try {
                // Make the API call to OpenAI

                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a veterinary AI assistant specialized in creating summary from text extracted from pdf files.'
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
                        Log::info('History Summary - Processed message: ' . $message);
                    }
                    Log::debug('History Summary - OpenAI response content: ' . $summary);
                    $encounter = Encounter::find($job->encounter_id);
                    $encounter->history_summary = $summary;
                    $encounter->save();
                    $job->state = SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_2_SUMMARY_RECEIVED;
                    $job->save();
                } else {
                    Log::debug('OpenAI response missing expected content');
                    SummarizePdfJob::dispatch($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
                    return;
                }

            } catch (\Exception $e) {
                Log::debug('OpenAI generate history summary: ' . $e->getMessage());
                SummarizePdfJob::dispatch($job)->delay(now()->addSeconds(GenAIConfig::JOB_QUEUE_RETRY_DELAY_S));
                return;
            }
        }

        if($job->state == SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_2_SUMMARY_RECEIVED){
            $this->_cleanup();
        }
    }

    private function _fail(string $msg)
    {
        $job = $this->summary_pdf_request;
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
        $job = $this->summary_pdf_request;
        $job->retries += 1;
        $job->error_msg = "Last state: (" . $job->state . ") | Release Msg: " . $retry_msg . '\n' . $job->error_msg;
        $job->save();
    }

    private function _logError(string $error_msg, bool $softError = false)
    {
        $job = $this->summary_pdf_request;
        if (!$softError) $job->error += 1;
        $job->error_msg = "Last state: (" . $job->state->value . ") | Error: " . $error_msg . '\n' . $job->error_msg;
        $job->save();
    }

    private function _cleanup()
    {
        $job = $this->summary_pdf_request;
        $job->error = 0;
        $job->save();
        $this->_deletelocal_pdf_fileIfExist();
        $job->delete();
    }

    private function _deletelocal_pdf_fileIfExist()
    {
        $job = $this->summary_pdf_request;
        $pdf_location = $job->pdf_location;
        if (is_dir($pdf_location)) {
            $pdf_files = glob($pdf_location . '/*.pdf');
            if (!empty($pdf_files)) {
                foreach ($pdf_files as $pdf_file) {

                    // Delete the PDF file
                    if (unlink($pdf_file)) {
                        Log::debug('History Summary:' . "Deleted file: " . basename($pdf_file) . "\n");
                    } else {
                        Log::debug("Error deleting file: " . basename($pdf_file) . "\n");
                    }
                }
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $msg = $exception->getMessage();
        $this->_logError($msg);
    }
}
