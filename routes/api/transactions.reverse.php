<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReversalController;

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
// Rotas protegidas por autenticação Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions/reverse', [ReversalController::class, 'reverse']);
});
