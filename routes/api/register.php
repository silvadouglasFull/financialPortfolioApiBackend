<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Este arquivo conterá rotas específicas de autenticação.
// IMPORTANTE: Não adicione 'prefix('auth')' aqui se você for adicionar o prefixo no RouteServiceProvider.
// Se você adicionar o prefixo 'auth' no RouteServiceProvider, a rota abaixo será /api/auth/register
Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

// Você pode adicionar outras rotas de autenticação aqui, como login, logout, etc.
// Route::post('/login', [LoginController::class, 'login'])->name('auth.login');