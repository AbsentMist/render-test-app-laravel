<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionPourCourse extends Model {
    protected $table = 'OptionPourCourse';

    public $timestamps = false;

    protected $fillable = ['id_option', 'id_course'];
    
    public function course() : BelongsTo{
        return $this->belongsTo(Course::class, 'id_course');
    }
    public function option() : BelongsTo{
        return $this->belongsTo(Option::class, 'id_option');
    }
}