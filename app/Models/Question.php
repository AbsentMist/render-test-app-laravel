<?php

/**
 * @fileoverview Question.php
 * @description Modèle Eloquent représentant une question du questionnaire d'inscription.
 *              Une question peut être associée à plusieurs courses via la table pivot CourseQuestion
 *              qui stocke également l'ordre d'affichage dans chaque course.
 *              Les questions de type QCM ont plusieurs choix de réponses (OptionQuestion).
 *              Le flag `modele` indique les questions réutilisables dans d'autres courses.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** Nom de table explicite (Laravel chercherait "questions" par défaut) */
    protected $table      = 'Question';
    public    $timestamps = false;

    protected $fillable = ['enonce', 'modele'];

    /**
     * Courses dans lesquelles cette question est posée.
     * La relation utilise le modèle pivot CourseQuestion pour accéder au champ `ordre`.
     * Les questions sont triées par leur ordre d'affichage dans chaque course.
     * @author Neris Alessandro
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'CourseQuestion', 'id_question', 'id_course')
            ->using(CourseQuestion::class)
            ->withPivot('ordre')
            ->orderByPivot('ordre');
    }

    /**
     * Choix de réponses disponibles pour cette question (si QCM).
     * @author Neris Alessandro
     */
    public function choix(): HasMany
    {
        return $this->hasMany(OptionQuestion::class, 'id_question');
    }

    /**
     * Réponses effectives des participants à cette question.
     * Utilisé pour les statistiques de réponses dans la vue admin.
     * @author Neris Alessandro
     */
    public function reponses(): HasMany
    {
        return $this->hasMany(ReponseQuestion::class, 'id_question');
    }
}