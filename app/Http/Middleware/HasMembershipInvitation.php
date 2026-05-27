<?php

/**
 * @fileoverview HasMembershipInvitation.php
 * @description Middleware vérifiant que l'utilisateur connecté possède une invitation
 *              membership active (status "En cours") avant d'accéder aux routes membership.
 *              Utilisé pour protéger les routes de soumission du formulaire membership
 *              et de checkout de paiement membership.
 * @author Ngoie Steven
 */

namespace App\Http\Middleware;

use App\Models\InvitationMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasMembershipInvitation
{
    /**
     * Vérifie qu'une invitation membership active existe pour l'utilisateur connecté.
     * @author Ngoie Steven
     * @param  Request  $request Requête entrante.
     * @param  Closure  $next    Prochain middleware ou contrôleur.
     * @return Response Réponse suivante, 401 si non authentifié ou 403 si pas d'invitation active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $hasActiveInvitation = InvitationMembership::query()
            ->where('id_user_participant', $user->id)
            ->where('status', 'En cours')
            ->exists();

        if (!$hasActiveInvitation) {
            return response()->json([
                'message' => 'Accès refusé : aucune invitation membership en cours.',
            ], 403);
        }

        return $next($request);
    }
}