<?php

/**
 * @fileoverview StatutParticipant.php
 * @description Enumération PHP 8.1 définissant les statuts possibles d'un participant
 *              dans un groupe. Utilisée dans la table pivot GroupeParticipant.
 *              - FONDATEUR  : créateur du groupe, peut le modifier/supprimer
 *              - MEMBRE     : membre actif ayant accepté l'invitation
 *              - EN_ATTENTE : invitation envoyée, en attente d'acceptation
 * @author Ngoie Steven
 */

namespace App\Enums;

enum StatutParticipant: string
{
    case FONDATEUR  = 'fondateur';
    case MEMBRE     = 'membre';
    case EN_ATTENTE = 'en_attente';
}