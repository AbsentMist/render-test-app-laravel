<?php

/**
 * @fileoverview OptionPourCourse.php
 * @description Modèle Eloquent représentant l'association entre une Option et une Course.
 *              Matérialise la table pivot OptionPourCourse avec ses propres relations
 *              pour permettre les requêtes directes sur les associations.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionPourCourse extends Model
{
    protected $table      = 'OptionPourCourse';
    public    $timestamps = false;

    protected $fillable = ['id_option', 'id_course'];

    /**
     * Course associée à cette liaison option-course.
     * @author Neris Alessandro
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    /**
     * Option associée à cette liaison option-course.
     * @author Neris Alessandro
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'id_option');
    }
}