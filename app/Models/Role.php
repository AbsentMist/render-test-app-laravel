<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // On précise le nom de la table car elle a une majuscule
    protected $table = 'Role'; 

    // On désactive les timestamps car ils ne sont pas dans ta migration
    public $timestamps = false;

    protected $fillable = [
        'type',
    ];

    /**
     * Relation inverse : Un rôle appartient à plusieurs utilisateurs
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'UserRole', 'id_role', 'id_user');
    }
}