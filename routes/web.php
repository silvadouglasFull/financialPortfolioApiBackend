<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/web/admin.php';
require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/register.php';
require __DIR__ . '/web/transaction.php';
Auth::routes();
Route::get('/', [HomeController::class, 'index'])->name('home');
