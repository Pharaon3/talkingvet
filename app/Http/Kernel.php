<?php

namespace App\Http;

use App\Http\Middleware\CheckAccessToken;
use App\Http\Middleware\ConvertCamelToSnakeCase;
use App\Http\Middleware\GenAI_APIAuthMiddleware;
use App\Http\Middleware\GenAI_TestAPISecretMiddleware;
use App\Http\Middleware\InternalApiAdminMiddleware;
use App\Http\Middleware\InternalApiMasterMiddleware;
use App\Http\Middleware\InternalAuth;
use App\Http\Middleware\IsUserAdminMiddleware;
use App\Http\Middleware\NarrativeApiLicenseCheck;
use App\Http\Middleware\NarrativeApiModifyAcceptHeaderForceJson;
use App\Http\Middleware\NarrativeApiModifyAcceptHeaderForcePlainText;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
             \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'gen-ai-test-secret-mw' => GenAI_TestAPISecretMiddleware::class,
        'gen-ai-auth-mw' => GenAI_APIAuthMiddleware::class,
        'gen-ai-isadmin-mw' => IsUserAdminMiddleware::class,
        'narrative-license-check' => NarrativeApiLicenseCheck::class,
        'force-json-response-mw' => NarrativeApiModifyAcceptHeaderForceJson::class,
        'force-plain-text' => NarrativeApiModifyAcceptHeaderForcePlainText::class,
        'api-auth-admin'=>InternalApiAdminMiddleware::class,
        'api-auth-token' => CheckAccessToken::class,
        'api-camel-snake' => ConvertCamelToSnakeCase::class,
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'internal-auth' => InternalAuth::class
//        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    protected function schedule(Schedule $schedule){
        $schedule->command('app:cleanup-expired')->hourly();
    }
}
