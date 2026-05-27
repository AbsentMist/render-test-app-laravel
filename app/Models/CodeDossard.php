<?php

/**
 * @fileoverview CodeDossard.php
 * @description Modèle Eloquent représentant un code permettant d'attribuer un dossard
 *              personnalisé à un participant lors de l'inscription.
 *              À la différence du numéro de dossard automatique, un CodeDossard permet
 *              d'associer un nom personnalisé (ex: nom de l'entreprise, sponsor).
 *              La validité est basée uniquement sur le nombre d'utilisations restantes.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeDossard extends Model
{
    protected $table = 'CodeDossard';

    protected $fillable = [
        'code',
        'nom_personnalise',       // Nom affiché sur le dossard à la place du numéro
        'id_course',
        'utilisations_max',       // Nombre maximum d'utilisations autorisées
        'utilisations_actuelles', // Compteur incrémenté à chaque utilisation
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Course à laquelle ce code de dossard est lié.
     * @author Guillermet Jean-Daniel
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    // ==========================================
    // MÉTHODES MÉTIER
    // ==========================================

    /**
     * Vérifie si le code est encore utilisable.
     * Un code est valide tant que le nombre d'utilisations actuelles
     * est strictement inférieur au maximum autorisé.
     * @author Guillermet Jean-Daniel
     * @return bool True si le code peut encore être utilisé.
     */
    public function estValide(): bool
    {
        return $this->utilisations_actuelles < $this->utilisations_max;
    }
}