<?php

/**
 * @fileoverview CodeRabais.php
 * @description Modèle Eloquent représentant un code de réduction applicable lors de l'inscription.
 *              Deux types de rabais sont supportés : pourcentage (%) et montant fixe (CHF).
 *              La validité du code est conditionnée par trois critères cumulatifs :
 *              le flag actif, la date d'expiration et le nombre maximum d'utilisations.
 *              Expose des méthodes métier pour le calcul et la vérification de validité.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeRabais extends Model
{
    protected $table = 'CodeRabais';

    protected $fillable = [
        'code',
        'type',                   // 'pourcentage' ou 'montant_fixe'
        'valeur',                 // Valeur du rabais (% ou CHF selon le type)
        'id_course',
        'utilisations_max',       // Null = utilisations illimitées
        'utilisations_actuelles',
        'date_expiration',        // Null = pas de date d'expiration
        'actif',
    ];

    /** Conversions automatiques : date_expiration en objet Carbon, actif en booléen */
    protected $casts = [
        'date_expiration' => 'date',
        'actif'           => 'boolean',
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Course à laquelle ce code de rabais est applicable.
     * Un code est toujours lié à une course spécifique.
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
     * Calcule le montant de rabais à appliquer pour un tarif donné.
     * Pour un rabais en pourcentage : arrondi à 2 décimales.
     * Pour un montant fixe : plafonné au tarif pour éviter un prix négatif.
     * @author Guillermet Jean-Daniel
     * @param  float $tarif Tarif de base sur lequel appliquer le rabais.
     * @return float Montant du rabais en CHF.
     */
    public function calculerMontantRabais(float $tarif): float
    {
        if ($this->type === 'pourcentage') {
            return round($tarif * ($this->valeur / 100), 2);
        }
        // Montant fixe : ne peut pas dépasser le tarif (évite un prix négatif)
        return min($this->valeur, $tarif);
    }

    /**
     * Vérifie si le code est encore utilisable.
     * Un code est valide uniquement si les trois conditions sont remplies simultanément :
     *   1. Le flag `actif` est à true
     *   2. La date d'expiration n'est pas dépassée (ou absente)
     *   3. Le nombre d'utilisations n'a pas atteint le maximum (ou pas de limite)
     * @author Guillermet Jean-Daniel
     * @return bool True si le code peut être appliqué.
     */
    public function estValide(): bool
    {
        if (!$this->actif) return false;

        if ($this->date_expiration && $this->date_expiration->isPast()) return false;

        if ($this->utilisations_max !== null && $this->utilisations_actuelles >= $this->utilisations_max) return false;

        return true;
    }
}