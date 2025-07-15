<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Inclui as rotas de autenticação do arquivo separado
require __DIR__ . '/api/register.php';
require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/transactions.php';
require __DIR__ . '/api/transactions.deposit.php';
require __DIR__ . '/api/transactions.reverse.php';
require __DIR__ . '/api/user.php';

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
