<?php

/**
 * @fileoverview Option.php
 * @description Modèle Eloquent représentant une option proposée aux participants lors de l'inscription.
 *              Deux sous-types sont supportés via des tables dédiées en relation 1-1 :
 *              - OptionQuantifiable : l'utilisateur choisit une quantité (ex: 2 repas)
 *              - OptionCochable     : simple case à cocher (ex: transport inclus)
 *              Une option peut être liée à plusieurs courses via la table pivot OptionPourCourse.
 *              Le flag `modele` indique les options réutilisables dans le formulaire admin.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Option extends Model
{
    /** Nom de table avec majuscule (convention du projet) + "s" pour éviter le conflit SQL */
    protected $table      = 'Options'; // Neris Alessandro
    public    $timestamps = false;

    protected $fillable = ['nom', 'tarif', 'type', 'modele', 'description']; // Neris Alessandro

    /**
     * Courses auxquelles cette option est disponible.
     * Liées via la table pivot OptionPourCourse.
     * @author Ngoie Steven
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'OptionPourCourse', 'id_option', 'id_course');
    }

    /**
     * Détails de quantification pour les options de type Quantifiable.
     * Relation 1-1 basée sur la clé primaire partagée (id).
     * @author Ngoie Steven
     */
    public function quantifiable(): HasOne
    {
        return $this->hasOne(OptionQuantifiable::class, 'id');
    }

    /**
     * Détails pour les options de type Cochable (case à cocher simple).
     * Relation 1-1 basée sur la clé primaire partagée (id).
     * @author Ngoie Steven
     */
    public function cochable(): HasOne
    {
        return $this->hasOne(OptionCochable::class, 'id');
    }
}