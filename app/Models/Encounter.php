<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'organization_id',
        'default_prompt_id',
        'created_by',
        'identifier',
        'notes',
        'encounter_date',
        'transcripts',
        'summary',
        'history_summary',
        'status'
    ];

    protected $casts = [
        'organization_id' => 'int',
        'default_prompt_id' => 'int',
        'created_by' => 'int',
        'status' => 'int'
    ];

    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id');
    }

}
