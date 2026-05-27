<?php

/**
 * @fileoverview Membre.php
 * @description Modèle Eloquent représentant un membre de l'association RunningGeneva.
 *              Distinct du rôle "Membre" (géré via UserRole) : cette table matérialise
 *              l'appartenance formelle à l'association après approbation du membership.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membre extends Model
{
    protected $table = 'Membre';

    protected $fillable = [
        'id_user',
    ];

    /**
     * Compte utilisateur associé à ce membre.
     * @author Ngoie Steven
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}