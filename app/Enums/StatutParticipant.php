<?php

namespace App\Enums;

enum StatutParticipant: string
{
    case FONDATEUR = 'fondateur';
    case MEMBRE = 'membre';
    case EN_ATTENTE = 'en_attente';
}