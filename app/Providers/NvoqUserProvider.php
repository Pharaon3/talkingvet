<?php

namespace App\Providers;

use App\Http\Controllers\NvoqNetworkController;
use App\Models\Nvoq\NvoqUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Validation\ValidationException;

class NvoqUserProvider implements UserProvider
{
    public function __construct()
    {
    }

    public function retrieveById($identifier)
    {

        /*dd($identifier, session(), session('username'),
            new GenericUser([
                'username' => fake()->userName(),
                'email' => fake()->email(),
            ]));*/

//        dd($identifier, session()->all());

        if(session('username') == $identifier)
        {
            return new \App\Models\Nvoq\NvoqUser([
                'username' => $identifier,
                'password' => session('password'),
                'country' => session('country'),
                'isAdmin' => session('isAdmin') // NvoqNetworkController.php @Login()
            ]);
        }
        else{
            return null;
        }
    }

    public function retrieveByToken($identifier, $token)
    {
//        dd($identifier, $token);
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        dd($user, $token);
        // nothing
    }

    // shouldn't validate anything
    public function retrieveByCredentials(array $credentials)
    {
//        dd('retr\n', $credentials);
        // should return a user model
        if (! array_key_exists('username', $credentials)) {
            return null;
        }
//        dd($credentials); // username & password

        // GenericUser is a class from Laravel Auth System
        return new NvoqUser($credentials);
/*
        return new NvoqUser([
            'username' => $credentials['username'],
            'password' => ""
//            'email' => $credentials['email'],
        ]);


        return new User([
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'server' => config("app.nvoq_servers")[$credentials['country']]
            ]
        );*/

    }

    /**
     * @throws ValidationException
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // ---------- https://laracasts.com/discuss/channels/code-review/custom-user-provider
        if (! array_key_exists('password', $credentials)) {
            return false;
        }
        return NvoqNetworkController::Login($credentials['username'], $credentials['password'],
        $credentials['country']);
    }
}
