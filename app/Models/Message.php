<?php

/**
 * @fileoverview Message.php
 * @description Modèle Eloquent représentant une notification interne entre utilisateurs.
 *              Le contenu est stocké sous forme de JSON sérialisé dans le champ `content`.
 *              Ce JSON encode le type de notification et les données associées
 *              (ex: échange de dossard refusé, invitation de groupe refusée, membership...).
 *              La structure est interprétée côté application par AuthController et MembershipController.
 * @author Guillermet Jean-Daniel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /** Nom de table en minuscules (convention Laravel par défaut, contrairement aux autres tables) */
    protected $table = 'messages';

    /** Seul le champ `content` (JSON sérialisé) est assignable en masse */
    protected $fillable = [
        'content', // @author Ngoie Steven
    ];
}