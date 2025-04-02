<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Handling string primary keys
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'reviewId';

    protected $fillable = [
        'id',
        'reviewId',
        'submitTime',
        'realUserName',
        'audioLength',
        'audio', /* Custom Field Contains:
                    'audioType', // contentType header of audio response from nvoq e.g. "audio/ogg"
                    'audioData', // custom field for audio data to be fetched and posted to able player
                */

        //'audioUrl', // can be manually generated using -> {+server}/SCVmcServices/rest/transactions/{id}/audio
        'wordCount',
        'itemType', // one of TransactionItemType
        'externalId',
        'audioQuality',
        'status',
        'accuracy',
        'reviewedBy',
    ];

    public $timestamps = false;

    protected $casts = [
        'submit_time' => 'datetime',
    ];
}
