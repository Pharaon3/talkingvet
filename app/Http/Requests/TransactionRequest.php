<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reviewId' => ['required'],
            'submitTime' => ['required', 'date'],
            'realUserName' => ['required'],
            'audioLength' => ['required', 'numeric'],
            'wordCount' => ['required', 'integer'],
            'externalId' => ['required'],
            'audioQuality' => ['required', 'integer'],
            'audioUrl' => ['nullable'],
            'originalTextUrl' => ['nullable'],
            'correctedTextUrl' => ['nullable'],
            'substitutedTextUrl' => ['nullable'],
        ];
    }
}
