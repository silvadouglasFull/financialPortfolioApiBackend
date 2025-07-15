<?php


use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Rotas de recursos para usuários (CRUD)
    Route::resource('users', UserController::class)->names('admin.users');

    // Nova rota para o perfil do usuário logado
    // É comum usar 'me' ou 'profile' para o usuário autenticado
    Route::get('/user/profile', [UserController::class, 'profile'])->name('api.user.profile');
});
