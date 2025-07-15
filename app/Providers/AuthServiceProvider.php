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

        // Opcional: Você também pode definir Gates mais granulares se precisar de controle individual
        // para cada ação de CRUD, mesmo que o menu seja visível apenas para admins.
        // Isso é o que você já fez no UserController, mas aqui são as definições dos Gates.

        // Gate para visualizar a lista de usuários (apenas admins)
        Gate::define('viewAny', function (User $user) {
            return $user->user_type === UserTypeEnum::ADMIN;
        });

        // Gate para criar usuários (apenas admins)
        Gate::define('create', function (User $user) {
            return $user->user_type === UserTypeEnum::ADMIN;
        });

        // Gate para visualizar um usuário específico (admins ou o próprio usuário)
        // Note que o $targetUser é a instância do usuário que está sendo visualizada.
        Gate::define('view', function (User $user, User $targetUser) {
            return $user->user_type === UserTypeEnum::ADMIN || $user->id === $targetUser->id;
        });

        // Gate para atualizar um usuário (admins ou o próprio usuário)
        Gate::define('update', function (User $user, User $targetUser) {
            return $user->user_type === UserTypeEnum::ADMIN || $user->id === $targetUser->id;
        });

        // Gate para deletar um usuário (apenas admins)
        Gate::define('delete', function (User $user, User $targetUser) {
            return $user->user_type === UserTypeEnum::ADMIN;
        });
    }
}
