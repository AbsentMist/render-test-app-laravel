<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrixEvolutif extends Model
{
    protected $table = 'PrixEvolutif';
    public $timestamps = false;

    protected $fillable = [
        'id_course',
        'type',
        'valeur_debut',
        'valeur_fin',
        'tarif',
        'ordre',
    ];

    protected $casts = [
        'tarif' => 'float',
        'ordre' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
}
