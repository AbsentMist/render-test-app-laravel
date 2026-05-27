<?php

/**
 * @fileoverview IsAdmin.php
 * @description Middleware vérifiant que l'utilisateur connecté possède le rôle Administrateur.
 *              Utilisé pour protéger les routes de l'espace organisateur/admin.
 *              Retourne 403 si l'utilisateur n'est pas authentifié ou n'a pas le rôle requis.
 * @author Ngoie Steven
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Vérifie que l'utilisateur connecté est administrateur avant de passer la requête.
     * @author Ngoie Steven
     * @param  Request  $request Requête entrante.
     * @param  Closure  $next    Prochain middleware ou contrôleur.
     * @return Response Réponse suivante ou 403 si non autorisé.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->roles()->where('type', 'Administrateur')->exists()) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Accès refusé : Vous devez être administrateur.'
        ], 403);
    }
}