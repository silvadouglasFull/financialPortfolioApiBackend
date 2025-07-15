<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Enums\TransactionStatus;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Cria uma nova transação.
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Encontra uma transação pelo ID.
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
     * @param Transaction $transaction
     * @param TransactionStatus $status
     * @param string|null $reason (Adicione este parâmetro)
     * @return bool
     */
    public function updateStatus(Transaction $transaction, TransactionStatus $status, ?string $reason = null): bool
    {
        $data = ['status' => $status];

        // Se um motivo for fornecido, adicione-o aos dados a serem atualizados
        if ($reason !== null) {
            $data['reason'] = $reason;
        }

        return $transaction->update($data);
    }
}
