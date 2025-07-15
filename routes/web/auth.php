<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
// Rotas de autenticação

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
// Rota de logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login')->with('success', 'Você foi desconectado.');
})->name('logout');
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
