<?php

namespace App\Mail;

use App\Models\FormulaireMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprobationMembershipMail extends Mailable
{
    use Queueable, SerializesModels;

    public FormulaireMembership $demande;

    public function __construct(FormulaireMembership $demande)
    {
        $this->demande = $demande;
    }

    public function build()
    {
        return $this->subject('Votre membership a été approuvé - Running Geneva')
            ->view('emails.approbation_membership');
    }
}
