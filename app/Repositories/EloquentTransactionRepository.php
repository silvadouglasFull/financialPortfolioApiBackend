<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Enums\TransactionStatus;

/**
 * Class EloquentTransactionRepository
 *
 * Implementação do repositório de transações utilizando o Eloquent ORM.
 */
class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Cria uma nova transação.
     *
     * @param array $data Os dados da transação.
     * @return Transaction
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Encontra uma transação pelo seu ID.
     *
     * @param string $id
     * @return Transaction|null
     */
    public function findById(string $id): ?Transaction
    {
        return Transaction::find($id);
    }

    /**
     * Atualiza o status de uma transação.
     *
     * @param Transaction $transaction A instância da transação.
     * @param TransactionStatus $status O novo status da transação.
     * @return bool
     */
    public function updateStatus(Transaction $transaction, TransactionStatus $status): bool
    {
        $transaction->status = $status;
        return $transaction->save();
    }
}
