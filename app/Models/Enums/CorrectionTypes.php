<?php

namespace App\Models\Enums;

enum CorrectionTypes : string
{
    case NEW = "new";
    case CORRECTED = "corrected";
    case REJECTED = "rejected";
    case CLEAR = "clear";
}
