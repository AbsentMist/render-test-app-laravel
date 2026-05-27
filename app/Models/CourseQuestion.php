<?php

/**
 * @fileoverview CourseQuestion.php
 * @description Modèle Eloquent pivot représentant l'association entre une Course et une Question.
 *              Étend Pivot (et non Model) pour s'intégrer correctement dans les relations
 *              BelongsToMany qui utilisent ->using(CourseQuestion::class).
 *              Le champ `ordre` permet de définir l'ordre d'affichage des questions
 *              pour chaque course de manière indépendante.
 *              La clé primaire est composite (id_course, id_question).
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseQuestion extends Pivot
{
    protected $table = 'CourseQuestion';
    public    $timestamps = false;

    /**
     * Clé primaire composite : une question ne peut apparaître qu'une fois par course.
     * L'auto-incrément est désactivé en conséquence.
     */
    protected $primaryKey = ['id_course', 'id_question'];
    public    $incrementing = false;

    protected $fillable = [
        'id_course',
        'id_question',
        'ordre', // Position de la question dans le questionnaire de cette course
    ];
}