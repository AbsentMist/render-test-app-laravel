<?php

/**
 * @fileoverview ConfirmationInscriptionMail.php
 * @description Mailable Laravel envoyé au participant après validation de son inscription.
 *              Utilise le template Blade `emails.confirmation_inscription` pour le contenu.
 *              L'objet complet de l'inscription (avec ses relations) est passé à la vue
 *              pour permettre l'affichage des détails (course, dossard, événement...).
 * @author Ngoie Steven
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationInscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Inscription complète (avec ses relations) transmise à la vue Blade */
    public $inscription;

    /**
     * Initialise le mailable avec l'inscription à confirmer.
     * @author Ngoie Steven
     * @param  mixed $inscription Inscription avec ses relations chargées (course, dossard, événement...).
     */
    public function __construct($inscription)
    {
        $this->inscription = $inscription;
    }

    /**
     * Construit l'email : définit l'objet et le template Blade à utiliser.
     * @author Ngoie Steven
     * @return static Instance du mailable configurée.
     */
    public function build()
    {
        return $this->subject('Confirmation de votre inscription - Running Geneva')
                    ->view('emails.confirmation_inscription');
    }
}