<?php

/**
 * @fileoverview MessageController.php
 * @description Contrôleur de test utilisé lors du développement initial.
 * @deprecated Non utilisé en production — remplacé par AuthController::mesNotificationsInfo()
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