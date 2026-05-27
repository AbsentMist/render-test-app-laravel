<?php

/**
 * @fileoverview Dossard.php
 * @description Modèle Eloquent représentant le dossard attribué à une inscription.
 *              Chaque inscription validée reçoit un dossard dont le numéro est généré
 *              automatiquement dans la plage définie par la course (premier_dossard → dernier_dossard).
 *              Un nom personnalisé peut être associé au dossard via un CodeDossard.
 *              Les timestamps automatiques sont désactivés.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dossard extends Model
{
    protected $table      = 'Dossard';
    public    $timestamps = false;

    protected $fillable = [
        'numero',          // Numéro séquentiel attribué automatiquement dans la plage de la course
        'nom_personnalise', // Nom personnalisé issu d'un CodeDossard (optionnel)
        'retrait_dossard', // Indique si le dossard a été retiré physiquement (0/1)
        'id_inscription',
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Inscription à laquelle ce dossard est associé (relation inverse 1-1).
     * @author Ngoie Steven
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}