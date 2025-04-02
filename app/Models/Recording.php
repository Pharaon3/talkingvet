<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'path',
        'seconds'
    ];

    public function encounter(){
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }
}
