<?php

use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Route;


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
