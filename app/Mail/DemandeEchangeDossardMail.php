<?php

/**
 * @fileoverview DemandeEchangeDossardMail.php
 * @description Mailable Laravel envoyé au participant cible lorsqu'un autre participant
 *              lui propose un échange de dossard. Passe l'inscription du demandeur
 *              à la vue Blade pour afficher les détails de la demande (course, dossard, nom).
 * @author Ngoie Steven
 */

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemandeEchangeDossardMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Inscription du participant qui initie la demande d'échange */
    public Inscription $inscription;

    /**
     * Initialise le mailable avec l'inscription du demandeur.
     * @author Ngoie Steven
     * @param  Inscription $inscription Inscription source de la demande d'échange.
     */
    public function __construct(Inscription $inscription)
    {
        $this->inscription = $inscription;
    }

    /**
     * Construit l'email de demande d'échange de dossard.
     * @author Ngoie Steven
     * @return static Instance configurée avec l'objet et la vue.
     */
    public function build()
    {
        return $this->subject('Demande d\'échange de dossard - Running Geneva')
            ->view('emails.demande_echange_dossard');
    }
}