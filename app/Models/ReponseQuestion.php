<?php

/**
 * @fileoverview ReponseQuestion.php
 * @description Modèle Eloquent représentant la réponse d'un participant à une question
 *              du questionnaire d'inscription. Chaque réponse lie une inscription à une
 *              question et à l'option de réponse choisie (QCM).
 *              L'unicité est garantie sur la combinaison (id_inscription, id_question).
 *              Les timestamps automatiques sont désactivés.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReponseQuestion extends Model
{
    protected $table      = 'ReponseQuestion';
    public    $timestamps = false;

    protected $fillable = [
        'id_question',
        'id_option_choisie', // Référence à OptionQuestion — null si sans réponse
        'id_inscription',
    ];

    /**
     * Question à laquelle le participant a répondu.
     * @author Neris Alessandro
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    /**
     * Option de réponse choisie parmi les choix disponibles de la question (QCM).
     * Pointe vers la table OptionQuestion.
     * @author Neris Alessandro
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(OptionQuestion::class, 'id_option_choisie');
    }

    /**
     * Inscription associée à cette réponse.
     * @author Neris Alessandro
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}