<?php

namespace App\Http\Controllers;
use App\Extension\HelperFunctions;
use App\Models\Enums\ValidURLSchemes;
use App\Models\Enums\EncryptedFields;
use App\Models\LoginTokenRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

use function PHPUnit\Framework\isNull;

class URLGenerationApiController extends Controller
{
    //region Public API Handling

    public function urlgen(Request $request) : JsonResponse
    {
        return $this->_generateLaunchURL($request);
    }

    public function getlc(Request $request) : JsonResponse
    {
        return $this->_decryptUserAuthentication($request);
    }

    public function getpubkey() : JsonResponse
    {
        return $this->_getPublicKey();
    }

    #region Private Functions

    private function _generateLaunchURL(Request $request,) : JsonResponse
    {
        /* arg check */
        $validator = Validator::make($request->all(), [
            'scheme' => ['required', Rule::in(Arr::pluck(ValidURLSchemes::cases(), 'value'))],
            'base64_creds' => ['required'],
            'country' => ['required', Rule::in(Arr::divide(config("app.nvoq_servers"))[0])], // keys only
        ]);

        if($validator->fails())
        {
            return HelperFunctions::ErrorJsonResponse($validator->errors()->all(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        $scheme = $validator->validated()['scheme'];
        $base64Creds = $validator->validated()['base64_creds'];
        $country = $validator->validated()['country'];
        // Log::info("Incoming force_password_change is " . $request['force_password_change']);
        if (isset($request['force_password_change'])) {
            $force_password_change = $request['force_password_change'] == '1' ? 1 : 0;
        } else {
            Log::info("force password change parameter not supplied. Default to 1");
            $force_password_change = 1;
        } 

        //Generate 32 byte token
        $token = bin2hex(openssl_random_pseudo_bytes(32));

        //Decrypt the base64 authentication
        $decryptedBase64Auth = $this->decryptIncomingData($base64Creds, EncryptedFields::BASE64_AUTH);
        // Log::info("Decrypted base64 auth is " . $decryptedBase64Auth);

        // Create new DB record in logintokens table
        try {
            $result = \DB::table('logintokens')->insert([
                'token' => $token,
                'scheme' => $scheme,
                'base64_auth' => $decryptedBase64Auth,
                'country' => $country,
                'force_password_change' => $force_password_change,
                'used' => 0
            ]);
        }
        catch (\Exception $e)
        {
            Log::warning('[URL Generation API Controller] [Logintokens] database insert error | ' . $e->getMessage());
            return HelperFunctions::ErrorJsonResponse('Error generating URL', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);

        }
        $launchURL = $scheme . "://?ecreds=" . $token;
        $json['status'] = 200;
        $json['url'] = $launchURL;
        // Return the contents as a response
        return new JsonResponse($json, ResponseAlias::HTTP_OK);
    }

    private function _decryptUserAuthentication(Request $request) : JsonResponse
    {
        if((!$request->has('token')) || (!$request->has('key'))
        ) {
        return HelperFunctions::ErrorJsonResponse('bad request, missing parameters', ResponseAlias::HTTP_BAD_REQUEST);
        }
        $token = $request->input('token'); //token
        $keys = $request->input('key'); //encrypted aes/iv keys

        //1. Look up token record
       $result = LoginTokenRequest::findByToken($token, false);
       if($result != null)
       {
            if($result->error)
            {
                return HelperFunctions::ErrorJsonResponse($result->error_msg, ResponseAlias::HTTP_OK);
            }
            else
            {
                //2. Check if token has been used. Return if it has
                $record = $result->toArray();
                if ($record['used']) {
                    return HelperFunctions::ErrorJsonResponse('Token already used', ResponseAlias::HTTP_FORBIDDEN);
                }

                //3. Decrypt encryption keys
                $encryptionKeys = $this->decryptIncomingData($keys, EncryptedFields::AES_KEYS);
                if ($encryptionKeys === null) {
                return HelperFunctions::ErrorJsonResponse('Internal server error, decryption error', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
                }

                //4. Encrypt base64 auth using decrypted key
                $encryptedAuth = openssl_encrypt(
                $record['base64_auth'],
                'AES-256-CBC',
                $encryptionKeys['aes'],
                1,
                $encryptionKeys['iv']
                );
                Log::debug('Encrypted base64 auth is: ' . $encryptedAuth);
                $updateState = LoginTokenRequest::markTokenAsUsed($token);
                if ($updateState) {
                    Log::error('Unable to update token record state');
                }

                //5. Respond with data
                $encryptedAuthHex = bin2hex($encryptedAuth);
                $json['status'] = 200;
                $json['userauth'] = strtoupper($encryptedAuthHex);
                $json['country'] = strtoupper($record['country']);
                $json['force_password_change'] = $record['force_password_change'] === 1 ? true : false;
            return new JsonResponse($json, ResponseAlias::HTTP_OK);
            }
       }
       else
       {
        Log::info('[URL Generation API Controller] [Logintokens] token' . $token . 'not found');
           return new JsonResponse(['status'=>"not found"], ResponseAlias::HTTP_NOT_FOUND);
       }
    }

    private function _getPublicKey() : JsonResponse
    {
        if ((config()->has('app.url_gen_data.app_pubkey_location')) && (strlen((config('app.url_gen_data.app_pubkey_location')) > 0))) {
            $publickKeyLocation = config('app.url_gen_data.app_pubkey_location');
        } else {
            Log::error("Missing public key details in config file");
            return HelperFunctions::ErrorJsonResponse('Unable to get public key', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
        $publicKeyFile = fopen($publickKeyLocation,"r");
        $publicKeyStream = fread($publicKeyFile,8192);
        fclose($publicKeyFile);
        $json['publicKey'] = base64_encode($publicKeyStream);
        return new JsonResponse($json, ResponseAlias::HTTP_OK);
    }

    // Returns a pipe separated string containing the assymetrical AES and IV keys
    private function decryptIncomingData($cipher, $field) {
        if (config()->has('app.url_gen_data.app_pk_passphrase') && config()->has('app.url_gen_data.app_pk_location')) {
            $pk = config('app.url_gen_data.app_pk_location');
            $passphrase = config('app.url_gen_data.app_pk_passphrase');
        } else {
            Log::error("Missing private key details in config file");
            return null;
        }
        $decodedCipher = hex2bin($cipher);  //Cannot use base64 encoding or decryption always fails
        $pkFile = fopen($pk,"r");
        $pkStream = fread($pkFile,8192);
        fclose($pkFile);
        $res = openssl_pkey_get_private($pkStream,$passphrase);
        if (!$res) {
            Log::error("Error extracting private key");
        }
        if (!openssl_private_decrypt($decodedCipher, $decryptedString, $res, OPENSSL_PKCS1_PADDING)) {
            Log::error("Error decrypting keys " . openssl_error_string());
            return null;
        }
        if (isset($decryptedString)) {
            switch ($field) {
                case EncryptedFields::AES_KEYS:
                    $decryptedKeys = explode("|",$decryptedString);
                    if (count($decryptedKeys) === 2) {
                        if(hex2bin($decryptedKeys[0]) == false || hex2bin($decryptedKeys[1]) == false)
                        {
                            $decryptedKeysArray['aes'] = $decryptedKeys[0];
                            $decryptedKeysArray['iv'] = $decryptedKeys[1];
                        }
                        /* Desktop fix - desktop app AES generation outputs some characters that are
                         * unsafe (non-UTF) to transport, hence the decryption here fails as the characters malform
                         * until they reach here.
                         *
                         * Soln: desktop now hex string encode the AES key and IV bytes before delimiting them
                         * with a pipe `|` and before encrypting them with the public key
                         *
                         * In short the api endpoint now receives:
                         * - hex string of ↓
                         * - public key encrypted string of ↓
                         * - a pipe `|` delimited string of ↓
                         * - hex string encoded AES key and IV bytes
                         *
                         * Where IV is 16 bytes as usual
                         *  */
                        else
                        {
                            $decryptedKeysArray['aes'] = hex2bin($decryptedKeys[0]);
                            $decryptedKeysArray['iv'] = hex2bin($decryptedKeys[1]);
                        }

                        return $decryptedKeysArray;
                    } else {
                        Log::info("Failed to extract keys");
                        return null;
                    }
                    break;
                case EncryptedFields::BASE64_AUTH:
                    return $decryptedString;
                    break;

            }
        } else {
            return null;
        }
    }

        // Returns a pipe separated string containing the assymetrical AES and IV keys
        private function decryptBase64Auth($cipher) {
            if (config()->has('app.url_gen_data.app_pk_passphrase') && config()->has('app.url_gen_data.app_pk_location')) {
                $pk = config('app.url_gen_data.app_pk_location');
                $passphrase = config('app.url_gen_data.app_pk_passphrase');
            } else {
                Log::error("Missing private key details in config file");
                return null;
            }
            $decodedCipher = hex2bin($cipher);  //Cannot use base64 encoding or decryption always fails
            $pkFile = fopen($pk,"r");
            $pkStream = fread($pkFile,8192);
            fclose($pkFile);
            $res = openssl_pkey_get_private($pkStream,$passphrase);
            if (!$res) {
                Log::error("Error extracting private key");
            }
            if (!openssl_private_decrypt($decodedCipher, $decryptedString, $res, OPENSSL_PKCS1_PADDING)) {
                Log::error("Error decrypting keys " . openssl_error_string());
                return null;
            }
            if (isset($decryptedString)) {
                $decryptedKeys = explode("|",$decryptedString);
            } else {
                return null;
            }
            if (count($decryptedKeys) === 2) {
                $decryptedKeysArray['aes'] = $decryptedKeys[0];
                $decryptedKeysArray['iv'] = $decryptedKeys[1];
                return $decryptedKeysArray;
            } else {
                Log::info("Failed to extract keys");
                return null;
            }
        }
    #endregion
}
