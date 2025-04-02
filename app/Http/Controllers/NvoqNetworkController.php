<?php

namespace App\Http\Controllers;

use App\Extension\HelperFunctions;
use App\Extension\NvoqApi;
use App\Models\Enums\GenAiState;
use App\Models\Enums\DemoJobState;
use App\Models\GenAiNvoqTransaction;
use App\Models\CorrectionParameters;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class NvoqNetworkController
{

    //region Administration
    public static function Login($username, $password, $country): bool
    {
        if(!array_key_exists($country, config("app.nvoq_servers")))
        {
            return false;
        }

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[$country]
        ])
            ->withBasicAuth($username, $password)
            ->get( NvoqApi::GUEST_LOGIN);

        if($response->ok())
        {
//            dd($response->json());
            // get extra account properties (email, org, etc)
            self::getAccountProperties($username, $password, $country);
            // check for admin role
            $isAdmin = Arr::has($response->json()['user_roles'], 'customeradmin');
            // donetodo #291 check for narrative license - done in middleware/policy @see NarrativeApiPolicy
            session(['isAdmin' => $isAdmin]);
            if($isAdmin)
            {
                // get available accounts
                self::getAvailableAccounts($username, $password, $country);
            }
            return true;
        }else {
            return false;
        }
    }

    public static function GenAI_IsUserAdmin(Request $request) : bool
    {
        /* check common args */
        if(!$request->has(['country']))
        {
            Log::debug('GenAI_IsUserAdmin] country parameter is missing');
            return false;
        }
        $country = $request->input('country');
        $encodedCredentials = "";

        /* Check for authentication (basic auth or regular json parameter (userAuthString) */
        if($request->hasHeader('Authorization'))
        {
            Log::debug('[GenAI_IsUserAdmin] has authorization header, processing..');
            $authHeader = $request->header('Authorization');


            if (isset($authHeader) && str_starts_with($authHeader, 'Basic '))
            {
                // Extract the base64 encoded credentials
                $encodedCredentials = explode(' ', $authHeader)[1];
            }
            else
            {
                Log::debug('[GenAI Login] authorization header is malformed or not Base64 encoded, aborting login');
                return false;
            }
        }
        else if($request->has('userAuthString'))
        {
            Log::debug('[GenAI_IsUserAdmin] request is using "userAuthString" base64 encoded credentials');
            $encodedCredentials = $request->userAuthString;
        }
        else
        {
            Log::debug('[GenAI_IsUserAdminn] request has neither "userAuthString" nor Authorization header, aborting login');
            return false;
        }


        /* parse to username and password */
        $result = HelperFunctions::decodeBasicAuth($encodedCredentials);

        if(!$result)
        {
            Log::debug('GenAI_IsUserAdmin] failed to decode base64 encoded credentials, aborting logins');
            return false;
        }

        $username = $result['username'];
        $password = $result['password'];

        session([
            'username' => $username,
            'password' => $password,
            'country' => $country
        ]);
        Log::debug('[GenAI_IsUserAdmin] - checking if user is admin');
        return self::CheckAdminLogin($username, $password, $country);
    }

    public static function CheckAdminLogin($username, $password, $country): bool
    {
        if(!array_key_exists($country, config("app.nvoq_servers")))
        {
            return false;
        }

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[$country]
        ])
            ->withBasicAuth($username, $password)
            ->get( NvoqApi::GUEST_LOGIN);

        if($response->ok())
        {
            self::getAccountProperties($username, $password, $country);
            // check for admin role
            $isAdmin = Arr::has($response->json()['user_roles'], 'customeradmin');
            session(['isAdmin' => $isAdmin]);
            if($isAdmin)
            {
                return true;
            } else {
                return false;
            }
        }else {
            return false;
        }
    }

    private static function getAccountProperties($username, $password, $country)
    {
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[$country],
            'username' => $username
        ])
            ->withBasicAuth($username, $password)
            ->get( NvoqApi::AUTH_GET_ACC_PROPERTIES);

        if($response->ok())
        {
            // check for admin role
//            dd($response->json());
            session(
                [
                    'narrative' => (isset($response->json()['tvn']) && $response->json()['tvn'] == true),
                    'email' => ($response->json()['e-mail']),
                    'organization' => ($response->json()['organization'])
                ]
            );
            return true;
        }else {
            return false;
        }
    }
    private static function getAvailableAccounts($username, $password, $country)
    {
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[$country],
            'username' => $username
        ])
            ->withBasicAuth($username, $password)
            ->get( NvoqApi::AUTH_GET_ACCOUNTS);

        if($response->ok())
        {
            // check for admin role
//            dd($response->json(), Arr::pluck($response->json(), 'username'));
            $arr = Arr::pluck($response->json(), 'username');
            $arr = Arr::sortRecursive($arr);
            array_unshift($arr, __('dashboard.accounts.options.all'));
            session(
                [
                    'accounts' => $arr
                ]
            );
            return true;
        }else {
            return false;
        }
    }

    private static function handleNvoqResponses(Response $response)
    {
        return match ($response->status()) {
            // log user out - external password change
            401 => redirect()->route("logout"),
//            ->withErrors(['global' => "Please login again"])
            default => null,
        };
    }

    public static function ResetPassword($newPassword)
    {
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'username' => session('username')
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->put( NvoqApi::AUTH_CHANGE_PASSWORD, ['password' => $newPassword]);

        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;


        if($response->status() == 200)
        {
            // update user password
            auth()->user()->password = $newPassword;
            session(['password' => $newPassword]);
            return back()->with('status', 'password-updated');
        }else if($response->status() == 400){
            return back()->withErrors(['password' => 'Password too weak or has already been used before'] , "updatePassword")
                ->withErrors(['global' => 'Password too weak or has already been used before']);

        }else{
            return back()->withErrors(['global' => $response->body()]);
        }
    }


    public static function EmailPasswordResetLink($username, $country)
    {
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[$country]
        ])->asForm()->post( NvoqApi::GUEST_EMAIL_RESET_PASS_LINK, ['username' => $username]);

        session(['status' => $response->body()]);

        return $response->ok();
    }



    public static function GetTransactions($opts)
    {
        // $opts are pre-validated before this function
////        Http::fake();
//
//        session()->flash('messageZ', 'Error in getTransactions -> ' . fake()->numberBetween(0,100));

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')]
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_GET_TRANSACTIONS, $opts);

        /*Http::assertSent(function (Request $request) {
            dd($request->url());
            return true;
            return $request->hasHeader('X-First', 'foo') &&
                $request->url() == 'http://example.com/users' &&
                $request['name'] == 'Taylor' &&
                $request['role'] == 'Developer';
        });*/

        /*dd(
            $response,
//            $validator->safe()->only(Arr::divide(Transaction::validationRules())[0]),
            $response->ok(),
            $response->json(),

        );*/
        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;

        if($response->status() == 200)
        {
            return $response->json(); // returns array
            //return back()->with('status', 'password-updated');
        }else{
//            return back()->withErrors(['Request Failed' => 'ping'], 'trxTableBag');
            throw ValidationException::withMessages(["error" => $response->body()]);
//            return back()->withErrors(['Request Failed' => $response->body()], 'trxTableBag');
        }
    }


    public static function GetSingleTransactionData($trxId)
    {
        // $opts are pre-validated before this function
//        Http::fake();
//
//        session()->flash('messageZ', 'Error in getTransactions -> ' . fake()->numberBetween(0,100));

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'trxId' => $trxId
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_GET_SINGLE_TRANSACTION_DATA);

        /*Http::assertSent(function (Request $request) {
            dd($request->url());
            return true;
            return $request->hasHeader('X-First', 'foo') &&
                $request->url() == 'http://example.com/users' &&
                $request['name'] == 'Taylor' &&
                $request['role'] == 'Developer';
        });*/

        /*dd(
            $response,
//            $validator->safe()->only(Arr::divide(Transaction::validationRules())[0]),
            $response->ok(),
            $response->json(),
            $response->status(),
            $response->body()
        );*/
        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;


        if($response->status() == 200)
        {
            return $response->json(); // returns array
            //return back()->with('status', 'password-updated');
        }else{
//            return back()->withErrors(['Request Failed' => 'ping'], 'trxTableBag');
            session()->flash('error', ["error" => $response->body()]);
//            return back()->withErrors(["error" => $response->body()]);
            return null;
//            throw ValidationException::withMessages(["error" => $response->body()]);
//            return back()->withErrors(['Request Failed' => $response->body()], 'trxTableBag');
        }
    }


    public static function GetAudio($trxId)
    {
        // $opts are pre-validated before this function
//        Http::fake();
//
//        session()->flash('messageZ', 'Error in getTransactions -> ' . fake()->numberBetween(0,100));

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'trxId' => $trxId
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_GET_AUDIO_FOR_TRANSACTION);

        /*Http::assertSent(function (Request $request) {
            dd($request->url());
            return true;
            return $request->hasHeader('X-First', 'foo') &&
                $request->url() == 'http://example.com/users' &&
                $request['name'] == 'Taylor' &&
                $request['role'] == 'Developer';
        });*/

        /*dd(
            $response,
//            $validator->safe()->only(Arr::divide(Transaction::validationRules())[0]),
            $response->ok(),
            $response->json(),
            $response->status(),
            $response->body()
        );*/
        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;


        if($response->status() == 200)
        {
//            dd(['audioType' => $response->header('Content-Type'), 'audioData' => $response->body()]);
            return ['audioType' => $response->header('Content-Type'), 'audioData' => base64_encode($response->body())];
//            return $response->json(); // returns array
            //return back()->with('status', 'password-updated');
        }else{
//            return back()->withErrors(['Request Failed' => 'ping'], 'trxTableBag');
            session()->flash('error', ["error" => $response->body()]);
//            return back()->withErrors(["error" => $response->body()]);
            return null;
//            throw ValidationException::withMessages(["error" => $response->body()]);
//            return back()->withErrors(['Request Failed' => $response->body()], 'trxTableBag');
        }
    }

    //endregion

    // region Review Calls


    /**
     * Retrieves original text for nvoq transaction item
     *  nvoq OK response (200) content type *\/* === text
     * @param $id string nvoq dictation ID
     * @return array|\Illuminate\Http\RedirectResponse|mixed
     */
    public static function GetOriginalText(string $id)
    {
////        Http::fake();

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'id' => $id
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_REVIEW_GET_ORIGINAL_TEXT);

/*        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;*/

        if($response->status() == 200)
        {
            return $response->body(); // returns array
        }else{
//            return back()->withErrors(['Request Failed' => 'ping'], 'trxTableBag');
            throw ValidationException::withMessages(["error" => 'Retrieving original text failed: ' . $response->body()]);
//            return back()->withErrors(['Request Failed' => $response->body()], 'trxTableBag');
        }
    }

    /**
     * Retrieves substituted text for nvoq transaction item
     *  nvoq OK response (200) content type *\/* === text
     * @param $id string nvoq dictation ID
     * @return array|\Illuminate\Http\RedirectResponse|mixed
     */
    public static function GetSubstitutedText(string $id)
    {
////        Http::fake();

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'id' => $id
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_REVIEW_GET_SUBSTITUTED_TEXT);

/*        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;*/


        if($response->status() == 200)
        {
            return $response->body(); // returns array
        }else{
//            return back()->withErrors(['Request Failed' => 'ping'], 'trxTableBag');
            throw ValidationException::withMessages(["error" => 'Retrieving substituted text failed: ' . $response->body()]);
//            return back()->withErrors(['Request Failed' => $response->body()], 'trxTableBag');
        }
    }


    /**
     * Retrieves substituted text for nvoq transaction item
     *  nvoq OK response (200) content type *\/* === text
     * @param $id string nvoq dictation ID
     * @return array|\Illuminate\Http\RedirectResponse|mixed
     */
    public static function GetCorrectedText(string $id)
    {
////        Http::fake();

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'id' => $id
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_REVIEW_GET_CORRECTED_TEXT);

/*        $handled = NvoqNetworkController::handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;*/


        if($response->status() == 200)
        {
            return $response->body(); // returns array
        }else{
//            return back()->withErrors(['Request Failed' => 'ping'], 'trxTableBag');
            throw ValidationException::withMessages(["error" => 'Retrieving substituted text failed: ' . $response->body()]);
//            return back()->withErrors(['Request Failed' => $response->body()], 'trxTableBag');
        }
    }


    /**
     * @param string $trxId nvoq transaction ID string
     * @param CorrectionParameters $correctionParams Correction Parameters to submit
     * @return bool success/fail
     */
    public static function SubmitCorrection(string $trxId, CorrectionParameters $correctionParams)
    {
//        Http::fake();
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'id' => $trxId
        ])->asForm()
            ->withBasicAuth(session('username'), session('password'))
            ->post( NvoqApi::AUTH_REVIEW_POST_CORRECTION, $correctionParams->all());

        session(['status' => $response->body()]);
//        dd($response);

        /*Http::assertSent(function (Request $request) {
            dd($request->url(), $request->data());
            return true;
            return $request->hasHeader('X-First', 'foo') &&
                $request->url() == 'http://example.com/users' &&
                $request['name'] == 'Taylor' &&
                $request['role'] == 'Developer';
        });*/

        if($response->ok())
        {
            return true;
        }else{
            session()->flash('error', ["error" => $response->body()]);
            return false;
        }
    }

    /**
     * @param $word
     * @param $soundsLike
     * @return array success/fail
     */
    public static function AddVocab($word, $soundsLike)
    {
//        Http::fake();
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'username' => session('username')
        ])->withHeaders(['Content-Type' => 'application/json'])
            ->withBasicAuth(session('username'), session('password'))
            ->asJson()
            ->post( NvoqApi::AUTH_REVIEW_POST_ADD_VOCAB,
                [
                    'add' => [['written' => $word, 'soundsLike' => $soundsLike]],
                    'update' => [],
                    'remove' => []
                ]);

//        session(['status' => $response->body()]);
//        dd($word, $soundsLike, $response->ok(), $response->status(), $response->body(), $response);

        $res['error'] = !$response->ok();
        $res['msg'] = $response->ok() ? 'Vocab added' : $response->body();
        return $res;
        /*if($response->ok())
        {
            return true;
        }else{
            session()->flash('error', ["error" => $response->body()]);
            return false;
        }*/
    }
    //endregion

    //region Gen Narrative API

    /**
     * @see https://test.nvoq.com/apidoc/administration#tag/Transactions/operation/TransactionsAdditionPropertiesForUuid
     *
     * Always using super admin account to update instead of current logged in user account
     *
     * @param $trxId string existing transaction Id
     * @param $propertyArr array property data to add
     * @return bool|array false if failed, TrxAdditionalData array if true
     */
    public static function GenAI_AddPropertyToExistingTransaction(string $trxId, $propertyArr) : bool|array
    {
//        Http::fake();
//        $fp = fopen(storage_path('http_log_32393453.txt'), 'w+');

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'id' => $trxId
        ])
//            ->withOptions(['debug'=>$fp])
            ->asJson()
            ->withHeaders(["Authorization" => 'Basic '. (config("app.nvoq_admin_secret")[session('country')]) ])
//            ->withBasicAuth(session('username'), session('password'))
            ->post( NvoqApi::AUTH_TRX_ADD_ADDITIONAL_PROPERTIES, $propertyArr);


//        dd(
//            $response->ok(),
//            $response->status(),
//            $response->body(),
//            $response->json(),
//        );

        if($response->ok())
        {
            return $response->json();
        }else{
            session()->flash('error', ["error" => $response->body()]);
            return false;
        }
    }

    public static function GenAI_GetTransactionById(string $trxId) : ?GenAiNvoqTransaction
    {
        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')],
            'trxId' => $trxId
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_GET_SINGLE_TRANSACTION_DATA);

        $handled = NvoqNetworkController::_GenAI_handleNvoqResponses($response); // handles common response errors
        if($handled != null) return null;


        if($response->status() == 200)
        {
            $json = $response->json();
            return new GenAiNvoqTransaction($json);
        }
        else
        {
            return null;
            /*return response()->json([
                'error' => $response->body()
            ], 401);*/
        }
    }


    /**
     * @api https://test.nvoq.com/apidoc/administration#tag/Transactions/operation/TransactionsGet
     * @return array|false|JsonResponse
     */
    public static function GenAI_GetNewSummaries()
    {
        // $opts are pre-validated before this function
////        Http::fake();
//
//        session()->flash('messageZ', 'Error in getTransactions -> ' . fake()->numberBetween(0,100));

        // Get today's date
        $today = new \DateTime();

        // Subtract 14 days from today
        $pastDate = $today->sub(new \DateInterval('P45D'));

        // Format the date in mm-dd-yyyy format
        $formattedDate = $pastDate->format('m-d-Y');

        $opts = [
            'startDate' => $formattedDate,
            'itemType' => 'Dictation',
            'resultLimit' => 500,
            'c' => 'realUserName',
            'q' => session('username'),
        ];

        $response = Http::withUrlParameters([
            'server' => config("app.nvoq_servers")[session('country')]
        ])
            ->withBasicAuth(session('username'), session('password'))
            ->get( NvoqApi::AUTH_GET_TRANSACTIONS, $opts);


        $handled = NvoqNetworkController::_GenAI_handleNvoqResponses($response); // handles common response errors
        if($handled != null) return $handled;

        $array = $response->json();

        $filtered = Arr::where($array, function ($value, $key)
        {
            return (
                (isset($value['additionalProperties']['gajstate']) && ($value['additionalProperties']['gajstate']) == GenAiState::GEN_AI_READY->value) ||
                (isset($value['additionalProperties']['demo_job']) && ($value['additionalProperties']['demo_job']) == DemoJobState::DEMO_JOB_SET->value)
                );
        });


        if($response->status() == 200)
        {
            return $filtered; // returns filtered array of READY state only GenAI Jobs
        }else{
            return false;
        }
    }

    public static function GenAI_LoginWithBasicAuthentication(Request $request) : bool
    {
        /* check common args */
        if(!$request->has(['country']))
        {
            Log::debug('[GenAI Login] country parameter is missing');
            return false;
        }
        $country = $request->input('country');
        $encodedCredentials = "";

        /* Check for authentication (basic auth or regular json parameter (userAuthString) */
        if($request->hasHeader('Authorization'))
        {
            Log::debug('[GenAI Login] has authorization header, processing..');
            $authHeader = $request->header('Authorization');


            if (isset($authHeader) && str_starts_with($authHeader, 'Basic '))
            {
                // Extract the base64 encoded credentials
                $encodedCredentials = explode(' ', $authHeader)[1];
            }
            else
            {
                Log::debug('[GenAI Login] authorization header is malformed or not Base64 encoded, aborting login');
                return false;
            }
        }
        else if($request->has('userAuthString'))
        {
            Log::debug('[GenAI Login] request is using "userAuthString" base64 encoded credentials');
            $encodedCredentials = $request->userAuthString;
        }
        else
        {
            Log::debug('[GenAI Login] request has neither "userAuthString" nor Authorization header, aborting login');
            return false;
        }


        /* parse to username and password */
        $result = HelperFunctions::decodeBasicAuth($encodedCredentials);

        if(!$result)
        {
            Log::debug('[GenAI Login] failed to decode base64 encoded credentials, aborting logins');
            return false;
        }

        $username = $result['username'];
        $password = $result['password'];

        session([
            'username' => $username,
            'password' => $password,
            'country' => $country
        ]);
        Log::debug('[GenAI Login] logging in to nvoq servers');
        return self::Login($username, $password, $country);
    }

    public static function GenAI_Login($username, $password, $country) : bool
    {
        return self::Login($username, $password, $country);
    }

    /** Private Gen AI API Helpers */
    private static function _GenAI_handleNvoqResponses(Response $response) : ?JsonResponse
    {
        if($response->status() == 401)
        {
            return response()->json([
                'error' => 'Invalid JSON format in the file.'
            ], 401);
        }
        else
        {
            return null;
        }
    }
    //endregion

}
