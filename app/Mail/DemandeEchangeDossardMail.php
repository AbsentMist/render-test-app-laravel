<?php

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemandeEchangeDossardMail extends Mailable
{
    use Queueable, SerializesModels;

    public Inscription $inscription;

    public function __construct(Inscription $inscription)
    {
        $this->inscription = $inscription;
    }

    public function build()
    {
        return $this->subject('Demande d\'échange de dossard - Running Geneva')
            ->view('emails.demande_echange_dossard');
    }
}