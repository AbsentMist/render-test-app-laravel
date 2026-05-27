<?php

/**
 * @fileoverview Groupe.php
 * @description Modèle Eloquent représentant un groupe de participants.
 *              Trois types de groupes sont supportés : Groupe (relais), Entreprise et Challenge.
 *              Les groupes de type Relais sont automatiquement normalisés en "Groupe" via
 *              un hook Eloquent sur la création.
 *              Les groupes Entreprise possèdent un code unique préfixé "E-" pour
 *              permettre aux participants de rejoindre le groupe lors de l'inscription.
 *              Les timestamps automatiques sont désactivés.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Course;

class Groupe extends Model
{
    use HasFactory;

    protected $table      = 'Groupe';
    public    $timestamps = false;

    protected $fillable = [
        'nom',
        'type',
        'code_entreprise',
        'id_course', // Permet d'utiliser le même nom de groupe pour des courses différentes
    ];

    // ==========================================
    // HOOKS ELOQUENT
    // ==========================================

    /**
     * Hook de création : normalise le type "Relais" en "Groupe".
     * "Relais" est un libellé frontend ; en base de données, la valeur stockée est "Groupe".
     * @author Ngoie Steven
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($groupe) {
            if (strtolower($groupe->type) === 'relais') {
                $groupe->type = 'Groupe';
            }
        });
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Participants membres de ce groupe avec leur statut dans la table pivot GroupeParticipant.
     * Le statut peut être : Fondateur, Membre ou En attente.
     * @author Ngoie Steven
     */
    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'GroupeParticipant', 'id_groupe', 'id_participant')
                    ->withPivot('statut');
    }

    /**
     * Course à laquelle ce groupe est associé.
     * Permet de distinguer des groupes de même nom sur des courses différentes.
     * @author Guillermet Jean-Daniel
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    /**
     * Inscriptions liées à ce groupe.
     * Utilisé pour vérifier si le groupe est encore actif (inscriptions non annulées).
     * @author Guillermet Jean-Daniel
     */
    public function inscriptions()
    {
        return $this->hasMany(\App\Models\Inscription::class, 'id_groupe');
    }
}