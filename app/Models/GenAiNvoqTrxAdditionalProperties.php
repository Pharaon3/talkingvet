<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenAiNvoqTrxAdditionalProperties extends Model
{
//    use HasFactory;

    protected $fillable = [
        "clientVendor",
        "topicVersion",
        "clientProduct",
        "clientVersion",
        "serverVersion",
        "gatewayHostname",
        "requestProtocol",
        "subscriptionRequest",
        "gaj",
        "gajstate",
        "gajbody",
        "inputTokenUsage",
        "outputTokenUsage",
    ];
}
