<?php

/**
 * @fileoverview MessageController.php
 * @description Contrôleur de test utilisé lors du développement initial de l'application.
 *              Affiche le premier message de la base de données dans la vue welcome.
 *              Ce contrôleur n'est plus utilisé dans l'application finale ;
 *              la gestion des notifications est assurée par AuthController::mesNotificationsInfo().
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Affiche le premier message de la base dans la vue welcome (test de démarrage).
     * @author Guillermet Jean-Daniel
     * @return View Vue welcome avec le premier message.
     */
    public function index(): View
    {
        $message = Message::first();
        return view('welcome', compact('message'));
    }
}