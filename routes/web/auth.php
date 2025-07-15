<?php

use App\Http\Controllers\Web\GoogleAuthController;
use Illuminate\Support\Facades\Route;

// ... outras rotas web

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
