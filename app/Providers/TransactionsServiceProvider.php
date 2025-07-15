<?php

namespace App\Providers;

use App\Repositories\EloquentTransactionRepository;
use App\Repositories\Reversal\TransactionReversalRepository;
use App\Repositories\Reversal\TransactionReversalRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Services\Reversal\ReversalService;
use App\Services\Reversal\ReversalServiceInterface;
use App\Services\Transactions\RetrieveTransactions;
use App\Services\Transactions\RetrieveTransactionsInterface;
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
        $this->app->bind(
            TransactionReversalRepositoryInterface::class,
            TransactionReversalRepository::class
        );
        $this->app->bind(
            ReversalServiceInterface::class,
            ReversalService::class
        );
        $this->app->bind(
            RetrieveTransactionsInterface::class,
            RetrieveTransactions::class
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
