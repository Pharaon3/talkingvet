<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        /* using
                public function failed(?Throwable $exception): void
            instead

            in Job class
        */
//        Queue::failing(function (JobFailed $event) {
            // $event->connectionName
            // $event->job
            // $event->exception

            /* test */
//            $test = "test";

            // $event->exception->getMessage()
            // $event->connectionName gives 'sync'
//        });
        View::composer('layouts.navigation', function ($view) {
            $user = $this->getAuthenticatedUser();

            $view->with('auth_user', $user);
        });
    }

    private function getAuthenticatedUser()
    {
        $guards = ['internal-auth-guard', 'web', 'nvoq-auth-guard']; // Add your guards in order of precedence

        foreach ($guards as $guard) {
            if ($user = Auth::guard($guard)->user()) {
                return $user;
            }
        }

        return null;
    }
}
