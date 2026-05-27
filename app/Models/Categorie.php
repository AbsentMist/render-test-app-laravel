<?php

/**
 * @fileoverview Categorie.php
 * @description Modèle Eloquent représentant une catégorie de course (ex: Trail, Route, Marche).
 *              Le flag `modele` distingue les catégories réutilisables dans plusieurs courses
 *              des catégories créées spécifiquement pour une course.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    protected $table      = 'Categorie';
    public    $timestamps = false;
    protected $fillable   = ['nom', 'modele']; // Neris Alessandro (champ modele)

    /**
     * Courses utilisant cette catégorie.
     * @author Neris Alessandro
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'id_categorie');
    }
}