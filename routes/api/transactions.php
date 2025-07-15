<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\TransactionController; // Importar o TransactionController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ROTAS DE AUTENTICAÇÃO TRADICIONAL
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');

// ROTAS DE REGISTRO E AUTENTICAÇÃO SOCIAL (fora do middleware auth:sanctum)
Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// ROTAS PROTEGIDAS POR AUTENTICAÇÃO E MIDDLEWARES
Route::middleware('auth:sanctum')->group(function () {
    // Rota para a transferência de dinheiro
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer'])
        ->name('transactions.transfer')
        ->middleware('transaction.throttle:5,1'); // Middleware de anti-abuso (5 requisições por minuto)
});
