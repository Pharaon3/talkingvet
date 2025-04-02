<?php

namespace App\Models\Enums;

enum ModalActions :int{
    case NONE = 0;
    case NEXT = 1;
    case PREV = 2;
    case CLOSE = 3;
//    case CORRECTION_SUCCESS = 4;
    case CORRECT_OR_REJECT = 5;
}
