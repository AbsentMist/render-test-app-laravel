<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Option extends Model
{
    protected $table = 'Options';
    public $timestamps = false;

    
    protected $fillable = ['nom', 'tarif', 'type', 'description', 'image'];

    // Relation Many-to-Many avec Course
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'OptionPourCourse', 'id_option', 'id_course');
    }

    // Relation One-To-One avec OptionQuantifiable (One-to-One)
    public function quantifiable(): HasOne
    {
        return $this->hasOne(OptionQuantifiable::class, 'id');
    }

    // Relation One-To-One avec OptionCochable (One-to-One)
    public function cochable(): HasOne
    {
        return $this->hasOne(OptionCochable::class, 'id');
    }
}