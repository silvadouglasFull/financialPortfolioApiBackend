<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Inclui as rotas de autenticação do arquivo separado
require __DIR__ . '/auth.php'; // Ou 'register.php' se for o nome que você escolheu

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
