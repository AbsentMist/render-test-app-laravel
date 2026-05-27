<?php

/**
 * @fileoverview OptionCochable.php
 * @description Modèle Eloquent représentant les détails d'une option de type "Cochable".
 *              Table satellite d'Option : partage la même clé primaire (id) que l'Option parente.
 *              Une option cochable est une case à cocher simple sans quantité (ex: transport inclus).
 *              L'auto-incrément est désactivé car la clé primaire est fournie par la table Option.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionCochable extends Model
{
    protected $table = 'OptionCochable';
    public    $timestamps  = false;

    /**
     * L'auto-incrément est désactivé : l'id est fourni par la table Option parente
     * (clé primaire partagée — table satellite dans une relation 1-1).
     */
    public    $incrementing = false;

    protected $fillable = ['id', 'is_coche'];
}