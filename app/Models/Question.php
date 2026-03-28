<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    // On précise le nom de la table car Laravel cherche "questions" par défaut
    protected $table = 'Question';
    
    // Désactive les colonnes created_at et updated_at si elles n'existent pas
    public $timestamps = false;

    protected $fillable = ['enonce', 'modele'];

    /**
     * Relation Many-to-Many avec Course
     * Permet de savoir dans quelles courses cette question est posée.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'CourseQuestion', 'id_question', 'id_course')
            ->using(CourseQuestion::class)
            ->withPivot('ordre')
            ->orderByPivot('ordre');
    }

    /**
     * Relation avec les options de réponses (si c'est un QCM)
     * Calqué sur ta logique d'OptionQuest... dans le MLD.
     */
    public function choix(): HasMany
    {
        return $this->hasMany(OptionQuestion::class, 'id_question');
    }

    /**
     * Relation avec les réponses effectives des participants
     */
    public function reponses(): HasMany
    {
        return $this->hasMany(ReponseQuestion::class, 'id_question');
    }
}