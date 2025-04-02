<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyAiTranscribeRequest extends Model
{
    protected $fillable = [
        // The URL of the audio or video file to transcribe.
        'audio_url',
//        'audio_end_at',
//        'audio_start_from',
//        'auto_chapters',
//        'auto_highlights',
//        'boost_param',
//        'content_safety',
//        'custom_spelling',
//        'custom_topics',

        // Transcribe Filler Words, like "umm", in your media file; can be true or false
//        'disfluencies',
//        'dual_channel',
//        'entity_detection',
//        'filter_profanity',
        // Enable Text Formatting, can be true or false
//        'format_text',
//        'iab_categories',

        // https://www.assemblyai.com/docs/api-reference/transcripts/submit#request.body.language_code
//        'language_code',
//        'language_detection',
//        'punctuate',
//        'redact_pii',
//        'redact_pii_audio',
//        'redact_pii_audio_quality',
//        'redact_pii_policies',
//        'redact_pii_sub',
//        'sentiment_analysis',

        // Enable Speaker diarization, can be true or false
        'speaker_labels',

        // Tells the speaker label model how many speakers it should attempt to identify,
        // up to 10. See Speaker diarization for more details.
//        'speakers_expected',
//        'speech_threshold',
//        'summarization',
//        'summary_model',
//        'summary_type',
//        'topics',
//        'webhook_auth_header_name',
//        'webhook_auth_header_value',
//        'webhook_url',
//        'word_boost',
    ];

    protected $casts = [
//        'auto_chapters' => 'boolean',
//        'auto_highlights' => 'boolean',
//        'content_safety' => 'boolean',
//        'custom_topics' => 'boolean',
//        'disfluencies' => 'boolean',
//        'dual_channel' => 'boolean',
//        'entity_detection' => 'boolean',
//        'filter_profanity' => 'boolean',
//        'format_text' => 'boolean',
//        'iab_categories' => 'boolean',
//        'language_detection' => 'boolean',
//        'punctuate' => 'boolean',
//        'redact_pii' => 'boolean',
//        'redact_pii_audio' => 'boolean',
//        'sentiment_analysis' => 'boolean',
        'speaker_labels' => 'boolean',
//        'summarization' => 'boolean',
    ];

    /* defaults */
    protected $attributes = [
//        'boost_param' => 'high',
        'speaker_labels' => true,
//        'speakers_expected' => 2
    ];

}
