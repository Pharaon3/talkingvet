<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'name',
        'prompt',
        'description',
        'position',
        'is_default',
        'system_default'
    ];

    protected $casts = [
        'position' => 'int',
        'is_default' => 'boolean',
        'system_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }
}
