<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

if (!defined('ADMIN_ROLE')) {define('ADMIN_ROLE', 9);}
if (!defined('MASTER_ACCOUNT_ROLE')) {define('MASTER_ACCOUNT_ROLE', 0);}
if (!defined('SUB_USER_ACCOUNT_ROLE')) {define('SUB_USER_ACCOUNT_ROLE', 1);}
if (!defined('CLERICAL_ACCOUNT_ROLE')) {define('CLERICAL_ACCOUNT_ROLE', 2);}

if (!defined('LOGIN_SERVER_USA')) {define('LOGIN_SERVER_USA', 0);}
if (!defined('LOGIN_SERVER_CANADA')) {define('LOGIN_SERVER_CANADA', 1);}
if (!defined('LOGIN_SERVER_TEST')) {define('LOGIN_SERVER_TEST', 2);}

if (!defined('MAX_RECORDING_COUNT')) {define('MAX_RECORDING_COUNT', env('MAX_RECORDING_COUNT'));}
if (!defined('MAX_RECORDING_LENGTH')) {define('MAX_RECORDING_LENGTH', env('MAX_RECORDING_LENGTH'));} //Seconds

if (!defined('ENCOUNTER_STATUS_OPEN')) {define('ENCOUNTER_STATUS_OPEN', 0);}
if (!defined('ENCOUNTER_STATUS_IN_PROGRESS')) {define('ENCOUNTER_STATUS_IN_PROGRESS', 1);}
if (!defined('ENCOUNTER_STATUS_READY_FOR_REVIEW')) {define('ENCOUNTER_STATUS_READY_FOR_REVIEW', 2);}
if (!defined('ENCOUNTER_STATUS_CLOSED')) {define('ENCOUNTER_STATUS_CLOSED', 3);}

if (!defined('DEFAULT_PROMPT_TEMPLATE')) {define('DEFAULT_PROMPT_TEMPLATE', 'Can you provide a comprehensive summary of the given text? The summary should cover all the key points and main ideas presented in the original text, while also condensing the information into a concise and easy-to-understand format. Please ensure that the summary includes relevant details and examples that support the main ideas, while avoiding any unnecessary information or repetition. The length of the summary should be appropriate for the length and complexity of the original text, providing a clear and accurate overview without omitting any important information.');}
return $app;
