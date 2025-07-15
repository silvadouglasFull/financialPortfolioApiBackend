<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class RetrieveTransactions implements RetrieveTransactionsInterface
{
    /**
     * Retrieves a collection of transactions for the authenticated user.
     *
     * @param int $take The number of transactions to retrieve (0 for all).
     * @return LengthAwarePaginator<Transaction> // Corrected type hint to Collection of Transaction models
     */
    public function retrieve(int $take = 0): LengthAwarePaginator
    {
        $user = Auth::user();
        $transactions = Transaction::where('payer_id', $user->id)
            ->orWhere('payee_id', $user->id)
            ->latest();

        if ($take !== 0) {
            return $transactions->take($take)->paginate(10);
        }
        return $transactions->paginate(10);
    }
}
