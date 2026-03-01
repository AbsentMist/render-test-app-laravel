<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        //Si admin
        if ($request->user() && $request->user()->role_id === 1) {
            return $next($request); 
        }

        // false --> Accès refusé
        return response()->json(['message' => 'Accès refusé. Vous devez être administrateur.'], 403);
    }
}