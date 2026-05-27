<?php

/**
 * @fileoverview Role.php
 * @description Modèle Eloquent représentant un rôle utilisateur dans l'application.
 *              Les rôles disponibles sont : Administrateur, Membre.
 *              Liés aux comptes via la table pivot UserRole.
 *              Les timestamps automatiques sont désactivés.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** Nom exact de la table en base (majuscule, contrairement à la convention Laravel) */
    protected $table      = 'Role';
    public    $timestamps = false;

    protected $fillable = [
        'type', // Ex: 'Administrateur', 'Membre'
    ];

    /**
     * Utilisateurs possédant ce rôle.
     * Relation inverse de User::roles() via la table pivot UserRole.
     * @author Ngoie Steven
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'UserRole', 'id_role', 'id_user');
    }
}