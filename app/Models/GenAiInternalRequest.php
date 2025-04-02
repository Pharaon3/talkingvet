<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\GenAIRequestState;

class GenAiInternalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'assembly_ai_job_id',
        'audio_location',
        'local_audio_file',
        'audio_url',
        'state',
        'valid',
        'retries',
        'error',
        'error_msg',
        'submit_time'
    ];

    protected $attributes = [
        'audio_location' => "",
        'audio_url' => "",
        'assembly_ai_job_id' => "",
        'local_audio_file' => "",
        'state' => 0,
        'submit_time' => "",
        'valid' => false,
//        'deleted_at' => "",
        'retries' => 0,
        'error' => 0,
        'error_msg' => "",
    ];

    protected $casts = [
      'submit_time' => 'datetime',
      'retries' => 'integer',
      'error' => 'integer',
      'state' => GenAIRequestState::class
    ];
}
