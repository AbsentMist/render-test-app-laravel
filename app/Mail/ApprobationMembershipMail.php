<?php

/**
 * @fileoverview ApprobationMembershipMail.php
 * @description Mailable Laravel envoyé au candidat lorsqu'un administrateur approuve
 *              sa demande de membership. Passe le formulaire complet à la vue Blade
 *              pour permettre l'affichage des détails de l'approbation (prix, notes...).
 * @author Ngoie Steven
 */

namespace App\Mail;

use App\Models\FormulaireMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprobationMembershipMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Formulaire de membership approuvé, transmis à la vue Blade */
    public FormulaireMembership $demande;

    /**
     * Initialise le mailable avec le formulaire de membership approuvé.
     * @author Ngoie Steven
     * @param  FormulaireMembership $demande Formulaire dont le statut vient de passer à "Approuvée".
     */
    public function __construct(FormulaireMembership $demande)
    {
        $this->demande = $demande;
    }

    /**
     * Construit l'email d'approbation de membership.
     * @author Ngoie Steven
     * @return static Instance configurée avec l'objet et la vue.
     */
    public function build()
    {
        return $this->subject('Votre membership a été approuvé - Running Geneva')
            ->view('emails.approbation_membership');
    }
}