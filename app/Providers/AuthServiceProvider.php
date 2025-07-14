<?php

namespace App\Providers;

// Removido App\Http\Controllers\Auth\RegisterController daqui,
// pois o ServiceProvider não está vinculando o Controller em si.
// Use App\Http\Controllers\Auth\RegisterController; // <-- Remova esta linha se não estiver usando-a para outra coisa.

use App\Repositories\EloquentUserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\RegisterValidationServiceInterface;
use App\Services\Auth\RegisterValidationService;
use App\Services\User\UserService;
use App\Services\User\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding para o serviço de validação de registro
        $this->app->bind(
            RegisterValidationServiceInterface::class,
            RegisterValidationService::class
        );

        // Binding para o repositório de usuário
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        // Binding CORRETO para o serviço de usuário
        // UMA INTERFACE PARA UMA IMPLEMENTAÇÃO
        $this->app->bind(
            UserServiceInterface::class,
            UserService::class
        );
        // Binding para o serviço de autenticação
        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
