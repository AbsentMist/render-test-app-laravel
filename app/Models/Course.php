<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'Course';
    public $timestamps = false;

    //Nom des champs dans DB
    protected $fillable = [
        'id_evenement', 'id_categorie', 'id_sous_categorie', 'id_avertissement', 'nom', 'date_debut', 'date_fin', 
        'debut_inscription', 'fin_inscription', 'tarif', 'status', 'type', 
        'is_challenge', 'is_actif', 'is_dossard', 'is_avertissement', 'is_document', 'is_questionnaire', 'max_inscription', 'max_nb_personne', 'premier_dossard', 
        'dernier_dossard', 'distance', 'heure_depart', 'heure_fin', 
        'age_minimum', 'age_maximum'
    ];

    protected $casts = [
        'is_challenge'      => 'boolean',
        'is_actif'          => 'boolean',
        'is_avertissement'  => 'boolean',
        'is_dossard'        => 'boolean',
        'is_document'       => 'boolean',
        'is_questionnaire'  => 'boolean',

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

    public function avertissement(): BelongsTo{
        return $this->belongsTo(Avertissement::class, 'id_avertissement');
    }

    public function inscriptions(): HasMany {
        return $this->hasMany(Inscription::class, 'id_course');
    }

    public function options(): BelongsToMany {
        return $this->belongsToMany(Option::class,'OptionPourCourse', 'id_course', 'id_option');
    }
}