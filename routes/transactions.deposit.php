<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController; // Certifique-se de que esta linha está presente

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

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions/deposit', [TransactionController::class, 'deposit']);
});
