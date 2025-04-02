<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\NarrativeApiController;
use App\Http\Controllers\URLGenerationApiController;
use App\Http\Controllers\Api\AuthenticationController as ApiAuthenticationController;
use App\Http\Controllers\Api\OrganizationController as ApiOrganizationController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\PromptController as ApiPromptController;
use App\Http\Controllers\Api\EncounterController as ApiEncounterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

//Route::middleware('auth')->group(function()
//{
//   Route::post("/vocab/add", [VocabController::class, 'add']);
//});


//region Narrative API - Temporarily - to be moved to sanctum when a DB is incorporated

Route::prefix('v1/utils')->name("util.")
    ->middleware(['force-json-response-mw'])
    ->group(function () {

    /** [POST] */
    Route::post("/urlgen", [URLGenerationApiController::class, 'urlgen'])->name('urlgen');
    Route::post("/getlc", [URLGenerationApiController::class, 'getlc'])->name('getlc');  //Get login credentials using token

    /** [GET] */
    Route::get("/getpubkey", [URLGenerationApiController::class, 'getpubkey'])->name('getpubkey');
});

Route::prefix('v1/reports')->name("report.")
    ->middleware(['gen-ai-isadmin-mw','force-json-response-mw'])
    ->group(function() {
        /** [GET] */
        Route::get("/getSummaryReport", [NarrativeApiController::class, 'getSummaryReport'])->name('getSummaryReport');
        Route::get("/getFeedbackReport", [NarrativeApiController::class, 'getFeedbackReport'])->name('getFeedbackReport');
});

Route::prefix('v1')->name("v1.")
    ->middleware(['gen-ai-auth-mw', 'narrative-license-check', 'force-json-response-mw'])
    ->group(function () {

    /** [GET] */
    Route::get("/", [NarrativeApiController::class, 'landing']);
    Route::get('/getToken', [NarrativeApiController::class, 'token'])->name('token');
    Route::get("/getPrompts", [NarrativeApiController::class, 'prompts'])->name('prompts');
    Route::get("/getHeadings", [NarrativeApiController::class, 'headings'])->name('headings');
    Route::get("/getNewSummaries", [NarrativeApiController::class, 'getNewSummaries'])->name('get-new-summaries');
    Route::get("/status", [NarrativeApiController::class, 'status'])->name('get-status');
    // Route::get("/urlgen", [URLGenerationApiController::class, 'urlgen'])->name('urlgen');


    /** [POST] */
    Route::post("/setGaj", [NarrativeApiController::class, 'setGenAiJobStateToReviewed'])->name('set-gen-ai-job-state-to-reviewed');
    Route::post("/postNarrative", [NarrativeApiController::class, 'newAudioNarrativeRequest'])->name('narrative');
    Route::post("/summarizationJobStats", [NarrativeApiController::class, 'summarizationJobStats'])->name('summarizationJobStats');
    Route::post("/setInputTokens", [NarrativeApiController::class, 'setInputTokens'])->name('set-input-tokens');
    Route::post("/setOutputTokens", [NarrativeApiController::class, 'setOutputTokens'])->name('set-output-tokens');
    Route::post("/feedback", [NarrativeApiController::class, 'feedback'])->name('feedback');
});

Route::prefix('v1/auth')
    ->middleware([])
    ->group(function(){
        Route::post('/login', [ApiAuthenticationController::class, 'login'])->name('api.auth.login');
        Route::post('refresh', [ApiAuthenticationController::class, 'refresh'])->name('api.auth.refresh');
    });

Route::prefix('v1')
    ->middleware(['api-camel-snake', 'api-auth-token', 'api-auth-admin'])
    ->group(function (){
       Route::prefix('organization')
           ->group(function (){
               Route::post('/create', [ApiOrganizationController::class, 'create'])->name('api.organization.create');
               Route::put('/update', [ApiOrganizationController::class, 'update'])->name('api.organization.update');
               Route::get('/view/{id}', [ApiOrganizationController::class, 'view'])->name('api.organization.view');
           });
    });

Route::prefix('v1')
    ->middleware(['api-auth-token'])
    ->group(function(){
        Route::post('/logout', [ApiAuthenticationController::class, 'logout'])->name('api.auth.logout');
    });


Route::prefix('v1')
    ->middleware(['api-camel-snake', 'api-auth-token'])
    ->group(function(){
        Route::prefix('organization')
            ->group(function (){
                Route::get('/list', [ApiOrganizationController::class, 'organization_list'])->name('api.organization.list');
            });

        Route::prefix('user')
            ->group(function(){
                Route::post('/create', [ApiUserController::class, 'create'])->name('api.user.create');
                Route::put('/update', [ApiUserController::class, 'update'])->name('api.user.update');
                Route::get('/view/{id}', [ApiUserController::class, 'view'])->name('api.user.view');
                Route::get('/list', [ApiUserController::class, 'user_list'])->name('api,user.list');
                Route::get('/list/organization/{organization_id}', [ApiUserController::class, 'user_list_by_organization'])->name('api.user.list.by.organization');
            });

        Route::prefix('prompt')
            ->group(function(){
                Route::post('/create', [ApiPromptController::class, 'create'])->name('api.prompt.create');
                Route::put('/update', [ApiPromptController::class, 'update'])->name('api.prompt.update');
                Route::get('/view/{id}', [ApiPromptController::class, 'view'])->name('api.prompt.view');
                Route::get('/list', [ApiPromptController::class, 'prompt_list'])->name('api.prompt.list');
                Route::get('/list/organization/{organization_id}', [ApiPromptController::class, 'prompt_list_by_organization'])->name('api.prompt.list.by.organization');
                Route::get('/list/user/{user_id}', [ApiPromptController::class, 'prompt_list_by_user'])->name('api.prompt.list.by.user');
            });

        Route::prefix('encounter')
            ->group(function(){
                Route::post('/create', [ApiEncounterController::class, 'create'])->name('api.encounter.create');
                Route::put('/update', [ApiEncounterController::class, 'update'])->name('api.encounter.update');
                Route::put('/updateStatus', [ApiEncounterController::class, 'update_status'])->name('api.encounter.update.status');
                Route::post('/add-recording', [ApiEncounterController::class, 'add_recording'])->name('api.encounter.add.recording');
                Route::get('/view/{id}', [ApiEncounterController::class, 'view'])->name('api.encounter.view');
                Route::get('/list', [ApiEncounterController::class, 'encounter_list'])->name('api.encounter.list');
                Route::get('/list/organization/{organization_id}', [ApiEncounterController::class, 'encounter_list_by_organization'])->name('api.encounter.list.by.organization');
                Route::get('/list/user/{user}', [ApiEncounterController::class, 'encounter_list_by_user'])->name('api.encounter.list.by.user');
            });
    });

//Route::prefix('test')->name("test.")
//    ->group(function ()
//    {
//
//    });

Route::prefix('test')->name("test.")
    ->middleware(['gen-ai-test-secret-mw', 'gen-ai-auth-mw', 'narrative-license-check', 'gen-ai-test-secret-mw'])
    ->group(function () {
    Route::get("/", [NarrativeApiController::class, 'landing']);

        Route::get('/test', [NarrativeApiController::class, 'test'])->name('test'); // dummy

//    Route::get('/testLogin', [NarrativeApiController::class, 'testLogin'])->name('test-login'); // dummy
//    Route::get('/testReadSession', [NarrativeApiController::class, 'testReadSession'])->name('test-read-session'); // dummy

    Route::get('/getToken', [NarrativeApiController::class, 'token'])->name('token');
    Route::get("/getPrompts", [NarrativeApiController::class, 'testPrompts'])->name('prompts');
    Route::get("/getHeadings", [NarrativeApiController::class, 'headings'])->name('headings');
    Route::get("/getNewSummaries", [NarrativeApiController::class, 'getNewSummaries'])->name('get-new-summaries');
    Route::get("/status", [NarrativeApiController::class, 'status'])->name('get-status');
    Route::get("/getSummaryReport", [NarrativeApiController::class, 'getSummaryReport'])->name('getSummaryReport');
    Route::get("/getFeedbackReport", [NarrativeApiController::class, 'getFeedbackReport'])->name('getFeedbackReport');

    Route::post("/setGaj", [NarrativeApiController::class, 'setGenAiJobStateToReviewed'])->name('set-gen-ai-job-state-to-reviewed');
    Route::post("/postNarrative", [NarrativeApiController::class, 'newAudioNarrativeRequest'])->name('narrative');
    Route::post("/summarizationJobStats", [NarrativeApiController::class, 'summarizationJobStats'])->name('summarizationJobStats');
    Route::post("/setInputTokens", [NarrativeApiController::class, 'setInputTokens'])->name('set-input-tokens');
    Route::post("/setOutputTokens", [NarrativeApiController::class, 'setOutputTokens'])->name('set-output-tokens');
    Route::post("/feedback", [NarrativeApiController::class, 'feedback'])->name('feedback');



    /* test only endpoints */
    Route::get("/setGaj", [NarrativeApiController::class, 'setGenAiJobStateToReviewed'])->name('set-gen-ai-job-state-to-reviewed-as-get');
    Route::get("/postNarrative", [NarrativeApiController::class, 'newAudioNarrativeRequest'])->name('narrative-as-get');
    Route::get("/setInputTokens", [NarrativeApiController::class, 'setInputTokens'])->name('set-input-tokens-as-get');
    Route::get("/setOutputTokens", [NarrativeApiController::class, 'setOutputTokens'])->name('set-output-tokens-as-get');
});

//endregion
