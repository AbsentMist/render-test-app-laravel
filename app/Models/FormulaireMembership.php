<?php

/**
 * @fileoverview FormulaireMembership.php
 * @description Modèle Eloquent représentant le formulaire de demande de membership
 *              soumis par un participant suite à une invitation d'un administrateur.
 *              Cycle de vie du statut : "À compléter" → "En attente de validation"
 *              → "Approuvée" / "Annulé".
 *              Ce modèle a été renommé depuis DemandeMembership lors d'une refactorisation.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaireMembership extends Model
{
    protected $table      = 'FormulaireMembership';
    public    $timestamps = false;

    protected $fillable = [
        'id_invitation',      // Lien vers l'invitation qui a déclenché ce formulaire
        'nom',
        'prenom',
        'email',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'telephone',
        'date_naissance',
        'description',        // Motivation du candidat (min 10 caractères)
        'status',             // 'À compléter' | 'En attente de validation' | 'Approuvée' | 'Annulé'
        'date_creation',
        'date_decision',
        'id_admin_decideur',  // Admin ayant approuvé ou refusé
        'notes_admin',        // Commentaire de l'admin lors du refus ou renvoi
        'prix',               // Montant du membership payé
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_creation'  => 'datetime',
        'date_decision'  => 'datetime',
        'prix'           => 'decimal:2',
    ];

    /**
     * Administrateur ayant pris la décision d'approbation ou de refus.
     * @author Ngoie Steven
     */
    public function adminDecideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_decideur');
    }

    /**
     * Invitation membership ayant déclenché la création de ce formulaire.
     * @author Ngoie Steven
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(InvitationMembership::class, 'id_invitation');
    }
}