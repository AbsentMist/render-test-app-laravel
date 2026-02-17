<?php

//use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

//Route::get('/', [MessageController::class, 'index']); // La route principale
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');