<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
//use App\Services\Auth\NvoqGuard;
use App\Policies\NarrativeApiPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
//         'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // https://laracasts.com/discuss/channels/code-review/custom-user-provider

        $this->registerPolicies();

        /** Explicitly define the policy methods for non-model policies */
//        Gate::define('test', [NarrativeApiPolicy::class, 'test']);
        Gate::define('narrativeLicensed', [NarrativeApiPolicy::class, 'narrativeLicensed']);


        Auth::provider('nvoq-user-provider', function (Application $app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...

            return new NvoqUserProvider();
//            return new NvoqUserProvider($app->make('request'));
        });

        /// end

        // add custom guard

        /*
         * @param $name guard name in config\auth.php, e.g. nvoq-auth-guard
         * @param $app App class
         * @param $config array e.g. ["driver", "nvoq-auth-driver"]
         *
         * @res $app->make('request') = makes an Illuminate\http\request with current url, method and data
         */
/*
        Auth::provider('nvoq-user-provider', function (Application $app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...

            return new NvoqUserProvider();
//            return new NvoqUserProvider($app->make('request'));
        });*/

     /*   Auth::extend('nvoq-auth-driver', function ($app, $name, array $config) {
//            dd($app, $name, $config, $app->make('request'));
            return new NvoqGuard($app->make('request'));
        });*/


        /* Auth::viaRequest('nvoq-auth-driver', function (Request $request) {
             dd($request);
             return null;

          /*   try{
                 $tokenPayload = JWT::decode($request->bearerToken(), new Key(config('jwt.key'), 'HS256'));

                 return \App\Models\User::find($tokenPayload)->first();
             } catch(\Exception $th){
                 Log::error($th);
                 return null;
             }*/
//        });*/

//        Auth::viaRequest('nvoq-auth-driver', function (Request $request) {
//            try{
//                $tokenPayload = JWT::decode($request->bearerToken(), new Key(config('jwt.key'), 'HS256'));
//
//                return \App\Models\User::find($tokenPayload)->first();
//            } catch(\Exception $th){
//                Log::error($th);
//                return null;
//            }
//        });
    }
}
