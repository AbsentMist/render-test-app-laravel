<?php

/**
 * @fileoverview ChoixOption.php
 * @description Modèle Eloquent représentant le choix d'une option par un participant
 *              lors de son inscription (ex: 2 repas, transport inclus, t-shirt XL).
 *              Correspond à la table pivot entre Inscription et Option, enrichie
 *              du champ `quantite` pour les options de type Quantifiable.
 *              La clé primaire est composite (id_inscription, id_option) ;
 *              l'auto-incrément est désactivé en conséquence.
 *              Les timestamps automatiques sont désactivés.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoixOption extends Model
{
    protected $table = 'ChoixOption';
    public    $timestamps  = false;

    /**
     * Clé primaire composite : l'auto-incrément est désactivé
     * car la combinaison (id_inscription, id_option) est unique.
     */
    public    $incrementing = false;
    protected $primaryKey   = ['id_inscription', 'id_option'];

    protected $fillable = [
        'id_option',
        'id_inscription',
        'quantite', // Null pour les options Cochables, entier pour les options Quantifiables
    ];

    /**
     * Option choisie (ex: T-shirt, Médaille, Repas).
     * @author Neris Alessandro
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'id_option');
    }

    /**
     * Inscription à laquelle ce choix d'option est rattaché.
     * @author Neris Alessandro
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}