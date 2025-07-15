<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
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
    /**
     * Retrieves a paginated collection of transactions that are eligible for reversal.
     * (e.g., COMPLETED status and not of REVERSAL type).
     *
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getRevertableTransactions(int $perPage = 10): LengthAwarePaginator;
    /**
     * Retrieves a single transaction that is eligible for reversal by its ID.
     * (e.g., COMPLETED status and not of REVERSAL type).
     *
     * @param string $id The ID of the transaction to retrieve.
     * @return Transaction|null
     */
    public function getOriginalTransaction(string $id): ?Transaction;
}
