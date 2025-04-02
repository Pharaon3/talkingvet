<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request, string $country = null, string $username = null)
    {
        // This is causing issues. if http request fails, login page doesn't appear and reports error
        // May also be causing some 500 server errors on fail
        // if($country == null && $request->ip() != null)
        // {
//            $locale = request()->server('HTTP_ACCEPT_LANGUAGE');

            // $json = file_get_contents('https://geolocation-db.com/json/' . $request->ip());
            // $data = json_decode($json);
            // if($data != null)
            // {
            //     if($data->country_code == "CA")
            //     {
            //         $country = 'canada';
            //     }
            //     else if($data->country_code == "US")
            //     {
            //         $country = 'usa';
            //     }
//                else
//                {
                    /* Leave $country = null to let app select the default */
//                }
            // }
        // }
        return view('auth.login',
            [
                'country' => $country != null ? Str::lower($country) : null,
                'username' => $username != null ? Str::lower($username) : null
            ]
        );
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::debug("Request ." . $request->only('username', 'password', 'country'));
//        return back()->withError('User not found by ID ' . $request->input('password'))->withInput();
//        return back()->withErrors(['password' => 'User not found by ID ' . $request->input('password')])->withInput();

        // https://laracasts.com/discuss/channels/code-review/custom-user-provider

        if(Auth::guard()->attempt(\request()->only('username', 'password','country')))
        {
            session($request->only('username', 'password', 'country')); // save user data to session
            $request->session()->regenerate(); // re-generate session identifier to prevent attacks
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // else
        return back()->withErrors([
            'global' => 'invalid credentials'
        ])->withInput();

        /// end

        /*$request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
        */
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::debug("In the logout destroy method");
        dd("Logout method called");

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function logout(Request $request)
    {
        // Log out the user
        Auth::guard('web')->logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Redirect to the login page
        return redirect('/login')->with('status', 'You have been logged out successfully.');

    }
}
