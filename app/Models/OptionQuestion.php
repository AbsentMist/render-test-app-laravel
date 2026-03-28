<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionQuestion extends Model
{
    protected $table = 'OptionQuestion';
    public $timestamps = false;

    protected $fillable = ['id_question', 'texte_option'];

    /**
     * Relation vers la Question parente
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    /**
     * Relation avec les réponses des participants 
     * (Pour savoir combien de personnes ont choisi cette option précise)
     */
    public function reponses(): HasMany
    {
        return $this->hasMany(ReponseQuestion::class, 'id_option_choisie');
    }
}