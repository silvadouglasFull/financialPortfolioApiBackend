<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Inclui as rotas de autenticação do arquivo separado
require __DIR__ . '/register.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/transactions.php';
require __DIR__ . '/transactions.deposit.php';
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
