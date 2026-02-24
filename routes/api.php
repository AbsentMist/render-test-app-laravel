<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\CourseController;


    // ===== Routes publiques (sans authentification) =====
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // ===== Routes protégées (nécessite d'être connecté) =====
    //Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
        
        // Route accessible à tous les utilisateurs (Participants + Admins)
        Route::prefix('participant')->group(function() {
            //Route pour la récupération des évènements
            Route::get('/evenements', [EvenementController::class, 'indexParticipant']); 

            //Route pour la récupération des courses
            Route::get('/courses/{id_evenement}', [CourseController::class, 'indexParticipant']);
            Route::get('/courses/course/{id}', [CourseController::class, 'show']);
        });
        
        //Gestion du rôle Administrateur par Middleware (3.1) - à décomenter plus tard
        //Route::middleware('is_admin')->prefix('organisateur')->group(function () {

        // Disponible sur l'URL : /api/organisateur/*
        Route::prefix('organisateur')->group(function () {
            // Routes pour la gestion des événements (CRUD)
            Route::get('/evenements', [EvenementController::class, 'indexAdmin']);
            Route::get('/evenements/{id}', [EvenementController::class, 'show']);
            Route::post('/evenements', [EvenementController::class, 'store']);
            Route::put('/evenements/{id}', [EvenementController::class, 'update']);
            Route::delete('/evenements/{id}', [EvenementController::class, 'destroy']);

            // Routes pour la gestion des courses (CRUD)
            Route::get('/courses/{id_evenement}', [CourseController::class, 'indexAdmin']);
            Route::post('/courses', [CourseController::class, 'store']);
            Route::put('/courses/{id}', [CourseController::class, 'update']);
            Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
        });
//});