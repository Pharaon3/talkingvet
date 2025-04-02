<?php

namespace App\Extension;

class GenAIConfig
{


    //region Configuration

    public const HEADING_CSV_FILENAME = "headings.csv";
    public const PROMPTS_JSON_FILENAME = "prompts.json";
    public const TEST_PROMPTS_JSON_FILENAME = "test_prompts.json";


    /** from 'filesystems.php' */
    public const GEN_AI_STORAGE_NAME = "genai";

    /** path within genai storage folder - to hold temporarily downloaded nvoq audio files */
    public const TEMP_AUDIO_PATH = "tmp/audio/";

    public const JOB_QUEUE_TIMEOUT = 170; // must be less than (queue.php & config.php) retry_after
    public const JOB_QUEUE_RETRY_COUNT = 48; //nVoq servers taking forever to process these damn jobs....
    public const JOB_QUEUE_RETRY_MINI_DELAY_S = 30; // in seconds Original value 30
    public const JOB_QUEUE_RETRY_DELAY_S = 60; // in seconds. Original 60
    //endregion

}
