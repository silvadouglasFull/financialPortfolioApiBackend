<?php

namespace App\Providers;

use App\Services\Deposit\DepositService;
use App\Services\Deposit\DepositServiceInterface;
use Illuminate\Support\ServiceProvider;

class DepositServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding para o serviço de depósito
        $this->app->bind(
            DepositServiceInterface::class,
            DepositService::class
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
