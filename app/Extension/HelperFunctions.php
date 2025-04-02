<?php

namespace App\Extension;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Enums\ValidLoginCountries;
use App\Models\Enums\ValidURLSchemes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class HelperFunctions
{
    /*public static function minDecimalToTime($decimal_minutes): string
    {
        $hours = floor($decimal_minutes / 60);
        $minutes = round(($decimal_minutes / 60 - $hours) * 60);
        $seconds = 0;

        if($minutes == 60) {
            $hours++;
            $minutes = 0;
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }*/

    /**
     * Decodes basic auth string into an array:
     * [
     *  'username' => $username,
     *  'password' => $password
     * ]
     * @param string $base64Str base64 encoded string
     * @return array|false
     */
    public static function decodeBasicAuth(string $base64Str): bool|array
    {
        /* parse to username and password */
        $authStr = base64_decode($base64Str, true);

//        if(!$authStr) return $this->_errorJsonResponse("bad authentication format");
        if(!$authStr) return false;

        $authStr = utf8_encode($authStr);
        $authArr = explode(':', $authStr, 2);

//        if(sizeof($authArr) != 2) return $this->_errorJsonResponse("bad authentication format");
        if(sizeof($authArr) != 2) return false;

        $username = $authArr[0];
        $password = $authArr[1];

        return [
            'username' => $username,
            'password' => $password
        ];
    }


    public static function ErrorJsonResponse(
        $string = "",
        ResponseAlias|int $code = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
    ) : JsonResponse
    {
        return response()->json(["error" => $string], $code);
    }

    public static function CheckForValidScheme(
        $scheme = ""
    )
    {
        $isValid = false;
        $validSchemes = array_column(ValidURLSchemes::cases(), 'value');
        if (in_array($scheme, $validSchemes)) {
            $isValid = true;
        }
        return $isValid;
    }

    public static function CheckForValidCountry(
        $country = ""
    )
    {
        $isValid = false;
        $validCountry = array_column(ValidLoginCountries::cases(), 'value');
        if (in_array($country, $validCountry)) {
            $isValid = true;
        }
        return $isValid;
    }

    //Checks to confirm that Query Parameters are there and that the country is valid
    // Test URL: tvm://?ecreds=da992d8f1620615a5544028c20b817834e8e99a1fd29323675d55bcf4d3a31bd&country=TEST


    public static function CheckForValidLaunchURL(
        $url = ""
    ) {
        Log::debug('Auth Decoding] launch URL is ' . $url);
        $isValid = false;
        $queryParameters = HelperFunctions::getQueryParams($url);
        if (ISSET($queryParameters['ecreds']) && (ISSET($queryParameters['country']))) {
            $validCountry = array_column(ValidLoginCountries::cases(), 'value');
            if (in_array(strtoupper($queryParameters['country']), array_map('strtoupper', $validCountry))) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    //Checks to confirm that Query Parameters are there and that the country is valid
    public static function GetURLParams(
        $url = ""
    ) {
        $queryParameters = HelperFunctions::getQueryParams($url);
        if (ISSET($queryParameters['ecreds']) && (ISSET($queryParameters['country']))) {
            return $queryParameters;
        }
        return [];
    }

    public static function getQueryParams($url) {
        $qp = explode("?", $url);
        $qparams = explode("&", $qp[1]);
        //Add schema as param
        $queryParameters['schema'] = explode("://",$url)[0];
        foreach ( $qparams as $x) {
            $key = explode("=", $x)[0];
            $value = explode("=", $x)[1];
            $queryParameters[$key] = $value;
        }
        return $queryParameters;
    }


    /**
     * @param string $url audio full URL
     * @param string $appended_filename text to append to filename
     * @return JsonResponse|string|null
     *      JsonResponse : error occurred
     *      string : local audio path (relative to GEN_AI_STORAGE_NAME storage disk path)
     */
    public static function DownloadGenAiAudio(string $url, bool $useAdminAccount, string $country, string $appended_filename = "") : JsonResponse|string|null
    {
        // Get filename from URL

        // Generate a temporary directory
        $tempDirName = GenAIConfig::TEMP_AUDIO_PATH;

        if($appended_filename != "")
        {
            $appended_filename = "_" . $appended_filename;
        }

        // Download the audio file with authentication (if credentials provided)
        $filename = md5(time() . $url) . $appended_filename . '.ogg';
//        $filePath = storage_path('app/' . $tempDirName  . $filename);

        // Create a temporary directory using Laravel's filesystem
        Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->makeDirectory($tempDirName);

        // Download file using HTTP Client
        $requestParamUsername = "";
        $requestParamPassword = "";
        $response = null;

        if($useAdminAccount)
        {
            $response = Http::withUrlParameters([
                'server' => config("app.nvoq_servers")[$country]
            ])
                ->withHeaders(["Authorization" => 'Basic '. (config("app.nvoq_admin_secret")[$country]) ])
                ->get($url);
        }
        else
        {
            $requestParamUsername = session('username');
            $requestParamPassword = session('password');
            $response = Http::withBasicAuth($requestParamUsername, $requestParamPassword)
                ->get($url);
        }


        if ($response->failed()) {
//            return null;
            return response()->json(['error' => 'Failed to download audio'], $response->status());
        }

        // Save the file to private disk (e.g., 'local' or custom disk)
//        $downloaded = file_put_contents($filePath, $response->body());
        $relativeFilePath = GenAIConfig::TEMP_AUDIO_PATH . $filename;
        $downloaded = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->put($relativeFilePath, $response->body());

        if (!$downloaded) {
            return response()->json(['error' => 'Failed to save downloaded file'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $relativeFilePath;
    }
}
