<?php

namespace App\Providers;

use App\Repositories\EloquentTransactionRepository;
use App\Repositories\TransactionRepositoryInterface;
use App\Services\Transfer\TransferService;
use App\Services\Transfer\TransferServiceInterface;
use Illuminate\Support\ServiceProvider;

class TransactionsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding para o repositório de transações
        $this->app->bind(
            TransactionRepositoryInterface::class,
            EloquentTransactionRepository::class
        );
        $this->app->bind(
            TransferServiceInterface::class,
            TransferService::class
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
