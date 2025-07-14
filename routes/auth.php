<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController; // Importar o LoginController
// Certifique-se de importar o GoogleAuthController quando criá-lo
// use App\Http\Controllers\Auth\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Agrupamento de rotas de autenticação
// O prefixo 'auth' foi configurado no RouteServiceProvider para este grupo.
// Portanto, as rotas aqui serão '/api/auth/register', '/api/login', etc.
// No entanto, para o POST /login, você especificou /api/login, então
// vamos colocá-lo fora do grupo 'auth' se o prefixo 'api/auth' já estiver lá.

// ROTAS DE AUTENTICAÇÃO TRADICIONAL
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');

// ROTAS DE REGISTRO
Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

    // ROTAS DE AUTENTICAÇÃO SOCIAL (GOOGLE)
    // Elas estarão dentro do prefixo 'auth' conforme o requisito
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});


// Exemplo de rota protegida pelo Sanctum (para testar depois do login)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
