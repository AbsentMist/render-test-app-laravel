<?php

/**
 * @fileoverview Inscription.php
 * @description Modèle Eloquent représentant une inscription d'un participant à une course.
 *              Centrale dans l'application : relie un Participant à une Course et agrège
 *              le dossard, le groupe, les options choisies, les réponses au questionnaire,
 *              les documents fournis et le résultat de course.
 *              Les timestamps automatiques sont désactivés (date_paiement gérée manuellement).
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscription extends Model
{
    protected $table = 'Inscription';

    /** Les timestamps created_at/updated_at sont gérés manuellement via date_paiement */
    public $timestamps = false;

    protected $fillable = [
        'id_participant',
        'id_course',
        'id_groupe',
        'id_document',
        'id_ancienne_inscription', // Référence à l'inscription source pour les échanges de dossard
        'id_ancienne_course',      // Référence à la course source pour les changements de course
        'code_participant',
        'tarif',
        'status_paiement',
        'montant_rabais',
        'avertissement_valide',
        'date_paiement',
        'participe_challenge',  // Indique si le participant participe au challenge de l'événement
        'type_challenge',       // Type de challenge (ex: Individuel, Équipe)
        'equipe_challenge',     // Nom de l'équipe challenge si applicable
    ];

    // ==========================================
    // RELATIONS BELONGSTO (Appartient à)
    // ==========================================

    /**
     * Course à laquelle le participant est inscrit.
     * @author Ngoie Steven
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    /**
     * Inscription source dans le cadre d'un échange de dossard ou d'un changement de course.
     * Relation auto-référentielle sur le même modèle Inscription.
     * @author Neris Alessandro
     */
    public function ancienneInscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_ancienne_inscription');
    }

    /**
     * Participant lié à cette inscription.
     * @author Ngoie Steven
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'id_participant');
    }

    /**
     * Groupe auquel appartient cette inscription (relais, entreprise, challenge).
     * @author Ngoie Steven
     */
    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'id_groupe');
    }

    /**
     * Document d'identité ou certificat médical requis pour cette inscription.
     * @author Ngoie Steven
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'id_document');
    }

    // ==========================================
    // RELATIONS HASONE (Possède un)
    // ==========================================

    /**
     * Dossard attribué à cette inscription (un seul dossard par inscription).
     * @author Ngoie Steven
     */
    public function dossard(): HasOne
    {
        return $this->hasOne(Dossard::class, 'id_inscription');
    }

    /**
     * Résultat de course associé à cette inscription (une inscription = un résultat).
     * @author Ngoie Steven
     */
    public function resultat(): HasOne
    {
        return $this->hasOne(Resultat::class, 'id_inscription');
    }

    // ==========================================
    // RELATIONS HASMANY (Possède plusieurs)
    // ==========================================

    /**
     * Réponses du participant aux questions du questionnaire de la course.
     * @author Ngoie Steven
     */
    public function reponsesQuestions(): HasMany
    {
        return $this->hasMany(ReponseQuestion::class, 'id_inscription');
    }

    /**
     * Choix d'options (repas, transport, t-shirt...) effectués lors de l'inscription.
     * @author Neris Alessandro
     */
    public function choixOptions(): HasMany
    {
        return $this->hasMany(ChoixOption::class, 'id_inscription');
    }

    /**
     * Documents fournis par le participant pour cette inscription
     * (ex: certificat médical, attestation).
     * @author Neris Alessandro
     */
    public function documentsFournis(): HasMany
    {
        return $this->hasMany(Document::class, 'id_inscription');
    }
}