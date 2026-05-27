<?php

/**
 * @fileoverview Evenement.php
 * @description Modèle Eloquent représentant un événement sportif (ex: Corrida de Genève).
 *              Un événement regroupe plusieurs courses et définit l'identité visuelle
 *              (couleurs, logo) ainsi que les fonctionnalités activées.
 *              Le logo est stocké en BLOB en base de données. Un accessor `logo_base64`
 *              est exposé automatiquement à la place du BLOB brut.
 *              Les timestamps automatiques sont désactivés.
 * @author Ngoie Steven
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    use HasFactory;

    protected $table      = 'Evenement';
    public    $timestamps = false;

    protected $fillable = [
        'nom', 'logo', 'site', 'couleur_primaire', 'couleur_secondaire',
        'is_avertissement', 'is_document', 'is_questionnaire',
        'is_rabais', 'is_actif', 'is_interne',
    ];

    /** Conversion automatique des champs booléens */
    protected $casts = [
        'is_avertissement' => 'boolean',
        'is_document'      => 'boolean',
        'is_questionnaire' => 'boolean',
        'is_rabais'        => 'boolean',
        'is_actif'         => 'boolean',
        'is_interne'       => 'boolean',
    ];

    /**
     * Le champ `logo` (BLOB binaire) est masqué dans la sérialisation JSON.
     * L'attribut calculé `logo_base64` est exposé à la place via $appends.
     */
    protected $hidden  = ['logo'];
    protected $appends = ['logo_base64'];

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Accessor : convertit le BLOB logo en data URI base64 pour l'affichage dans le frontend.
     * Retourne null si aucun logo n'est défini.
     * @author Ngoie Steven
     * @return string|null Data URI base64 (ex: "data:image/jpeg;base64,...") ou null.
     */
    public function getLogoBase64Attribute()
    {
        if ($this->logo) {
            return 'data:image/jpeg;base64,' . base64_encode($this->logo);
        }
        return null;
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Courses rattachées à cet événement.
     * @author Perroud Rémi
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'id_evenement');
    }
}