<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
//use \Illuminate\Auth\GuardHelpers;
//use Illuminate\Auth\SessionGuard;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'regex:/\w*$/i'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        Auth::guard('nvoq-auth-guard')->logout();

        // use below (full guard implementation) or use Auth:viaRequest as an inline quick guard
        try {
            if(Auth::guard('nvoq-auth-guard')->validate($this->only('username', 'password', 'country')))
            {
                // success

                RateLimiter::clear($this->throttleKey());
            }else{
                // login failed
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
//                'username' => trans('auth.failed'),
                    'username' => "username or password is incorrect",
                ]);
            }
        }catch (ValidationException $ex)
        {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages($ex->errors());
        }
/*
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
//                'username' => trans('auth.failed'),
                'username' => "here?",
            ]);
        }*/
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('username')).'|'.$this->ip());
    }
}
