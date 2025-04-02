<?php

namespace App\Models\Enums;

enum ValidURLSchemes : string
{
    case TVM = "tvm";
    case VVM = "vvm";
    case TVMM = "tvmm";
    case VVMM = "vvmm";
}
