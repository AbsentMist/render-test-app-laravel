<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscription extends Model
{
    
    protected $table = 'Inscription';
    
    
    public $timestamps = false;

    
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    // à continuer avec les autres relations dans les prochains sprints.
}