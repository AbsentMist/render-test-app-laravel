<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeRabais extends Model
{
    protected $table = 'CodeRabais';

    protected $fillable = [
        'code',
        'type',
        'valeur',
        'id_course',
        'utilisations_max',
        'utilisations_actuelles',
        'date_expiration',
        'actif',
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'actif'           => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    /**
     * Calcule le montant de rabais à appliquer pour un tarif donné.
     * @param float $tarif
     * @return float
     */
    public function calculerMontantRabais(float $tarif): float
    {
        if ($this->type === 'pourcentage') {
            return round($tarif * ($this->valeur / 100), 2);
        }
        // montant_fixe : on ne dépasse pas le tarif
        return min($this->valeur, $tarif);
    }

    /**
     * Vérifie si le code est utilisable (actif, non expiré, pas épuisé).
     * @return bool
     */
    public function estValide(): bool
    {
        if (!$this->actif) return false;

        if ($this->date_expiration && $this->date_expiration->isPast()) return false;

        if ($this->utilisations_max !== null && $this->utilisations_actuelles >= $this->utilisations_max) return false;

        return true;
    }
}
