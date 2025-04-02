<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = true;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'firstname',
        'lastname',
        'organization_id',
        'login_server',
        'default_language',
        'enabled',
        'sync_key',
        'sync_needed'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        "sync_needed" => 'boolean'
    ];

    /**
     * Get the primary key name.
     *
     * @return string
     */
    public function get_key_name()
    {
        return 'id';
    }

    /**
     * Get the organization associated with the user.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function permissions(){
        return $this->hasMany(Permission::class);
    }

    public function prompts(){
        return $this->hasMany(Prompt::class);
    }

    public function get_account_list() : array
    {
        $user = Auth::guard('internal-auth-guard')->user();
        $accounts = User::where('organization_id', $user->organization_id)
            ->with('permissions')
            ->get()->toArray();

        return $accounts;
    }

    public function isAdmin(){
        $user = Auth::guard('internal-auth-guard')->user();
        $role = $user->permissions()->where([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id
            ]
        )->first()->role;
        return $role == 2;
    }

}
