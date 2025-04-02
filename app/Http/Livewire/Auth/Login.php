<?php

namespace App\Http\Livewire\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\User;

class Login extends Component
{
    public $username, $password, $country = 'usa';
    public $step = 1;

    public function mount($username = null, $country = null)
    {
        $this->username = $username ? strtolower($username) : null;
        $this->country = $country ? strtolower($country) : 'usa'; // or whatever default you want
    }

    public function nextStep()
    {
        $this->validate(['username' => 'required']);

        $userExists = User::whereRaw('LOWER(username) = ?', [strtolower($this->username)])->exists();
        $this->step = $userExists ? 2 : 3;
    }

    public function previousStep()
    {
        $this->step = 1;
    }

    public function login()
    {
        $this->validate([
            'password' => 'required',
            'country' => 'required_if:step,3',
        ]);

        if ($this->step == 2) {
            $credentials = ['username' => $this->username, 'password' => $this->password];
            if(Auth::guard('internal-auth-guard')->attempt($credentials)){
                session()->regenerate(); // re-generate session identifier to prevent attacks
                $user = Auth::guard('internal-auth-guard')->user();
                $token = $user->createToken('API Token')->plainTextToken;
                return redirect(route('assist.home'));
            }else{
                return back()->withErrors([
                    'global' => 'invalid credentials'
                ])->withInput();
            }
        } else {
            $credentials = ['username' => $this->username, 'password' => $this->password, 'country' => $this->country];
            if (Auth::guard()->attempt($credentials)) {
                session($credentials); // save user data to session
                session()->regenerate(); // re-generate session identifier to prevent attacks
                return redirect()->intended(RouteServiceProvider::HOME);
            } else {
                session()->flash("error", "Please login again");
                session()->flash('error', ["error" => "Invalid Credentials"]);
                return back()->withErrors([
                    'global' => 'invalid credentials'
                ])->withInput();
            }
        }
    }

    public function clearError()
    {
        session()->forget('error');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}