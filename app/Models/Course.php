<?php

/**
 * @fileoverview Course.php
 * @description Modèle Eloquent représentant une course sportive rattachée à un événement.
 *              Une course définit les paramètres d'inscription (dates, tarif, limites, dossards),
 *              les fonctionnalités activées (challenge, questionnaire, options, prix évolutif)
 *              et expose une méthode métier pour vérifier si les inscriptions sont encore ouvertes.
 *              Les timestamps automatiques sont désactivés.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'Course';
    public $timestamps = false;

    protected $fillable = [
        'id_evenement', 'id_categorie', 'id_sous_categorie', 'id_avertissement',
        'nom', 'date_debut', 'date_fin',
        'debut_inscription', 'fin_inscription', 'tarif', 'status', 'type',
        'is_challenge', 'is_actif', 'is_dossard', 'is_avertissement',
        'is_document', 'is_questionnaire', 'max_inscription', 'max_nb_personne',
        'premier_dossard', 'dernier_dossard', 'distance',
        'age_minimum', 'age_maximum', 'is_prix_evolutif', 'document_description',
    ];

    /** Conversions automatiques des champs booléens et numériques */
    protected $casts = [
        'is_challenge'     => 'boolean', // Neris Alessandro
        'is_actif'         => 'boolean', // Neris Alessandro
        'is_avertissement' => 'boolean', // Neris Alessandro
        'is_dossard'       => 'boolean', // Neris Alessandro
        'is_document'      => 'boolean', // Neris Alessandro
        'is_questionnaire' => 'boolean', // Neris Alessandro
        'is_prix_evolutif' => 'boolean', // Guillermet Jean-Daniel
        'tarif'            => 'float',
        'distance'         => 'float',
    ];

    // ==========================================
    // RELATIONS BELONGSTO (Appartient à)
    // ==========================================

    /**
     * Événement parent auquel cette course est rattachée.
     * @author Ngoie Steven
     */
    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'id_evenement');
    }

    /**
     * Catégorie de la course (ex: Trail, Route).
     * @author Ngoie Steven
     */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'id_categorie');
    }

    /**
     * Sous-catégorie de la course (ex: Senior H, Junior F).
     * @author Ngoie Steven
     */
    public function sousCategorie(): BelongsTo
    {
        return $this->belongsTo(SousCategorie::class, 'id_sous_categorie');
    }

    /**
     * Avertissement à accepter par le participant lors de l'inscription.
     * @author Neris Alessandro
     */
    public function avertissement(): BelongsTo
    {
        return $this->belongsTo(Avertissement::class, 'id_avertissement');
    }

    // ==========================================
    // RELATIONS HASMANY (Possède plusieurs)
    // ==========================================

    /**
     * Inscriptions validées ou en attente pour cette course.
     * @author Ngoie Steven
     */
    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'id_course');
    }

    /**
     * Paliers de prix évolutifs associés à cette course, triés par ordre croissant.
     * Utilisés pour calculer le tarif applicable au prochain inscrit.
     * @author Guillermet Jean-Daniel
     */
    public function prixEvolutifs(): HasMany
    {
        return $this->hasMany(PrixEvolutif::class, 'id_course')->orderBy('ordre');
    }

    // ==========================================
    // RELATIONS BELONGSTOMANY (Plusieurs à plusieurs)
    // ==========================================

    /**
     * Options disponibles pour les participants lors de l'inscription à cette course.
     * Liées via la table pivot OptionPourCourse.
     * @author Neris Alessandro
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'OptionPourCourse', 'id_course', 'id_option');
    }

    /**
     * Questions du questionnaire associées à cette course, triées par ordre d'affichage.
     * Le champ `ordre` du pivot CourseQuestion détermine l'ordre de présentation.
     * @author Neris Alessandro
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'CourseQuestion', 'id_course', 'id_question')
                    ->withPivot('ordre')
                    ->orderBy('CourseQuestion.ordre', 'asc');
    }

    // ==========================================
    // MÉTHODES MÉTIER
    // ==========================================

    /**
     * Vérifie si les inscriptions sont encore ouvertes pour cette course.
     * Les inscriptions sont considérées ouvertes jusqu'à la fin de la journée
     * de la date de fin d'inscription (23:59:59). Sans date de fin définie,
     * les inscriptions sont toujours ouvertes.
     * @author Ngoie Steven
     * @return bool True si les inscriptions sont encore acceptées.
     */
    public function isRegistrationOpen(): bool
    {
        // Aucune date de fin définie → inscriptions toujours ouvertes
        if (!$this->fin_inscription) {
            return true;
        }

        // Inclut toute la journée de fin d'inscription (jusqu'à 23:59:59)
        $dateFin = \Carbon\Carbon::parse($this->fin_inscription)->endOfDay();

        return now()->lte($dateFin);
    }
}