<?php

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefusEchangeDossardMail extends Mailable
{
    use Queueable, SerializesModels;

    public Inscription $inscriptionA;
    public Inscription $inscriptionB;

    public function __construct(Inscription $inscriptionA, Inscription $inscriptionB)
    {
        $this->inscriptionA = $inscriptionA;
        $this->inscriptionB = $inscriptionB;
    }

    public function build()
    {
        return $this->subject('Refus d\'échange de dossard - Running Geneva')
            ->view('emails.refus_echange_dossard');
    }
}