<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ===== Routes publiques (sans authentification) =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ===== Routes protégées (nécessite d'être connecté) =====
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
});