<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
   
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