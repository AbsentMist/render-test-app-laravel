<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dossard extends Model
{
    // Nom exact de la table dans la base de données
    protected $table = 'Dossard';

    // Désactiver les timestamps selon ta migration 
    public $timestamps = false;

    // Les champs autorisés pour l'insertion
    protected $fillable = [
        'numero',
        'nom_personnalise',
        'retrait_dossard',
        'id_inscription',
    ];

    //GESTION DES RELATIONS
    
    //Un dossard appartient à une seule inscription
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}