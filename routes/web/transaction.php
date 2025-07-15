<?php

use App\Http\Controllers\Web\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Rotas de Transferência
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/transfer', [TransactionController::class, 'showTransferForm'])->name('transactions.transfer.create');
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer'])->name('transactions.transfer.store');

    // Rotas de Depósito
    Route::get('/transactions/deposit', [TransactionController::class, 'showDepositForm'])->name('transactions.deposit.create');
    Route::post('/transactions/deposit', [TransactionController::class, 'deposit'])->name('transactions.deposit.store');
});
