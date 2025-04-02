<?php

namespace App\Models\Nvoq;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;

class NvoqUser extends GenericUser implements Authenticatable, CanResetPassword
{
//    use HasApiTokens, HasFactory, Notifiable, \Illuminate\Auth\Passwords\CanResetPassword;
    use HasApiTokens, Notifiable, \Illuminate\Auth\Passwords\CanResetPassword;

//    public $incrementing = false;
//    protected $primaryKey = 'username';

//    protected $rememberTokenName = 'remember_token';

    protected $attributes = [
//        'id' => null, 'roleId' => null,
//        'email' => null, 'phone' => null,
        'username' => null, 'country' => 'usa',
        'isAdmin' => false,
//        'firstName' => null, 'lastName' => null,
        'password' => null, 'rememberToken' => null,
//        'registrationLocation' => null,
        'data' => null,
//        'verified' => 0,
//        'createdAt' => null, 'updatedAt' => null,
    ];


    public function isAdmin(): bool
    {
        return Arr::has($this->attributes, 'isAdmin') && $this->attributes['isAdmin'] == true;
    }

//    public function hasNarrativeLicense() : bool
//    {
//        // DONETODO #291 dynamic check for narrative license - moved to policy @see NarrativeApiPolicy
//        return false;
//    }

    public function getAccountsList() : array
    {
        if($this->isAdmin())
        {
            return session('accounts', [$this->getAuthIdentifier()]);
        }else{
            return [$this->getAuthIdentifier()];
        }
    }

    public function getAuthIdentifierName()
    {
        return "username";
    }

    public function getAuthIdentifier()
    {
//        dd($this->attributes);
        return $this->attributes[$this->getAuthIdentifierName()];
    }

    public function getAuthPassword()
    {
        return $this->attributes['password'];
    }

    public function getRememberToken()
    {
        return false;
    }

    public function setRememberToken($value)
    {
        return false;
    }

    public function getRememberTokenName()
    {
        return false;
    }

    public function getEmailForPasswordReset()
    {
        return $this->attributes['username'];
    }

}
