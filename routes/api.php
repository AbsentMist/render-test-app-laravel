<?php

use App\Http\Controllers\AvertissementController;
use App\Http\Controllers\OptionPourCourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\GroupeController;

    // ===== Routes publiques (sans authentification) =====
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // ===== Routes protégées (nécessite d'être connecté) =====
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);

        // Route accessible à tous les utilisateurs (Participants + Admins)
        Route::prefix('participant')->group(function() {
            // Récupération des évènements
            Route::get('/evenements', [EvenementController::class, 'indexParticipant']);

            // Récupération des courses
            Route::get('/courses/{id_evenement}', [CourseController::class, 'indexParticipant']);
            Route::get('/courses/course/{id}', [CourseController::class, 'show']);

            // Récupération des options d'une course
            Route::get('/options/{id_course}', [OptionController::class, 'indexParticipant']);

            Route::get('/avertissements/{id_course}', [AvertissementController::class, 'indexParticipant']);
            // Recherche d'un participant par email (pour inscription relais)
            Route::get('/rechercher-participant', [AuthController::class, 'rechercherParticipant']);

            // CRUD Groupe
            Route::get('/groupes', [GroupeController::class, 'index']);
            Route::post('/groupes', [GroupeController::class, 'store']);
            Route::get('/groupes/{id}', [GroupeController::class, 'show']);
            Route::put('/groupes/{id}', [GroupeController::class, 'update']);
            Route::delete('/groupes/{id}', [GroupeController::class, 'destroy']);

            // Gestion des membres dans le groupe
            Route::post('/groupes/{id}/participants', [GroupeController::class, 'addParticipant']);
            Route::delete('/groupes/{id}/participants/{id_participant}', [GroupeController::class, 'removeParticipant']);

            //Gestion du code participant
            Route::post('/groupes/verifier-code', [GroupeController::class, 'verifierCodeEntreprise']);
        });

        // Gestion du rôle Administrateur par Middleware
        Route::middleware('is_admin')->prefix('organisateur')->group(function () {
            // Routes pour la gestion des événements (CRUD)
            Route::get('/evenements', [EvenementController::class, 'indexAdmin']);
            Route::get('/evenements/{id}', [EvenementController::class, 'show']);
            Route::post('/evenements', [EvenementController::class, 'store']);
            Route::put('/evenements/{id}', [EvenementController::class, 'update']);
            Route::delete('/evenements/{id}', [EvenementController::class, 'destroy']);

            // Routes pour la gestion des courses (CRUD)
            Route::get('/evenements/{id_evenement}/courses', [CourseController::class, 'indexAdmin']);
            Route::get('/courses/{id}', [CourseController::class, 'show']);
            Route::post('/courses', [CourseController::class, 'store']);
            Route::put('/courses/{id}', [CourseController::class, 'update']);
            Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

            // Routes pour la gestion des options (CRUD)
            Route::get('/options', [OptionController::class, 'indexAdmin']);
            Route::get('/options/{id}', [OptionController::class, 'show']);
            Route::post('/options', [OptionController::class, 'store']);
            Route::put('/options/{id}', [OptionController::class, 'update']);
            Route::delete('/options/{id}', [OptionController::class, 'destroy']);

            Route::get('/avertissements', [AvertissementController::class, 'indexAdmin']);
            Route::get('/avertissements/{id}', [AvertissementController::class, 'show']);
            Route::post('/avertissements', [AvertissementController::class, 'store']);
            Route::put('/avertissements/{id}', [AvertissementController::class, 'update']);
            Route::delete('/avertissements/{id}', [AvertissementController::class, 'destroy']);

            Route::get('/optionCourse', [OptionPourCourseController::class, 'indexAdmin']);
            Route::get('/optionCourse/{id_course}/{id_option}', [OptionPourCourseController::class, 'show']);
            Route::post('/optionCourse', [OptionPourCourseController::class, 'store']);
            Route::put('/optionCourse/{id_course}/{id_option}', [OptionPourCourseController::class, 'update']);
            Route::delete('/optionCourse/{id_course}/{id_option}', [OptionPourCourseController::class, 'destroy']);
            Route::delete('/optionCourse/{id_course}', [OptionPourCourseController::class, 'destroyByCourse']);
        });
    });