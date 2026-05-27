<?php

/**
 * @fileoverview SousCategorie.php
 * @description Modèle Eloquent représentant une sous-catégorie de course (ex: Senior H, Junior F).
 *              Fonctionne de manière identique à Categorie :
 *              le flag `modele` distingue les sous-catégories réutilisables.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SousCategorie extends Model
{
    protected $table      = 'SousCategorie';
    public    $timestamps = false;
    protected $fillable   = ['nom', 'modele']; // Neris Alessandro (champ modele)

    /**
     * Courses utilisant cette sous-catégorie.
     * @author Neris Alessandro
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'id_sous_categorie');
    }
}