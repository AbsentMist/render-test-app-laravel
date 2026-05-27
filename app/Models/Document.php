<?php

/**
 * @fileoverview Document.php
 * @description Modèle Eloquent représentant un document fourni par un participant
 *              lors de son inscription (ex: certificat médical, attestation).
 *              Un document peut être lié à un participant (document personnel réutilisable)
 *              et/ou à une inscription spécifique (document fourni pour cette course).
 *              Les timestamps automatiques sont désactivés.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table      = 'Document';
    public    $timestamps = false;

    protected $fillable = [
        'url',            // Chemin ou URL du fichier stocké
        'date_debut',     // Date de début de validité du document
        'date_fin',       // Date de fin de validité du document
        'valable',        // Indique si le document est encore valide
        'id_participant',
        'id_inscription',
    ];

    /**
     * Participant propriétaire de ce document.
     * @author Neris Alessandro
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'id_participant');
    }

    /**
     * Inscription à laquelle ce document a été fourni.
     * @author Neris Alessandro
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}