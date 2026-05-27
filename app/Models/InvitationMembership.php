<?php

/**
 * @fileoverview InvitationMembership.php
 * @description Modèle Eloquent représentant une invitation de membership envoyée
 *              par un administrateur à un participant.
 *              Cycle de vie du statut : "En cours" → "Complété" / "Annulé".
 *              Une invitation active est requise pour qu'un participant puisse
 *              accéder au formulaire de membership et le soumettre.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvitationMembership extends Model
{
    protected $table      = 'InvitationMembership';
    public    $timestamps = false;

    protected $fillable = [
        'id_user_participant',    // Utilisateur invité
        'id_admin_createur',      // Admin ayant envoyé l'invitation
        'commentaire_admin',      // Message d'accompagnement de l'invitation
        'status',                 // 'En cours' | 'Complété' | 'Annulé'
        'date_invitation',
        'date_annulation',
        'id_admin_annulation',    // Admin ayant annulé l'invitation
        'commentaire_annulation',
    ];

    protected $casts = [
        'date_invitation' => 'datetime',
        'date_annulation' => 'datetime',
    ];

    /**
     * Compte utilisateur du participant invité.
     * @author Ngoie Steven
     */
    public function participantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_participant');
    }

    /**
     * Administrateur ayant créé et envoyé cette invitation.
     * @author Ngoie Steven
     */
    public function adminCreateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_createur');
    }

    /**
     * Administrateur ayant annulé cette invitation (si applicable).
     * @author Ngoie Steven
     */
    public function adminAnnulation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_annulation');
    }

    /**
     * Formulaire de membership soumis en réponse à cette invitation.
     * @author Ngoie Steven
     */
    public function formulaire(): HasOne
    {
        return $this->hasOne(FormulaireMembership::class, 'id_invitation');
    }
}