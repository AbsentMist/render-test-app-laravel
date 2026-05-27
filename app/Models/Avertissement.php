<?php

/**
 * @fileoverview Avertissement.php
 * @description Modèle Eloquent représentant un avertissement affiché aux participants
 *              lors de l'inscription à une course. Le participant doit explicitement
 *              l'accepter si la course a is_avertissement = true.
 *              Le flag `modele` distingue les avertissements réutilisables.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avertissement extends Model
{
    protected $table      = 'Avertissement';
    public    $timestamps = false;
    protected $fillable   = ['titre', 'contenu', 'modele'];
}