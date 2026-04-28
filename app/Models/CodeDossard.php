<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeDossard extends Model
{
    protected $table = 'CodeDossard';

    protected $fillable = [
        'code',
        'nom_personnalise',
        'id_course',
        'utilisations_max',
        'utilisations_actuelles',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    /**
     * Vérifie si le code est encore utilisable.
     */
    public function estValide(): bool
    {
        return $this->utilisations_actuelles < $this->utilisations_max;
    }
}
