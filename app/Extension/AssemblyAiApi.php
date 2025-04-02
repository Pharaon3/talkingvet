<?php

namespace App\Extension;

class AssemblyAiApi
{


    //region Guest API calls


    //endregion


    //region Authenticated API calls
    public const AUTH_POST_UPLOAD_AUDIO = "https://api.assemblyai.com/v2/upload";
    public const AUTH_POST_TRANSCRIBE_AUDIO = "https://api.assemblyai.com/v2/transcript";
    public const AUTH_GET_TRANSCRIPT = "https://api.assemblyai.com/v2/transcript/{transcript_id}";
    public const AUTH_DELETE_TRANSCRIPT = "https://api.assemblyai.com/v2/transcript/{transcript_id}";

    //endregion


}
