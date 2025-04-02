<?php
/**
 * Created by PhpStorm.
 * User: 585
 * Date: 3/7/2025
 * Time: 6:45 AM
 */

namespace App\Models\Enums;

enum SummaryPdfRequestState : int {
    case SUMMARY_PDF_REQUEST_STATE_0_INIT = 0;
    case SUMMARY_PDF_REQUEST_STATE_1_PDF_PARSED = 1;
    case SUMMARY_PDF_REQUEST_STATE_2_SUMMARY_RECEIVED = 2;
    case SUMMARY_PDF_REQUEST_STATE_3_PDF_DELETED = 3;
}