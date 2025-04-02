<?php

namespace App\Models;

use App\Http\Controllers\NvoqNetworkController;
use App\Models\Enums\GenAiState;
use App\Models\Enums\NvoqTransactionPropertyType;
use Illuminate\Database\Eloquent\Model;

class GenAiNvoqTransaction extends Model
{
//    use HasFactory;

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
        'additionalProperties',
    ];

    public $timestamps = false;

    protected $casts = [
        'submit_time' => 'datetime',
    ];

    public function SetAsGenAIJob() : bool
    {
        return $this->_addAdditionalProperty(
            NvoqTransactionPropertyType::IS_GEN_AI_JOB,
            true
        );
    }

    public function SetGenAiJobState(GenAiState $genAiState) : bool
    {
        return $this->_addAdditionalProperty(
            NvoqTransactionPropertyType::GEN_AI_JOB_STATE,
            $genAiState->value
        );
    }

    public function SetGenAiJobBody(string $body) : bool
    {
        return $this->_addAdditionalProperty(
            NvoqTransactionPropertyType::GEN_AI_JOB_BODY,
            $body
        );
    }

    public function GetAdditionalProperties(): GenAiNvoqTrxAdditionalProperties
    {
        return new GenAiNvoqTrxAdditionalProperties($this->additionalProperties);
    }


    private function _addAdditionalProperty(NvoqTransactionPropertyType $propertyType, string $propertyValue) : bool
    {
        $data = [$propertyType->value => $propertyValue];
        $result = NvoqNetworkController::GenAI_AddPropertyToExistingTransaction(
            $this->id,
            $data);

        return is_array($result);
    }
}
