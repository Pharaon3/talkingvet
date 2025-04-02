<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGptSummaryResponseModel extends Model
{
    protected $fillable = [
        'response',
        'token_usage',
    ];

    public function GetSummary()
    {
        return $this->response;
    }

    public function GetTokensUsage()
    {
        return $this->token_usage;
    }

    public function GetPromptTokens()
    {
        $data = $this->token_usage;
        if(isset($data['prompt_tokens']))
        {
            return $data['prompt_tokens'];
        }
        else
        {
            return 0;
        }
    }

    public function GetCompletionTokens()
    {
        $data = $this->token_usage;
        if(isset($data['completion_tokens']))
        {
            return $data['completion_tokens'];
        }
        else
        {
            return 0;
        }
    }

    public function GetTotalTokens()
    {
        $data = $this->token_usage;
        if(isset($data['total_tokens']))
        {
            return $data['total_tokens'];
        }
        else
        {
            return 0;
        }
    }

}
