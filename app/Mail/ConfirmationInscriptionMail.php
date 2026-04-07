<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationInscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inscription;

    public function __construct($inscription)
    {
        $this->inscription = $inscription;
    }

    public function build()
    {
        return $this->subject('Confirmation de votre inscription - Running Geneva')
                    ->view('emails.confirmation_inscription');
    }
}
