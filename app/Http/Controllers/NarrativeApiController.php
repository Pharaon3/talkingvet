<?php

namespace App\Http\Controllers;

use App\Extension\GenAIConfig;
use App\Extension\HelperFunctions;
use App\Http\Middleware\NarrativeApiLicenseCheck;
use App\Http\Middleware\NarrativeApiModifyAcceptHeaderForceJson;
use App\Jobs\ProcessMultispeakerDictation;
use App\Models\AssemblyAiTranscribeRequest;
use App\Models\Enums\GenAIRequestState;
use App\Models\Enums\NvoqTransactionPropertyType;
use App\Models\ChatGptSummaryResponseModel;
use App\Models\Enums\GenAiState;
use App\Models\GenAIRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Authorization checks are automatically handled by middleware
 * (narrative-license-check) @see NarrativeApiLicenseCheck
 *
 * Json response (instead of HTML render) can be enforced by the client by sending Accept header as Json
 * OR by our server using the middleware (force-json-response-mw) @see NarrativeApiModifyAcceptHeaderForceJson
 */
class NarrativeApiController extends Controller
{


    //region Public API Handling
    public function landing(Request $request) : JsonResponse
    {
        return response()->json(null, ResponseAlias::HTTP_OK);
    }

    public function token(Request $request) : JsonResponse
    {
        return response()->json(['token' => config('app.gen_ai_data.chatgpt_token')], ResponseAlias::HTTP_OK);
    }

    public function prompts(Request $request) : JsonResponse
    {
        return $this->_processGetFileContentRequest(GenAIConfig::PROMPTS_JSON_FILENAME);
    }

    public function headings(Request $request) : JsonResponse
    {
        return $this->_readCsvToJson(GenAIConfig::HEADING_CSV_FILENAME);
    }

    public function setGenAiJobStateToReviewed(Request $request) : JsonResponse
    {
        return $this->_updateNvoqTrxAdditionalProperty(
            $request,
            NvoqTransactionPropertyType::GEN_AI_JOB_STATE
        );
    }

    public function getSummaryReport(Request $request) : JsonResponse {
        return $this->_getAllSummaries();
    }

    public function getFeedbackReport(Request $request) : JsonResponse {
        return $this->_getAllFeedback();
    }

    /**
     * Incoming API – This will set the “inputtokens” property. This will save the amount of input and output tokens used for the summary generation. Note: If the property already exists, the incoming value in the API call must be added to the existing property. Also, since there will be input and output token, I would still like to store in a single property since we can’t add/update multiple properties in a single API call. This is set using the additionalProperties endpoint:
     *
     * {{nVoqServer}}/SCVmcServices/rest/transactions/{{id}}/additionalProperties
     *
     * Body: {
     * “inputTokenUsage” : “12”
     * }
 * @param Request $request
     * @return JsonResponse
     */
    public function setInputTokens(Request $request): JsonResponse
    {
        return $this->_updateNvoqTrxAdditionalProperty($request,
            NvoqTransactionPropertyType::GEN_AI_JOB_INPUT_TOKEN_USAGE,
            true);
    }


    public function setOutputTokens(Request $request): JsonResponse
    {
        return $this->_updateNvoqTrxAdditionalProperty($request,
            NvoqTransactionPropertyType::GEN_AI_JOB_OUTPUT_TOKEN_USAGE,
            true);
    }

    public function summarizationJobStats(Request $request): JsonResponse
    {
        /* arg check */
        $validator = Validator::make($request->all(), [
            'summarizationID' => ['required'],
            'prompt' => ['required'],
            'source' => ['required'],
            'output' => ['required'],
            'input_tokens' => ['required'],
            'output_tokens' => ['required'],
        ]);

        if($validator->fails())
        {
            return HelperFunctions::ErrorJsonResponse($validator->errors()->all(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        /* parameters */

        $summarizationID = $validator->validated()['summarizationID'];
        $prompt = $validator->validated()['prompt'];
        $source = $validator->validated()['source'];
        $output = $validator->validated()['output'] ?? "";
        $inputTokens = $validator->validated()['input_tokens'];
        $outputTokens = $validator->validated()['output_tokens'];

        $result = false;
        try {
            $result = \DB::table('summary_results')->insert([
                'summarizationID' => $summarizationID,
                'username' => session('username'),
                'prompt' => $prompt,
                'source' => $source,
                'output' => $output,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
            ]);
        }
        catch (\Exception $e)
        {
            Log::warning('[Narrative API Controller] [Summary Results] database insert error | ' . $e->getMessage());
        }

        if($result)
        {
            return Response()->json([
                'msg' => 'Summary result inserted successfully'
            ], ResponseAlias::HTTP_OK);
        }
        else
        {
            return Response()->json([
                'msg' => 'Failed to insert summary result'
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function feedback(Request $request): JsonResponse
    {
        /* arg check */
        $validator = Validator::make($request->all(), [
            'job_id' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:0', 'max:3'],
            'country' => ['required', Rule::in(Arr::divide(config("app.nvoq_servers"))[0])], // keys only
            'comment' => ['string', 'nullable'],
        ]);

        if($validator->fails())
        {
            return HelperFunctions::ErrorJsonResponse($validator->errors()->all(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        /* parameters */
        $jobId = $validator->validated()['job_id'];
        $rating = $validator->validated()['rating'];
        $country = $validator->validated()['country'];
        $comment = $validator->validated()['comment'] ?? "";

        $result = false;
        try {
            $result = \DB::table('feedback')->insert([
                'job_id' => $jobId,
                'username' => session('username'),
                'rating' => $rating,
                'comment' => $comment,
                'country' => $country
            ]);
        }
        catch (\Exception $e)
        {
            Log::warning('[Narrative API Controller] [Feedback] database insert error | ' . $e->getMessage());
        }

        if($result)
        {
            return Response()->json([
                'msg' => 'Feedback recorded, thank you!'
            ], ResponseAlias::HTTP_OK);
        }
        else
        {
            return Response()->json([
                'msg' => 'Failed to record feedback, please try again later'
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    /**
     * Searches and returns summaries with state Ready (unreviewed) @see GenAiState::GEN_AI_READY
     * @param Request $request
     * @return JsonResponse
     */
    public function getNewSummaries(Request $request) : JsonResponse
    {
        /* test getting trx from nvoq */
        $newSummariesResult = NvoqNetworkController::GenAI_GetNewSummaries();

        if($newSummariesResult instanceof JsonResponse)
            return $newSummariesResult;

        if(is_array($newSummariesResult))
        {
            $summariesArray = array_values($newSummariesResult);
            return new JsonResponse($summariesArray, ResponseAlias::HTTP_OK);
        }

        else if(!$newSummariesResult)
        {
            return HelperFunctions::ErrorJsonResponse("failed to retrieve summaries",
                responseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        /* test login save */
        return new JsonResponse($newSummariesResult, 200);
    }

        /**

     * Incoming Request Sample
     *      {
     *          "dictationId": dictationID,
     *          "audioLocation": audioLocation,
     *          "country": DataManager.shared.getLoginServer(),
     *          "userAuthString": DataManager.shared.getSavedBasicAuthString(),
     *          "gaj":"true" / "false",
     *          "gajstate": 0
     *      }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function newAudioNarrativeRequest(Request $request) : JsonResponse
    {
//        return Response()->json(['msg' => 'Not Implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);

//        $req = GenAIRequest::find('ThzzUfJWT9iZMZtIeMHlcQ');

        /* arg check */
        $validator = Validator::make($request->all(), [
            'audioLocation' => ['required', 'regex:/\/SCVmcServices\/rest\/transactions\/[^\/]+\/audio/'],
            'audioFsUrl' => ['required', 'regex:/\/SCFileserver\/audio\/.*/'],
//            'audioFsUrl' => ['required', 'regex:/.*/'],
            'country' => ['required', Rule::in(Arr::divide(config("app.nvoq_servers"))[0])], // keys only
            'userAuthString' => ['required', 'string']
        ]);

        if($validator->fails())
        {
            return HelperFunctions::ErrorJsonResponse($validator->errors()->all(), ResponseAlias::HTTP_BAD_REQUEST);
        }
        /* parameters */
        $audioLocation = $validator->validated()['audioLocation'];
        $audioFs = $validator->validated()['audioFsUrl'];
        $country = $validator->validated()['country'];
        $audioUrlFromTrxData = config("app.nvoq_servers")[$country] . $audioLocation;
        $audioFsUrlDirect = config("app.nvoq_servers")[$country] . $audioFs;
//        $token = $validator->validated()['token'];
//        $userAuthString = $validator->validated()['userAuthString'];

        /** 1. Make job model & parse audio url from request */
//        $genAIRequestModel = new GenAIRequest($validator->validated());
        $genAIRequestModel = null;

        try {
            $genAIRequestModel = GenAIRequest::create($validator->valid());
        } catch (\Exception $e)
        {
            if(isset($e->errorInfo[2]))
            {
                if($e->errorInfo[2] == "UNIQUE constraint failed: gen_ai_requests.trxId")
                {
                    // check existing job
                    $existingModel = GenAiRequest::findByAudioLocation($audioLocation);
                    $errorCount = $existingModel->error;
                    if($existingModel != null && $existingModel->error != 0)
                    {
//                        if($existingModel->error != 0)
//                        {
                            return HelperFunctions::ErrorJsonResponse("Job Error | Count ($errorCount) " . $existingModel->error_msg, ResponseAlias::HTTP_BAD_REQUEST);
//                        }
//                        else
//                        {
//                            // no error - report state
//                            return Response()->json([
//                                'msg' => 'Job already exists, current state: ' . $existingModel->state->name
//                            ], ResponseAlias::HTTP_OK);
//                        }
                    }
                    else
                    {
                        return HelperFunctions::ErrorJsonResponse("Job already exists please wait for processing", ResponseAlias::HTTP_BAD_REQUEST);
                    }

                }
            }
            else
            {
                return HelperFunctions::ErrorJsonResponse("Unknown error occurred (180)", ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }

            //$duplicateTrxID = true;

        }
//        $genAIRequestModel = $genAIRequestModel->create($validator->valid());

//        if(GenAIRequest::exist())

//        $genAIRequestModel->save();

//        $genAIRequestModel->Create($validator->validated()); // fill data
        if(!$genAIRequestModel->valid)
        {
            return HelperFunctions::ErrorJsonResponse('couldn\'t parse transaction ID from audioLocation', ResponseAlias::HTTP_BAD_REQUEST);
        }

        // 1. OK, parse username & update DB state
        $genAIRequestModel->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_1_TRX_ID_PARSED;
        $genAIRequestModel->username = session('username');
        $genAIRequestModel->save();

        /** step modified due to #39 nvoq delay issue
         * 2. Download transaction audio from nvoq to local storage
         */
        $resultPath = HelperFunctions::DownloadGenAiAudio($audioFsUrlDirect,
            false,
            session("country"),
            $genAIRequestModel->trxId);

        /** 2.a. handle downloaded file response */
        if(
            $resultPath == null ||
            $resultPath instanceof JsonResponse
        )
        {
            /* donetodo fetch from another location (transaction data) */
            /* try original transaction audio link */
            $resultPath = HelperFunctions::DownloadGenAiAudio($audioUrlFromTrxData,
                false,
                session("country"),
                $genAIRequestModel->trxId);

            if(($resultPath == null) || ($resultPath instanceof JsonResponse))
            {
                // error occurred - dispatch for retry using job queue

                /** Z0. dispatch un-downloaded audio job */
                ProcessMultispeakerDictation::dispatch($genAIRequestModel);

                return Response()->json([
                    'msg' => 'Job scheduled for processing (2)',
                    'model' => $genAIRequestModel
                ], ResponseAlias::HTTP_OK);
            }
//            if($resultPath == null)
//            {
//                return HelperFunctions::ErrorJsonResponse("failed to download audio", ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
//            }
//            else if($resultPath instanceof JsonResponse)
//            {
//                $genAIRequestModel->error += 1;
//                $genAIRequestModel->error_msg = $resultPath->content();
//                $genAIRequestModel->save();
//                $genAIRequestModel->delete(); // remove model (softly)
//
//                /* error occurred */
//                return $resultPath;
//            }
        }

        // else - continue

        // save local audio file path
        $genAIRequestModel->localAudioFile = $resultPath;
        $genAIRequestModel->save();

        /* update job state */
        $genAIRequestModel->state = GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_2_DOWNLOADED_TRX_AUDIO;

        /* save to DB */
        $genAIRequestModel->save();

        // Done
        // Dispatch job then
        // Report back to (Mobile App | Requester)

        /** Z. dispatch job */
        // donetodo - this is testing only switch back to dispatch instead of dispatchSync
        ProcessMultispeakerDictation::dispatch($genAIRequestModel);
//        ProcessMultispeakerDictation::dispatchSync($genAIRequestModel);

        return Response()->json([
            'msg' => 'Job scheduled for transcription',
            'model' => $genAIRequestModel
        ], ResponseAlias::HTTP_OK);
    }

    public function status(Request $request) : JsonResponse
    {
        /* arg check */
        $validator = Validator::make($request->all(),
        [
            'id' => ['required', 'string'],
            'userAuthString' => ['required', 'string']
        ]);

        if($validator->fails())
        {
            return HelperFunctions::ErrorJsonResponse($validator->errors()->all(), ResponseAlias::HTTP_BAD_REQUEST);
        }
        /* parameters */
        $username = session('username');
        $id = $validator->validated()['id'];

        /* force dispatch */
        $result = GenAIRequest::findByTrxId($id, true);

        if($result != null)
        {
            if($result->username == $username)
            {
                // valid request user - transaction belong to requester
                if($result->error)
                {
//                    return new JsonResponse(, ResponseAlias::HTTP_OK);
                    return HelperFunctions::ErrorJsonResponse($result->error_msg, ResponseAlias::HTTP_OK);
                }
                else
                {
                    if($result->state->value < GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_10_AI_JOB_SET_AS_READY->value)
                    {
                        return new JsonResponse(['status' => 'processing'], ResponseAlias::HTTP_OK);
                    }
                    else
                    {
                        return new JsonResponse(['status' => 'completed'], ResponseAlias::HTTP_OK);
                    }
                }
            }
            else
            {
                return new JsonResponse(['status'=>"not found"], ResponseAlias::HTTP_NOT_FOUND);
            }

            //ProcessMultispeakerDictation::dispatch($result);
//            ProcessMultispeakerDictation::dispatchSync($result);
        }
        else
        {
            return new JsonResponse(['status'=>"not found"], ResponseAlias::HTTP_NOT_FOUND);
        }

//        /* try get transaction */
//        $trxId = config('app.gen_ai_data.test_nvoq_trx_id');
//
//        /* test getting trx from nvoq */
//        $trx = NvoqNetworkController::GenAI_GetTransactionById($trxId);
//        if($trx == null)
//            return HelperFunctions::ErrorJsonResponse("transaction doesn't exist", ResponseAlias::HTTP_NOT_FOUND);
//
//        /* get additional properties */
//        $trxAdditionalData = $trx->GetAdditionalProperties();
//
//        /* test login save */
//        return new JsonResponse(
//            [
//                //'data' => $base64Str,
//                'username' => $username,
//                'password' => $password,
//                'country' => $country,
//                'trx' => $trxId,
//                'trxData' => $trx,
//                'Properties' => $trxAdditionalData,
//            ], 200);

    }

    public static function _getAllSummaries()
    {

        $results = \DB::select('select * from summary_results');

        Log::error($results);

        if($results instanceof JsonResponse)
        return $results;

    if(is_array($results))
    {
        $summariesArray = array_values($results);
        return new JsonResponse($summariesArray, ResponseAlias::HTTP_OK);
    }

    else if(!$results)
    {
        return HelperFunctions::ErrorJsonResponse("failed to retrieve summary report",
            responseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    public static function _getAllFeedback()
    {

        $feedbackResults = \DB::select('select * from feedback');

        Log::error($feedbackResults);

        if($feedbackResults instanceof JsonResponse)
        return $feedbackResults;

    if(is_array($feedbackResults))
    {
        $feedbackArray = array_values($feedbackResults);
        return new JsonResponse($feedbackArray, ResponseAlias::HTTP_OK);
    }

    else if(!$feedbackResults)
    {
        return HelperFunctions::ErrorJsonResponse("failed to retrieve feedback report",
            responseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    //endregion


    //region Test Functions

    public function test(Request $request) : JsonResponse
    {
//        return new JsonResponse("test");

        /* force dispatch */
        $result = GenAIRequest::findByTrxId("ecYl-Re4Q9e4X7b0KeVdCg");
        if($result != null)
        {
            //ProcessMultispeakerDictation::dispatch($result);
//            ProcessMultispeakerDictation::dispatchSync($result);
            return new JsonResponse($result->username);

        }

        return new JsonResponse("doesn't exist", ResponseAlias::HTTP_NOT_FOUND);

//        $resultPath = "tmp/audio/da9d2942c976c5d74e8b4e2c24588506ecYl-Re4Q9e4X7b0KeVdCg.ogg";
//        $localAudioPublicWebUrl = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->url($resultPath);
//
//        ProcessMultispeakerDictation::dispatch(new GenAIRequest());
//
//
//        /* login check */
//        if(!$this->_login($request))
//            return HelperFunctions::ErrorJsonResponse("failed to authenticate", ResponseAlias::HTTP_UNAUTHORIZED);
//
//
//        /* Get user data */
//        $username = session('username');
//        $password = session('password');
//        $country = session('country');
//
//        /* try get transaction */
//        $trxId = config('app.gen_ai_data.test_nvoq_trx_id');
//
//        /* test getting trx from nvoq */
//        $trx = NvoqNetworkController::GenAI_GetTransactionById($trxId);
//        if($trx == null)
//            return HelperFunctions::ErrorJsonResponse("transaction doesn't exist", ResponseAlias::HTTP_NOT_FOUND);
//
//        /* get additional properties */
//        $trxAdditionalData = $trx->GetAdditionalProperties();
//
//        /* test login save */
//        return new JsonResponse(
//            [
//                //'data' => $base64Str,
//                'username' => $username,
//                'password' => $password,
//                'country' => $country,
//                'trx' => $trxId,
//                'trxData' => $trx,
//                'Properties' => $trxAdditionalData,
//            ], 200);

    }

    public function testPrompts(Request $request) : JsonResponse
    {
        return $this->_processGetFileContentRequest(GenAIConfig::TEST_PROMPTS_JSON_FILENAME);
    }

    public function testReadSession(Request $request) : JsonResponse
    {
//        dd(session()->all());
        return new JsonResponse(
            session(),
            200
        );
    }

//    public function testLogin(Request $request) : JsonResponse
//    {
//
//    }

    /**
     * @api https://test.nvoq.com/SCVmcServices/rest/transactions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function test_search_tx(Request $request) : JsonResponse
    {
        /* login check */
        //if(!$this->_login($request))
          //  return $this->_errorJsonResponse("failed to authenticate", ResponseAlias::HTTP_UNAUTHORIZED);


        /* test getting trx from nvoq */
        $result = NvoqNetworkController::GenAI_GetNewSummaries();

        if($result instanceof JsonResponse)
            return $result;

        else if(!$result)
        {
            return HelperFunctions::ErrorJsonResponse("failed to retrieve summaries",
                responseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        /* test login save */
        return new JsonResponse($result, 200);
    }

    public function test_gpt_parsing(Request $request) : JsonResponse
    {
        $gptResponse = <<<'EOD'
[
   {
      "response":"Subject: Summary of Your Pet's Recent Veterinary Visit and Post-Appointment Care Instructions\n\nDear Pet Parent,\n\nI wanted to provide you with a comprehensive summary of your pet's recent visit to our veterinary clinic and give you some post-appointment care instructions for home.\n\nDuring the visit, we conducted tests to investigate the cause of your pet's severe thrombocytopenia, which could be due to a drug reaction, tick-borne infections, or cancers. The in-house test for tick-borne infections was negative, but we have submitted a PCR panel for further investigation, and we will notify you of the results within a week.\n\nBased on our findings, we have started your pet on doxycycline as a precaution for a possible tick-borne infection. We also initiated intravenous steroid therapy, gastrointestinal protectant agents, and a medication to stabilize blood clots. While your pet's diarrhea and vomiting have resolved, she is still passing digested blood in her stool, which is a concern. She received a whole blood transfusion earlier this week, which seems to be holding.\n\nUnfortunately, your pet still has no platelets in her bloodstream, which may indicate an immune system attack on her platelet precursors in the bone marrow. This may require several weeks to respond to therapy. We discussed additional treatment options, such as a second immune suppressant, vincristine chemotherapy, and intravenous gamma globulin infusions, but due to costs and delayed onset of effects, we are not pursuing them at this time.\n\nYour pet has benefited from close monitoring and cage confinement in the hospital, but we understand the mounting costs involved. Therefore, we recommend taking her home for continued care. Please follow these specific recommendations:\n- Do not allow her to roam freely outside, only short leash walks.\n- Avoid heavy play or fighting with other pets to minimize the risk of bleeding.\n- Monitor her overall condition closely and schedule frequent check-ups with your family veterinarian or with us.\n\nAdditionally, your pet has developed inflammation in her right front leg where the IV catheter was placed. We advise applying cool compresses to the area 2-3 times daily with an ice pack or frozen vegetable bag wrapped in a towel until the swelling resolves.\n\nIf you have any questions or concerns about your pet's condition or the care instructions, please do not hesitate to contact us. We are here to support you and your furry companion during this challenging time.\n\nTake care and best wishes for your pet's recovery.\n\nSincerely,\nYour Veterinary Team",
      "token_usage":{
         "prompt_tokens":609,
         "completion_tokens":505,
         "total_tokens":1114
      },
      "raw_json":"{\n  \"id\": \"chatcmpl-9ekzOfXHFOwGmVpNNStYeY4w16eam\",\n  \"choices\": [\n    {\n      \"finish_reason\": \"stop\",\n      \"index\": 0,\n      \"logprobs\": null,\n      \"message\": {\n        \"content\": \"Subject: Summary of Your Pet's Recent Veterinary Visit and Post-Appointment Care Instructions\\n\\nDear Pet Parent,\\n\\nI wanted to provide you with a comprehensive summary of your pet's recent visit to our veterinary clinic and give you some post-appointment care instructions for home.\\n\\nDuring the visit, we conducted tests to investigate the cause of your pet's severe thrombocytopenia, which could be due to a drug reaction, tick-borne infections, or cancers. The in-house test for tick-borne infections was negative, but we have submitted a PCR panel for further investigation, and we will notify you of the results within a week.\\n\\nBased on our findings, we have started your pet on doxycycline as a precaution for a possible tick-borne infection. We also initiated intravenous steroid therapy, gastrointestinal protectant agents, and a medication to stabilize blood clots. While your pet's diarrhea and vomiting have resolved, she is still passing digested blood in her stool, which is a concern. She received a whole blood transfusion earlier this week, which seems to be holding.\\n\\nUnfortunately, your pet still has no platelets in her bloodstream, which may indicate an immune system attack on her platelet precursors in the bone marrow. This may require several weeks to respond to therapy. We discussed additional treatment options, such as a second immune suppressant, vincristine chemotherapy, and intravenous gamma globulin infusions, but due to costs and delayed onset of effects, we are not pursuing them at this time.\\n\\nYour pet has benefited from close monitoring and cage confinement in the hospital, but we understand the mounting costs involved. Therefore, we recommend taking her home for continued care. Please follow these specific recommendations:\\n- Do not allow her to roam freely outside, only short leash walks.\\n- Avoid heavy play or fighting with other pets to minimize the risk of bleeding.\\n- Monitor her overall condition closely and schedule frequent check-ups with your family veterinarian or with us.\\n\\nAdditionally, your pet has developed inflammation in her right front leg where the IV catheter was placed. We advise applying cool compresses to the area 2-3 times daily with an ice pack or frozen vegetable bag wrapped in a towel until the swelling resolves.\\n\\nIf you have any questions or concerns about your pet's condition or the care instructions, please do not hesitate to contact us. We are here to support you and your furry companion during this challenging time.\\n\\nTake care and best wishes for your pet's recovery.\\n\\nSincerely,\\nYour Veterinary Team\",\n        \"role\": \"assistant\"\n      }\n    }\n  ],\n  \"created\": 1719500774,\n  \"model\": \"gpt-3.5-turbo-0125\",\n  \"object\": \"chat.completion\",\n  \"system_fingerprint\": null,\n  \"usage\": {\n    \"completion_tokens\": 505,\n    \"prompt_tokens\": 609,\n    \"total_tokens\": 1114\n  }\n}"
   }
]
EOD;

        $json = json_decode($gptResponse, true);
        if (
            (json_last_error() !== JSON_ERROR_NONE) ||
            !isset($json[0]['token_usage'])
        )
        {
            return response()->json([
                'error' => 'Invalid JSON format in the file.'
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY); // Unprocessable Entity
        }

        $gptModel = new ChatGptSummaryResponseModel($json[0]);
        $body = $gptModel->getSummary();
//        $tokens = $gptModel->GetPromptTokens();
//        $gptModel->GetPromptTokens()
//        $gptModel->GetCompletionTokens()
//        $gptModel->GetTotalTokens()


        return new JsonResponse([
            "body" => $body,
            "token_details" => $gptModel->GetTokensUsage()

        ], ResponseAlias::HTTP_OK);
//        return $this->token($request);

//        if (! Gate::allows('update-post', $post)) {
//            abort(401);
//        }

//        return response('OK', 200)
//            ->header('Content-Type', 'text/plain');

//        Gate::authorize('test');

//        return response('OK', 200)->json();
//        return response()->json(['msg' => 'OK'], ResponseAlias::HTTP_BAD_REQUEST);
//        return response()->json(['msg' => 'passed'], ResponseAlias::HTTP_OK);
//        return response()->json(['msg' => 'OK'], 302);
    }
    //endregion



    #region Private Functions


    private function _processGetFileContentRequest(string $filename) : JsonResponse
    {
        if (!Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->exists($filename)) {
            return response()->json(['error' => 'File not found or not readable'], 400);
        }

        $content = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->get($filename);

//        $contents = Storage::disk('public')->get($filename);

        // Validate if contents are valid JSON
        $json = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Invalid JSON format in the file.'
            ], 422); // Unprocessable Entity
        }

        // Return the contents as a response
        return new JsonResponse($json, ResponseAlias::HTTP_OK);
    }

    private function _readCsvToJson(string $filename) : JsonResponse
    {
        // Check if file exists
        if (!Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->exists($filename)) {
            return response()->json(['error' => 'File not found or not readable'], 400);
        }
        $content = Storage::disk(GenAIConfig::GEN_AI_STORAGE_NAME)->get($filename);

//        $content = Storage::disk('public')->get($filePath);
        $content = str_replace("\"", "", $content);
        $content = str_replace("\n", "", $content);
        $splitContent = explode(",", $content);

        return response()->json($splitContent);
    }

    /**
     * @summary Creates/Updates a transaction additional property value
     *
     * @param Request $request http client request
     * @param NvoqTransactionPropertyType $propertyEnum nvoq additional property to update
     * @param $append bool true: append value to current property value | false: create/overwrite new property
     * @return JsonResponse
     */
    private function _updateNvoqTrxAdditionalProperty(Request $request,
                                                      NvoqTransactionPropertyType $propertyEnum,
                                                      bool $append = false) : JsonResponse
    {
        $property = $propertyEnum->value;

        /** 0. arg check */
        if( !$request->has('trxId') ||
            !$request->has($property)
        )
            return HelperFunctions::ErrorJsonResponse('bad request, missing parameters', ResponseAlias::HTTP_BAD_REQUEST);


        $trxId = $request->input('trxId');
        $propertyValue = $request->input($property);

        $trx = NvoqNetworkController::GenAI_GetTransactionById($trxId);
        if($trx == null)
            return HelperFunctions::ErrorJsonResponse("transaction doesn't exist", ResponseAlias::HTTP_NOT_FOUND);

        /* get additional properties */
        $trxAdditionalData = $trx->GetAdditionalProperties();


        if($append)
        {
            if(isset($trxAdditionalData[$property]))
            {
                /* update */
                $propertyValue = (int)$propertyValue + (int)$trxAdditionalData[$property];
            }
        }
        // else ignored as it is already set as user input

        /* update transaction input token */
        $result = NvoqNetworkController::GenAI_AddPropertyToExistingTransaction($trxId,
            [
                $property => $propertyValue
            ]);


        if(!$result)
        {
            return Response()->json(
                [
                    'error' => 'error updating property value'
                ],
                ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        else {
            return Response()->json(
                $result, // all additional data as json returned from nvoq
                ResponseAlias::HTTP_OK
            );
        }
    }

    #endregion

}
