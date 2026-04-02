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

    public function __construct($participant, $motDePasseProvisoire)
    {
        $this->participant = $participant;
        $this->motDePasseProvisoire = $motDePasseProvisoire;
    }

    public function build()
    {
        // On remplace le ->html() par un ->view() pointant vers notre nouveau fichier
        return $this->subject('Invitation à une course - Running Geneva')
                    ->view('emails.invitation_participant');
    }
}