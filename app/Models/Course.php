<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'Course';
    public $timestamps = false;

    //Nom des champs dans DB
    protected $fillable = [
        'id_evenement', 'id_categorie', 'id_sous_categorie', 'nom', 'date', 
        'debut_inscription', 'fin_inscription', 'tarif', 'status', 'type', 
        'challenge', 'is_actif', 'max_inscription', 'premier_dossard', 
        'dernier_dossard', 'distance', 'heure_depart', 'heure_fin', 
        'age_minimum', 'age_maximum'
    ];

    protected $casts = [
        'challenge' => 'boolean',
        'is_actif' => 'boolean',
        'tarif' => 'float',
        'distance' => 'float',
    ];

    //Gestion des clés étrangères
    public function evenement(): BelongsTo {
        return $this->belongsTo(Evenement::class, 'id_evenement');
    }

    public function categorie(): BelongsTo {
        return $this->belongsTo(Categorie::class, 'id_categorie');
    }

    public function sousCategorie(): BelongsTo {
        return $this->belongsTo(SousCategorie::class, 'id_sous_categorie');
    }

    public function inscriptions(): HasMany {
        return $this->hasMany(Inscription::class, 'id_course');
    }
}