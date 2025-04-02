<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NvoqNetworkController;
use App\Rules\MatchPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // request reset password email from nvoq

//        dd($request, $request['current_password'], session()->all(), $request->user());

        /*dd($request->input(),
            $request->user(),
            "requested current: " . $request['current_password'],
            "userpass: " . $request->user()->password,
            ($request['current_password'] == $request->user()->password),
        );*/

//        return back()->withErrors(["password" => "nvoq didn't like it"], 'updatePassword');

        // will automatically switch back on validation error and skip the rest of code lines here == return
//        $validated = $request->validateWithBag('updatePassword', [
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', new MatchPassword()],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

//        dd($request['password']);
        return NvoqNetworkController::ResetPassword($request['password']);

//        dd($request->user(), $validated);

        /*$request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);*/

        /* Request Nvoq servers for updates */


    }
}
