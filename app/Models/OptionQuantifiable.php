<?php

/**
 * @fileoverview OptionQuantifiable.php
 * @description Modèle Eloquent représentant les détails d'une option de type "Quantifiable".
 *              Table satellite d'Option : partage la même clé primaire (id) que l'Option parente.
 *              Définit les bornes minimale et maximale de la quantité que le participant peut choisir.
 *              L'auto-incrément est désactivé car la clé primaire est fournie par la table Option.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionQuantifiable extends Model
{
    protected $table = 'OptionQuantifiable';
    public    $timestamps  = false;

    /**
     * L'auto-incrément est désactivé : l'id est fourni par la table Option parente
     * (clé primaire partagée — table satellite dans une relation 1-1).
     */
    public    $incrementing = false;

    protected $fillable = [
        'id',
        'quantiteMin', // Quantité minimum que le participant doit choisir
        'quantiteMax', // Quantité maximum que le participant peut choisir
    ];
}