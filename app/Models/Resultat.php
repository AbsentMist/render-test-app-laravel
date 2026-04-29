<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultat extends Model
{
    protected $table = 'Resultat';

    public $timestamps = false;

    protected $fillable = [
        'temps_course',
        'position',
        'id_inscription',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}
