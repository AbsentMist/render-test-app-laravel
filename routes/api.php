<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvenementController;


    // ===== Routes publiques (sans authentification) =====
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // ===== Routes protégées (nécessite d'être connecté) =====
    //Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
        
        // Route accessible à tous les utilisateurs (Participants + Admins)
        Route::get('/evenements', [EvenementController::class, 'indexParticipant']); 

        // Accessible uniquement aux administrateurs ("/organisateur")
        
        //Gestion du rôle Administrateur par Middleware (3.1)
        //Route::middleware('is_admin')->prefix('organisateur')->group(function () {
        Route::prefix('organisateur')->group(function () {
            Route::get('/evenements', [EvenementController::class, 'indexAdmin']);
            Route::get('/evenements/{id}', [EvenementController::class, 'show']);
            Route::post('/evenements', [EvenementController::class, 'store']);
            Route::put('/evenements/{id}', [EvenementController::class, 'update']);
            Route::delete('/evenements/{id}', [EvenementController::class, 'destroy']);
        });
//});