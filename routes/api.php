<?php

use App\Http\Controllers\AvertissementController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\OptionPourCourseController;
use App\Http\Controllers\SousCategorieController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\PayrexxController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReponseQuestionController;
use App\Http\Controllers\OptionQuestionController;
use App\Http\Controllers\CourseQuestionController;
use App\Http\Controllers\ChoixOptionController;
use App\Http\Controllers\DocumentController;

    // ===== Routes publiques (sans authentification) =====
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // ===== Routes protégées (nécessite d'être connecté) =====
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/paiement/gateway', [PayrexxController::class, 'creerGateway']);

        // Route accessible à tous les utilisateurs (Participants + Admins)
        Route::prefix('participant')->group(function() {
            // Récupération des évènements
            Route::get('/evenements', [EvenementController::class, 'indexParticipant']);

            // Récupération des courses
            Route::get('/evenements/{id_evenement}/courses', [CourseController::class, 'indexParticipant']);
            Route::get('/courses/{id}', [CourseController::class, 'show']);

            // Récupération des options d'une course
            Route::get('/options/{id_course}', [OptionController::class, 'indexParticipant']);

            Route::get('/avertissements/{id_course}', [AvertissementController::class, 'indexParticipant']);
            Route::get('/categories/{id_course}', [CategorieController::class, 'indexParticipant']);
            Route::get('/sous-categories/{id_course}', [SousCategorieController::class, 'indexParticipant']);
            // Recherche d'un participant par email (pour inscription relais)
            Route::get('/rechercher-participant', [AuthController::class, 'rechercherParticipant']);

            // Invitation de participants à rejoindre un groupe
            Route::get('/groupes/mes-invitations', [GroupeController::class, 'getInvitations']);
            Route::post('/groupes/{id}/accepter', [GroupeController::class, 'accepterInvitation']);
            Route::post('/groupes/{id}/refuser', [GroupeController::class, 'refuserInvitation']);
            
            // CRUD Groupe
            Route::get('/groupes', [GroupeController::class, 'index']);
            Route::post('/groupes', [GroupeController::class, 'store']);
            Route::get('/groupes/{id}', [GroupeController::class, 'show']);
            Route::put('/groupes/{id}', [GroupeController::class, 'update']);
            Route::delete('/groupes/{id}', [GroupeController::class, 'destroy']);

            //Invitation de participants à rejoindre un groupe (pour inscription relais, Inscription fantôme)
            Route::post('/inviter-participant', [AuthController::class, 'createInvitedUser']);

            // Gestion des membres dans le groupe
            Route::post('/groupes/{id}/participants', [GroupeController::class, 'addParticipant']); //peut-être à supprimer
            Route::delete('/groupes/{id}/participants/{id_participant}', [GroupeController::class, 'removeParticipant']); //peut-être à supprimer
            
            // Gestion des participants liés au compte
            Route::get('/participants', [AuthController::class, 'mesParticipants']);
            Route::post('/participants', [AuthController::class, 'creerParticipant']);
            
            //Gestion du code participant
            Route::post('/groupes/verifier-code', [GroupeController::class, 'verifierCodeEntreprise']);

            //CRUD Inscription
            Route::get('/inscriptions', [InscriptionController::class, 'indexParticipant']);
            Route::post('/inscriptions', [InscriptionController::class, 'store']);
            Route::get('/inscriptions/{id}', [InscriptionController::class, 'show']);
            Route::put('/inscriptions/{id}', [InscriptionController::class, 'updateParticipant']);
            Route::delete('/inscriptions/{id}', [InscriptionController::class, 'destroyParticipant']);

            //CRUD Choix options
            Route::get('/inscriptions/{id_inscription}/choix-options', [ChoixOptionController::class, 'indexParInscription']);
            Route::post('/choix-options', [ChoixOptionController::class, 'store']);
            Route::put('/choix-options/{id_inscription}/{id_option}', [ChoixOptionController::class, 'update']);
            Route::delete('/choix-options/{id_inscription}/{id_option}', [ChoixOptionController::class, 'destroy']);

            // Questions d'une course
            Route::get('/questions/{id_course}', [QuestionController::class, 'indexParticipant']);

            // Réponses aux questions
            Route::get('/inscriptions/{id_inscription}/reponses', [ReponseQuestionController::class, 'indexParInscription']);
            Route::post('/reponses-questions', [ReponseQuestionController::class, 'store']);
            Route::delete('/reponses-questions/{id_inscription}/{id_question}', [ReponseQuestionController::class, 'destroy']);

            // Documents (liés à une inscription)
            Route::get('/inscriptions/{id_inscription}/documents', [DocumentController::class, 'indexByInscription']);
            Route::post('/inscriptions/{id_inscription}/documents', [DocumentController::class, 'storeForInscription']);
            Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
            Route::delete('/documents/{id}', [DocumentController::class, 'destroyParticipant']);
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

            // Routes pour la gestion des inscriptions (CRUD)
            Route::get('/inscriptions', [InscriptionController::class, 'indexAdmin']);
            Route::get('/inscriptions/{id}', [InscriptionController::class, 'show']);
            Route::put('/inscriptions/{id}', [InscriptionController::class, 'updateAdmin']);
            Route::delete('/inscriptions/{id}', [InscriptionController::class, 'destroyAdmin']);

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

            Route::get('/categories', [CategorieController::class, 'indexAdmin']);
            Route::get('/categories/{id}', [CategorieController::class, 'show']);
            Route::post('/categories', [CategorieController::class, 'store']);
            Route::put('/categories/{id}', [CategorieController::class, 'update']);
            Route::delete('/categories/{id}', [CategorieController::class, 'destroy']);


            Route::get('/sous-categories', [SousCategorieController::class, 'indexAdmin']);
            Route::get('/sous-categories/{id}', [SousCategorieController::class, 'show']);
            Route::post('/sous-categories', [SousCategorieController::class, 'store']);
            Route::put('/sous-categories/{id}', [SousCategorieController::class, 'update']);
            Route::delete('/sous-categories/{id}', [SousCategorieController::class, 'destroy']);

            Route::get('/optionCourse', [OptionPourCourseController::class, 'indexAdmin']);
            Route::get('/optionCourse/{id_course}/{id_option}', [OptionPourCourseController::class, 'show']);
            Route::post('/optionCourse', [OptionPourCourseController::class, 'store']);
            Route::put('/optionCourse/{id_course}/{id_option}', [OptionPourCourseController::class, 'update']);
            Route::delete('/optionCourse/{id_course}/{id_option}', [OptionPourCourseController::class, 'destroy']);
            Route::delete('/optionCourse/{id_course}', [OptionPourCourseController::class, 'destroyByCourse']);
            
            Route::get('/inscriptions/{id_inscription}/choix-options', [ChoixOptionController::class, 'indexParInscription']);
            Route::get('/options/{id_option}/choix', [ChoixOptionController::class, 'indexParOption']);
            Route::post('/choix-options', [ChoixOptionController::class, 'storeAdmin']);
            Route::put('/choix-options/{id_inscription}/{id_option}', [ChoixOptionController::class, 'updateAdmin']);  // ← manquante
            Route::delete('/choix-options/{id_inscription}/{id_option}', [ChoixOptionController::class, 'destroy']);
            
            // Questions (CRUD)
            Route::get('/questions', [QuestionController::class, 'indexAdmin']);
            Route::get('/questions/{id}', [QuestionController::class, 'show']);
            Route::post('/questions', [QuestionController::class, 'store']);
            Route::put('/questions/{id}', [QuestionController::class, 'update']);
            Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

            // Options de réponse QCM (CRUD)
            Route::get('/questions/{id_question}/choix', [OptionQuestionController::class, 'index']);
            Route::get('/choix/{id}', [OptionQuestionController::class, 'show']);
            Route::post('/questions/{id_question}/choix', [OptionQuestionController::class, 'store']);
            Route::put('/choix/{id}', [OptionQuestionController::class, 'update']);
            Route::delete('/choix/{id}', [OptionQuestionController::class, 'destroy']);

            // Liaison course/question + ordre
            Route::get('/courses/{id_course}/questions', [CourseQuestionController::class, 'index']);
            Route::put('/courses/{id_course}/questions/ordre', [CourseQuestionController::class, 'reordonner']);

            // Réponses (lecture admin)
            Route::get('/questions/{id_question}/reponses', [ReponseQuestionController::class, 'indexParQuestion']);

            // Documents (admin)
            Route::get('/inscriptions/{id_inscription}/documents', [DocumentController::class, 'indexByInscription']);
            Route::delete('/documents/{id}', [DocumentController::class, 'destroyAdmin']);
        });
    });