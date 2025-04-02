<?php

namespace App\Models\Enums;

enum DemoJobState : int
{
    case DEMO_JOB_NOT_SET = 0;
    case DEMO_JOB_SET = 1;
}