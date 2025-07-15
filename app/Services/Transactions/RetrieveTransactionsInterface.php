<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface RetrieveTransactionsInterface
{
    /**
     * Retrieves a collection of transactions for the authenticated user.
     *
     * @param int $take The number of transactions to retrieve (0 for all).
     * @return LengthAwarePaginator<Transaction> // Corrected type hint to Collection of Transaction models
     */
    public function retrieve(int $take = 0): LengthAwarePaginator;
}
