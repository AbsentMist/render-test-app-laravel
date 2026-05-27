<?php

/**
 * @fileoverview Resultat.php
 * @description Modèle Eloquent représentant le résultat d'un participant à une course.
 *              Chaque inscription peut avoir un seul résultat contenant le temps
 *              et la position finale. La position est null pour les DNS/DNF (abandon).
 *              Les timestamps automatiques sont désactivés.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultat extends Model
{
    protected $table      = 'Resultat';
    public    $timestamps = false;

    protected $fillable = [
        'temps_course', // Temps au format HH:MM:SS — null si DNS/DNF
        'position',     // Position finale — null si le participant n'a pas terminé (DNS/DNF)
        'id_inscription',
    ];

    /**
     * Inscription à laquelle ce résultat est associé.
     * @author Guillermet Jean-Daniel
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}