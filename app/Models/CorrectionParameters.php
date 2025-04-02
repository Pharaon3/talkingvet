<?php

namespace App\Models;

use App\Models\Enums\CorrectionTypes;
use Illuminate\Database\Eloquent\Model;

class CorrectionParameters
{

    public CorrectionTypes $status;
    public string $text;
    public ?string $externalId;

    /**
     * @param CorrectionTypes $status (new, corrected, rejected, clear)
     * @param string $text
     * @param string|null $externalId
     */
    public function __construct(CorrectionTypes $status, string $text, ?string $externalId)
    {
        $this->status = $status;
        $this->text = $text;
        $this->externalId = $externalId;
    }

    public function all()
    {
        return [
            'status' => $this->status->value,
//            'status' => 'new',
            'text' => $this->text,
//            'external_id' => $this->externalId // not sending this again to nvoq
        ];
    }
}
