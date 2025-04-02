<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the users for the organization.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the encounters for the organization.
     */
    public function encounters(){
        return $this->hasMany(Encounter::class);
    }

    public function permissions(){
        return $this->hasMany(Permission::class);
    }

    public function prompts(){
        return $this->hasMany(Prompt::class);
    }
}
