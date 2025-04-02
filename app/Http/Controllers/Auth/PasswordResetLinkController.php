<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NvoqNetworkController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(string $country = null)
    {
        return view('auth.forgot-password', ['country' => $country != null ? Str::lower($country) : null]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
//        dd($request->all(), config("app.nvoq_servers"), Arr::divide(config("app.nvoq_servers"))[0]);
        $request->validate([
//            'username' => 'required|string|regex:/\w*$/|max:255|unique:users,username',
            'username' => ['required', 'string'],
            'country' => ['required', Rule::in(Arr::divide(config("app.nvoq_servers"))[0])] // keys only
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
/*        $status = Password::sendResetLink(
            $request->only('username')
        );*/
        $status = NvoqNetworkController::EmailPasswordResetLink($request['username'], $request['country']);

//        return $status == Password::RESET_LINK_SENT
        return $status
                    ? back()->with('status', __('Reset email sent, please check your inbox'))
                    : back()->withInput($request->only('username'))
                            ->withErrors(['global' => __('Something went wrong please try again.')]);
    }
}
