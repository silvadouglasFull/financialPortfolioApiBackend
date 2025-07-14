<?php

namespace App\Providers;

use App\Repositories\EloquentUserRepository;
use App\Repositories\UserRepositoryInterface;
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

        // Binding para o serviço de usuário
        $this->app->bind(
            UserServiceInterface::class,
            UserService::class
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
