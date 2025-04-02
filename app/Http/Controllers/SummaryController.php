<?php

namespace App\Http\Controllers;

use App\Models\Encounter;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class SummaryController extends Controller
{

    public function re_generate(Request $request) {
        $encounter_id = $request->input('encounter_id');
        $prompt_id = $request->input('prompt_id');
        $transaction = $request->input('transaction');
        $encounter = Encounter::find($encounter_id);
        $prompt = Prompt::find($prompt_id);
        $prompt_text = $prompt->prompt;
        $full_prompt = $prompt_text . "\n\nTranscript to analyze:\n" . $transaction;
        try {
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
                Log::debug('OpenAI response content: ' . $summary);
            } else {
                Log::debug('OpenAI response missing expected content');
                return;
            }

        } catch (\Exception $e) {
            Log::debug('OpenAI generate summary: ' . $e->getMessage());
            return;
        }
    }

    public function index(Request $request, $encounter_id){
        $encounter = Encounter::find($encounter_id);
        $organizationId = $encounter->organization_id;
        $prompts = Prompt::where('prompts.organization_id', $organizationId)
            ->leftJoin('users', 'prompts.user_id', '=', 'users.id') // Join with Users table
            ->select('prompts.id', 'prompts.name', 'prompts.user_id', 'users.username') // Specify columns
            ->get();
        $prompt = Prompt::where('id', $encounter->default_prompt_id)->first() ??
            Prompt::where('system_default', true)->first();
        

        $summary_text = $encounter->summary;
        $summary_html = convert_summary_to_html($summary_text);
        $summary_sections = extract_sections($summary_text);
        $transcripts = $encounter->transcripts ? process_transcripts($encounter->transcripts):[];
        return view('summary.home', [
            'encounter' => $encounter,
            'prompts' => $prompts,
            'prompt' => $prompt,
            'summary_text' => $summary_text,
            'summary_html' => $summary_html,
            'summary_sections' => $summary_sections,
            'transcripts' => $transcripts
        ]);
    }
}
