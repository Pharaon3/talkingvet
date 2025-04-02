# # Production

### Deployment Docs
https://laravel.com/docs/10.x/deployment

### Configure caching

- run `php artisan config:cache`

IMPORTANT ⚠️
> If you execute the `config:cache` command during your deployment process,
> you should be sure that you are only calling the env function from within your configuration files.
> Once the configuration has been cached, the .env file will not be loaded; therefore,
> the env function will only return external, system level environment variables.

https://laravel.com/docs/10.x/configuration#configuration-caching

-----

### Turn off debug mode in `.env` or `.env.production`
- `APP_DEBUG=false`

----

### To enable maintenance mode
- run `php artisan down`
- or `php artisan down --refresh=15`
- or `php artisan down --retry=60`
- or redirect all requests to somewhere `php artisan down --redirect=/`
- to disable `php artisan up`

> While your application is in maintenance mode,
> no queued jobs will be handled. The jobs will continue to be handled as
> normal once the application is out of maintenance mode.

#### bypassing maintenance mode with secret
- run `php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"`
- bypass with `https://example.com/1630542a-246b-4b66-afa1-dd72a4c43515`

more info > https://laravel.com/docs/10.x/configuration#bypassing-maintenance-mode

---------

## List all automatic make commands
- php artisan list make

## Events & Listeners
### The Listeners Directory 🧏

This directory does not exist by default, but will be created for you if you execute the `event:generate`
or `make:listener`Artisan commands. The Listeners directory contains the classes that handle your events.
Event listeners receive an event instance and perform logic in response to the event being fired.
For example, a `UserRegistered` event might be handled by a `SendWelcomeEmail` listener.

### The Mail Directory ✉️

This directory does not exist by default, but will be created for you if you execute the `make:mail` Artisan command.
The Mail directory contains all of your classes that represent emails sent by your application. Mail objects
allow you to encapsulate all of the logic of building an email in a single, simple class that may be sent
using the `Mail::send` method.

------
## Gen AI API
### API entry point
- Prod: `<server>/api/v1`
- Test: `<server>/api/test`
    - only accessible if the request was backed by a query parameter named `token` and of value `GEN_AI_TEST_API_ENDPOINT_SECRET` (located in `.env` file)


### Endpoints
_All responses in JSON_
- `[GET] /` landing : empty HTTP_OK 200 response
- `[GET] /token` returns chatGPT token (property `token`)  
- `[GET] /test` used in development (only accessible on test entry point)

_any error response will be of format:_
```
{
   "error": "<error message>"
}
```

### Config
#### .env File
- `APP_URL` should point to production URL with no trailing slash eg. `https://testportal.talkingvet.com`
- `APP_NARRATIVE_API_GPT_TOKEN` : chatGPT token
- `APP_NARRATIVE_API_ASSEMBLY_TOKEN` : AssemblyAI token
- `GEN_AI_TEST_API_ENDPOINT_SECRET` : secret token for `api/test/<endpoints>`
- `DB_CONNECTION` : sqlite
- `DB_DATABASE` : /absolute/path/to/database.sqlite
  - Example: `C:\vhost\laravel_dictation_portal\database\portal_db.sqlite`
- You mush reload the cache for changes to take effect. See Useful commands below for syntax


### AI Feature Installation Help
- ~~`php artisan storage:link` to link storage json files to public web~~
- `.env` file (update from `.env.example`) the following:
  - `APP_NARRATIVE_API_GPT_TOKEN`
  - `APP_NARRATIVE_API_ASSEMBLY_TOKEN` 
  - `GEN_AI_TEST_API_ENDPOINT_SECRET`
  - Update `QUEUE_CONNECTION` to `database`
    - eg. `QUEUE_CONNECTION=database`

### Database and Queue Initialization
1. in project/server root run command -> `touch database/portal_db.sqlite`
2. update `DB_DATABASE` in `.env` file with the full path of step 1 db file including the file name
3. in `php.ini` uncomment the following
   1. `extension=pdo_sqlite`
   2. `extension=pdo_mysql`
4. run `php artisan migrate`
5. `php artisan make:job ProcessMultispeakerDictation`

### Useful commands
- [View failed jobs](https://laravel.com/docs/10.x/queues#retrying-failed-jobs) -> `php artisan queue:failed`
- [Retry failed job](https://laravel.com/docs/10.x/queues#retrying-failed-jobs) -> `php artisan queue:retry ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece`
- debug queue jobs -> `php artisan queue:work --debug`
- Reload .env parameters -> `php artisan config:cache`

## To Run The Gen AI Queue 👇
### Run: `php artisan queue:work -v`
- needs restarting when:
  - re-caching server data eg. running `php artisan optimize`
  - php script code was updated
