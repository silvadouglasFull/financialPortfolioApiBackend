<?php

use App\Http\Controllers\Web\ReversalController;
use Illuminate\Support\Facades\Route;

// ... outras rotas web

Route::middleware('auth', 'admin')->group(function () {
    Route::get('/admin/reversals/create', [ReversalController::class, 'showReversalForm'])->name('admin.reversals.create');
    Route::post('/admin/reversals', [ReversalController::class, 'reverse'])->name('admin.reversals.store');
});
