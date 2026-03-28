<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseQuestion extends Pivot
{
    protected $table = 'CourseQuestion';
    public $timestamps = false;

    // On indique que la clé primaire est composée
    protected $primaryKey = ['id_course', 'id_question'];
    public $incrementing = false;

    protected $fillable = ['id_course', 'id_question', 'ordre'];
}