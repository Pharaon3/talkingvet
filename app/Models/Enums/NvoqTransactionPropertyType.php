<?php

namespace App\Models\Enums;

enum NvoqTransactionPropertyType : string
{
    case IS_GEN_AI_JOB = "gaj";
    case GEN_AI_JOB_STATE = "gajstate";
    case GEN_AI_JOB_BODY = "gajbody";
    case GEN_AI_JOB_SUMMARY_TOKEN_USAGE = "summaryTokenUsage";
    case GEN_AI_JOB_INPUT_TOKEN_USAGE = "inputTokenUsage";
    case GEN_AI_JOB_OUTPUT_TOKEN_USAGE = "outputTokenUsage";
}
