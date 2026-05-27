<?php

/**
 * @fileoverview PrixEvolutif.php
 * @description Modèle Eloquent représentant un palier de prix évolutif pour une course.
 *              Deux types de paliers sont supportés :
 *              - "dossards" : plages basées sur le nombre d'inscrits (valeur_debut/fin = entiers)
 *              - "dates"    : plages basées sur des dates d'inscription (valeur_debut/fin = dates ISO)
 *              Les paliers sont ordonnés et évalués séquentiellement pour déterminer
 *              le tarif applicable au prochain inscrit.
 *              Les timestamps automatiques sont désactivés.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrixEvolutif extends Model
{
    protected $table      = 'PrixEvolutif';
    public    $timestamps = false;

    protected $fillable = [
        'id_course',
        'type',         // 'dossards' ou 'dates'
        'valeur_debut', // Début de la plage (nombre d'inscrits ou date ISO selon le type)
        'valeur_fin',   // Fin de la plage — null = dernier palier (illimité)
        'tarif',        // Tarif applicable dans cette plage
        'ordre',        // Position du palier dans la séquence (1 = premier)
    ];

    protected $casts = [
        'tarif' => 'float',
        'ordre' => 'integer',
    ];

    /**
     * Course à laquelle ce palier de prix est associé.
     * @author Guillermet Jean-Daniel
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
}