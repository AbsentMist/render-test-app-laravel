<?php

/**
 * @fileoverview ChallengeOrganisation.php
 * @description Modèle Eloquent représentant une organisation (entreprise ou association)
 *              pouvant participer au challenge d'une course.
 *              Deux types sont supportés : Groupe et Entreprise.
 *              La liste des organisations est définie par l'admin pour chaque course ;
 *              les participants choisissent leur organisation lors de l'inscription au challenge.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeOrganisation extends Model
{
    protected $table      = 'ChallengeOrganisation';
    public    $timestamps = false;

    protected $fillable = [
        'id_course',
        'nom',
        'type', // 'Groupe' ou 'Entreprise'
    ];

    /**
     * Course à laquelle cette organisation de challenge est associée.
     * @author Guillermet Jean-Daniel
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
}