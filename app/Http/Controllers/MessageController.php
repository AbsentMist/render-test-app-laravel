<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Message; // Assurez-vous d'importer votre modèle
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        // Récupère le premier message pour l'affichage
        $message = Message::first();
        return view('welcome', compact('message'));
    }
}
