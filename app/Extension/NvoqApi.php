<?php

namespace App\Extension;

class NvoqApi
{


    //region Guest API calls

    public const GUEST_LOGIN = "{+server}/SCVmcServices/login";
    public const GUEST_EMAIL_RESET_PASS_LINK = "{+server}/SCVmcServices/rest/recovery";
    //endregion




    //region Authenticated API calls
    public const AUTH_GET_ACC_PROPERTIES = "{+server}/SCVmcServices/rest/accounts/{username}/properties";
    public const AUTH_GET_ACCOUNTS = "{+server}/SCVmcServices/rest/accounts";
    public const AUTH_CHANGE_PASSWORD = "{+server}/SCVmcServices/rest/accounts/{username}/password";
    public const AUTH_GET_TRANSACTIONS = "{+server}/SCVmcServices/rest/transactions";
    public const AUTH_GET_SINGLE_TRANSACTION_DATA = "{+server}/SCVmcServices/rest/transactions/{trxId}";

    //region Nvoq Transactions Review API calls
    public const AUTH_GET_AUDIO_FOR_TRANSACTION = "{+server}/SCVmcServices/rest/transactions/{trxId}/audio";
    public const AUTH_REVIEW_GET_ORIGINAL_TEXT = "{+server}/SCVmcServices/rest/transactions/{id}/originalText";
    public const AUTH_REVIEW_GET_SUBSTITUTED_TEXT = "{+server}/SCVmcServices/rest/transactions/{id}/substitutedText";
    public const AUTH_REVIEW_GET_CORRECTED_TEXT = "{+server}/SCVmcServices/rest/transactions/{id}/correctedText";
    public const AUTH_REVIEW_POST_CORRECTION = "{+server}/SCVmcServices/rest/transactions/{id}/correction";
    public const AUTH_TRX_ADD_ADDITIONAL_PROPERTIES = "{+server}/SCVmcServices/rest/transactions/{id}/additionalProperties";
    public const AUTH_REVIEW_POST_ADD_VOCAB = "{+server}/SCVmcServices/rest/accounts/{username}/vocabulary";
    //endregion

    //endregion


}
