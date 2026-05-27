<?php

/**
 * @fileoverview OptionQuestion.php
 * @description Modèle Eloquent représentant un choix de réponse pour une question QCM.
 *              Chaque question peut avoir plusieurs options de réponse.
 *              Les participants sélectionnent une option lors de l'inscription ;
 *              les statistiques de sélection sont accessibles via la relation `reponses`.
 * @author Neris Alessandro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionQuestion extends Model
{
    protected $table      = 'OptionQuestion';
    public    $timestamps = false;

    protected $fillable = ['id_question', 'texte_option'];

    /**
     * Question parente à laquelle ce choix de réponse appartient.
     * @author Neris Alessandro
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    /**
     * Réponses des participants ayant sélectionné cette option.
     * Permet de calculer le nombre de sélections pour les statistiques admin.
     * @author Neris Alessandro
     */
    public function reponses(): HasMany
    {
        return $this->hasMany(ReponseQuestion::class, 'id_option_choisie');
    }
}