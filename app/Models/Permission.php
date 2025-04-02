<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'role'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    function organization(){
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
