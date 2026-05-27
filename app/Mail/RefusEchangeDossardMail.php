<?php

/**
 * @fileoverview RefusEchangeDossardMail.php
 * @description Mailable Laravel envoyé au participant demandeur lorsque le participant
 *              cible refuse une demande d'échange de dossard.
 *              Passe les deux inscriptions concernées à la vue Blade pour permettre
 *              l'affichage du contexte complet de l'échange refusé.
 * @author Ngoie Steven
 */

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefusEchangeDossardMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Inscription du participant qui a initié la demande d'échange */
    public Inscription $inscriptionA;

    /** Inscription du participant qui a refusé l'échange */
    public Inscription $inscriptionB;

    /**
     * Initialise le mailable avec les deux inscriptions de l'échange refusé.
     * @author Ngoie Steven
     * @param  Inscription $inscriptionA Inscription du demandeur (qui reçoit le refus).
     * @param  Inscription $inscriptionB Inscription du refusant.
     */
    public function __construct(Inscription $inscriptionA, Inscription $inscriptionB)
    {
        $this->inscriptionA = $inscriptionA;
        $this->inscriptionB = $inscriptionB;
    }

    /**
     * Construit l'email de refus d'échange de dossard.
     * @author Ngoie Steven
     * @return static Instance configurée avec l'objet et la vue.
     */
    public function build()
    {
        return $this->subject('Refus d\'échange de dossard - Running Geneva')
            ->view('emails.refus_echange_dossard');
    }
}