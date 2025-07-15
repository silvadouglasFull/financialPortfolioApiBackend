<?php

namespace App\Services\Transactions;

use App\Enums\TransactionReversalStatus;
use App\Enums\TransactionStatus;
use App\Enums\UserTypeEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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
        $transactions = $user->user_type !== UserTypeEnum::ADMIN ? Transaction::where('payer_id', $user->id)
            ->latest() : Transaction::latest();

        if ($take !== 0) {
            return $transactions->take($take)->paginate(10);
        }
        return $transactions->paginate(10);
    }
    /**
     * Retrieves a paginated collection of transactions that are eligible for reversal.
     * These are typically transactions with a 'COMPLETED' status that are not
     * themselves 'REVERSAL' type transactions.
     *
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getRevertableTransactions(int $perPage = 10): LengthAwarePaginator
    {
        // Aqui buscamos transações que:
        // 1. Estão com o status COMPLETED.
        // 2. Não são do tipo REVERSAL (para evitar reverter uma reversão).
        $query = Transaction::query()
            ->where('status', TransactionStatus::COMPLETED->value) // Usar .value para o valor string do Enum
            ->where('type', '!=', TransactionReversalStatus::REVERSED->value) // Usar .value para o valor string do Enum
            ->latest(); // Ordenar pelas mais recentes

        // O AdminLTE já usa o Paginator do Laravel, então podemos paginar diretamente.
        return $query->paginate($perPage);
    }
    /**
     * Retrieves a single transaction that is eligible for reversal by its ID.
     * (e.g., COMPLETED status and not of REVERSAL type).
     *
     * @param string $id The ID of the transaction to retrieve.
     * @return Transaction|null
     */
    public function getOriginalTransaction(string $id): ?Transaction
    {
        // You might want to add additional checks here
        // to ensure the transaction is actually eligible for reversal,
        // as you did in your controller and the list method.
        return Transaction::where("id", "=", $id)->first();
    }
}
