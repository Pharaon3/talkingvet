<?php

namespace App\Models\Enums;

enum GenAiState : int
{
    case GEN_AI_NEW = 0;
    case GEN_AI_READY = 1;
    case GEN_AI_REVIEWED = 2;
    case GEN_AI_ERROR = 9;
}
