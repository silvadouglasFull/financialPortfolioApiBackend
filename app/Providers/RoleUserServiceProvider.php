<?php

namespace App\Providers;

use App\Services\RoleUser\CheckRoleAdminService;
use App\Services\RoleUser\CheckRoleInterface;
use App\Services\RoleUser\CheckRoleManagerService;
use Illuminate\Support\ServiceProvider;

class RoleUserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CheckRoleInterface::class, function ($app) {
            return new CheckRoleAdminService();
        });
        $this->app->bind(CheckRoleInterface::class, function ($app) {
            return new CheckRoleManagerService();
        });
    }
}
