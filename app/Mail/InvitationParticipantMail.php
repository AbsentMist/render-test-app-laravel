<?php

/**
 * @fileoverview InvitationParticipantMail.php
 * @description Mailable Laravel envoyé à un participant invité à s'inscrire sur la plateforme
 *              par un organisateur. Transmet les informations du participant et un mot de passe
 *              provisoire généré automatiquement. Le participant devra le changer à sa première connexion.
 * @author Ngoie Steven
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationParticipantMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Participant invité (nom, prénom, email) */
    public $participant;

    /** Mot de passe provisoire généré pour la première connexion */
    public $motDePasseProvisoire;

    /**
     * Initialise le mailable avec le participant et son mot de passe provisoire.
     * @author Ngoie Steven
     * @param  mixed  $participant          Participant invité.
     * @param  string $motDePasseProvisoire Mot de passe temporaire à transmettre dans l'email.
     */
    public function __construct($participant, $motDePasseProvisoire)
    {
        $this->participant          = $participant;
        $this->motDePasseProvisoire = $motDePasseProvisoire;
    }

    /**
     * Construit l'email d'invitation au participant.
     * @author Ngoie Steven
     * @return static Instance configurée avec l'objet et la vue.
     */
    public function build()
    {
        return $this->subject('Invitation à une course - Running Geneva')
                    ->view('emails.invitation_participant');
    }
}