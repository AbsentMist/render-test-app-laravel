<?php

/**
 * @fileoverview Participant.php
 * @description Modèle Eloquent représentant le profil sportif d'un utilisateur.
 *              Un compte utilisateur (User) peut avoir plusieurs profils participants
 *              (profil principal + sous-profils pour les proches).
 *              La photo est stockée en BLOB en base de données et automatiquement
 *              convertie en base64 data URI à la lecture via un accessor Eloquent.
 *              Les timestamps automatiques sont désactivés.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $table      = 'Participant';
    public    $timestamps = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_user', 'nom', 'prenom', 'date_naissance', 'equipe_nom',
        'adresse', 'code_postal', 'ville', 'pays', 'telephone',
        'nationalite', 'instagram', 'facebook', 'taille_tshirt', 'sexe', 'photo',
    ];

    // ==========================================
    // ACCESSORS / MUTATORS (Photo BLOB ↔ base64)
    // ==========================================

    /**
     * Accessor : convertit le BLOB photo en data URI base64 à la lecture.
     * Gère trois cas pour éviter les doubles encodages :
     *   1. La valeur est déjà une data URI (commence par "data:") → retournée telle quelle
     *   2. La valeur est une chaîne base64 valide → préfixée avec le header data URI
     *   3. La valeur est un BLOB binaire brut → encodé en base64 puis préfixé
     * @author Ngoie Steven
     * @param  mixed $value Valeur brute issue de la base de données.
     * @return string|null Data URI base64 ou null si aucune photo.
     */
    public function getPhotoAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Déjà une data URI : pas besoin de ré-encoder
        if (is_string($value) && str_starts_with($value, 'data:')) {
            return $value;
        }

        // Chaîne base64 valide : ajoute simplement le header
        if (is_string($value) && base64_decode($value, true) !== false) {
            return 'data:image/jpeg;base64,' . $value;
        }

        // BLOB binaire brut : encodage base64 complet
        return 'data:image/jpeg;base64,' . base64_encode($value);
    }

    /**
     * Mutator : convertit une data URI base64 en BLOB binaire avant stockage.
     * Si la valeur est une data URI (ex: envoyée par le frontend),
     * extrait et décode la partie base64 pour stocker le binaire brut.
     * Les autres formats (BLOB direct) sont stockés sans transformation.
     * @author Ngoie Steven
     * @param  mixed $value Valeur à stocker (data URI ou binaire brut).
     * @return void
     */
    public function setPhotoAttribute($value): void
    {
        if (is_string($value) && str_starts_with($value, 'data:')) {
            $commaPosition = strpos($value, ',');
            $decoded = $commaPosition === false
                ? null
                : base64_decode(substr($value, $commaPosition + 1), true);

            // Stocke le binaire décodé si valide, sinon conserve la chaîne originale
            $this->attributes['photo'] = $decoded !== false && $decoded !== null ? $decoded : $value;
            return;
        }

        $this->attributes['photo'] = $value;
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Compte utilisateur (User) auquel ce profil participant est rattaché.
     * @author Ngoie Steven
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Groupes (relais, entreprise, challenge) auxquels ce participant appartient.
     * Le statut dans le groupe (Fondateur, Membre, En attente) est disponible via le pivot.
     * @author Ngoie Steven
     */
    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'GroupeParticipant', 'id_participant', 'id_groupe')
                    ->withPivot('statut');
    }
}