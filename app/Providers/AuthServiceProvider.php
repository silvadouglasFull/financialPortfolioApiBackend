<?php

namespace App\Providers;

use App\Repositories\EloquentUserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\RegisterValidationServiceInterface;
use App\Services\Auth\RegisterValidationService;
use App\Services\User\UserService;
use App\Services\User\UserServiceInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Importe o facade Gate
use App\Models\User; // Importe o modelo User
use App\Enums\UserTypeEnum; // Importe o Enum UserTypeEnum

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
        // Defina o Gate 'admin-access'
        Gate::define('admin-access', function (User $user) {
            return $user->user_type === UserTypeEnum::ADMIN;
        });

        // Defina o Gate 'commun-access'
        Gate::define('commun-access', function (User $user) {
            return $user->user_type === UserTypeEnum::COMMON;
        });
    }
}
