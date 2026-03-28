<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReponseQuestion extends Model
{
    // On précise le nom exact de la table
    protected $table = 'ReponseQuestion';
    
    // Pas de colonnes created_at/updated_at dans ta migration
    public $timestamps = false;

    protected $fillable = [
        'id_question', 
        'id_option_choisie', 
        'id_inscription'
    ];

    /**
     * La question à laquelle le participant répond.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    /**
     * L'option spécifique choisie (si c'est un QCM).
     * Pointe vers la table OptionQuestion.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(OptionQuestion::class, 'id_option_choisie');
    }

    /**
     * L'inscription liée à cette réponse.
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}