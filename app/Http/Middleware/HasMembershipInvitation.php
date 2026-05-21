<?php

namespace App\Http\Middleware;

use App\Models\InvitationMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasMembershipInvitation
{
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