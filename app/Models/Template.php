<?php

/**
 * @fileoverview Template.php
 * @description Modèle Eloquent représentant un template de contenu réutilisable.
 *              Utilisé pour pré-remplir des champs texte complexes dans l'interface admin
 *              (ex: description de course, règlement, conditions particulières).
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table      = 'Template';
    public    $timestamps = false;
    protected $fillable   = ['nom', 'contenu'];
}