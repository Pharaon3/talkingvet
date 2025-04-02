<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Transaction */
class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reviewId' => $this->reviewId,
            'submitTime' => $this->submitTime,
            'realUserName' => $this->realUserName,
            'audioLength' => $this->audioLength,
            'wordCount' => $this->wordCount,
            'externalId' => $this->externalId,
            'audioQuality' => $this->audioQuality,
            'audioUrl' => $this->audioUrl,
            'originalTextUrl' => $this->originalTextUrl,
            'correctedTextUrl' => $this->correctedTextUrl,
            'substitutedTextUrl' => $this->substitutedTextUrl,
        ];
    }
}
