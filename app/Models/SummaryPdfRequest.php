<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\SummaryPdfRequestState;

class SummaryPdfRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'pdf_location',
        'parsed_text',
        'state',
        'valid',
        'retries',
        'error',
        'error_msg',
        'submit_time'
    ];

    protected $casts = [
        'state' => SummaryPdfRequestState::class,
        'valid' => 'boolean',
        'retries' => 'integer',
        'error' => 'integer',
        'submit_time' => 'datetime'
    ];
}
