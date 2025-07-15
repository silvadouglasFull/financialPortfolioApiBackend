<?php

use App\Http\Controllers\Web\GoogleAuthController;
use App\Http\Controllers\Web\LoginController;

use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
