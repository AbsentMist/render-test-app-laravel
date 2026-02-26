<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OptionController;


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

            //Route pour la récupération des options d'une course
            Route::get('/options/{id_course}', [OptionController::class, 'indexParticipant']);
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
            Route::get('/evenements/{id_evenement}/courses', [CourseController::class, 'indexAdmin']);
            Route::post('/courses', [CourseController::class, 'store']);
            Route::put('/courses/{id}', [CourseController::class, 'update']);
            Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

            //Route pour la gestion des options (CRUD)
            Route::get('/options', [OptionController::class, 'indexAdmin']);
            Route::get('/options/{id}', [OptionController::class, 'show']);
            Route::post('/options', [OptionController::class, 'store']);
            Route::put('/options/{id}', [OptionController::class, 'update']);
            Route::delete('/options/{id}', [OptionController::class, 'destroy']);
        });
//});