<?php

/**
 * @fileoverview User.php
 * @description Modèle Eloquent représentant un compte utilisateur de l'application.
 *              Étend Authenticatable pour la gestion de l'authentification Laravel.
 *              Utilise Laravel Sanctum pour l'émission des tokens d'API.
 *              Un compte peut posséder un ou plusieurs profils Participant et un ou plusieurs rôles.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'User';

    /** Seuls email et password sont assignables en masse */
    protected $fillable = [
        'email',
        'password',
    ];

    /** Le mot de passe et le token de session ne sont jamais exposés dans les réponses JSON */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversions automatiques des attributs.
     * Le mot de passe est automatiquement haché par Laravel lors de l'assignation.
     * @author Guillermet Jean-Daniel
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Profil participant principal rattaché à ce compte.
     * Un compte peut aussi avoir des sous-profils (famille, proches) via la même relation.
     * @author Guillermet Jean-Daniel
     */
    public function participant()
    {
        return $this->hasOne(Participant::class, 'id_user', 'id');
    }

    /**
     * Rôles attribués à ce compte (ex: Administrateur, Membre).
     * Liés via la table pivot UserRole.
     * @author Ngoie Steven
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'UserRole', 'id_user', 'id_role');
    }
}