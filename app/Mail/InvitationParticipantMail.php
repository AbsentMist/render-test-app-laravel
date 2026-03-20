<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationParticipantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $participant;
    public $motDePasseProvisoire;

    // On donne le mdp provisoire en paramètre pour l'inclure dans l'email
    public function __construct($participant, $motDePasseProvisoire)
    {
        $this->participant = $participant;
        $this->motDePasseProvisoire = $motDePasseProvisoire;
    }

    public function build()
    {
        // On construit l'email directement en HTML (à changer plus tard)
        return $this->subject('Invitation à une course - Running Geneva')
                    ->html("
                        <h2>Bonjour {$this->participant->prenom},</h2>
                        <p>Une personne vient de vous inscrire à une course sur Running Geneva !</p>
                        <p>Un compte a été créé automatiquement pour vous permettre de suivre votre inscription et valider votre équipe.</p>
                        <br>
                        <p><b>Vos identifiants de connexion :</b></p>
                        <ul>
                            <li><b>Email :</b> {$this->participant->user->email}</li>
                            <li><b>Mot de passe provisoire :</b> {$this->motDePasseProvisoire}</li>
                        </ul>
                        <br>
                        <p><i>Nous vous conseillons de vous connecter et de modifier ce mot de passe depuis votre profil.</i></p>
                        <p>À bientôt,<br>L'équipe Running Geneva</p>
                    ");
    }
}