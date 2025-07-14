<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Enums\TransactionStatus;

/**
 * Interface TransactionRepositoryInterface
 *
 * Define o contrato para o repositório de transações.
 */
interface TransactionRepositoryInterface
{
    /**
     * Cria uma nova transação.
     *
     * @param array $data Os dados da transação.
     * @return Transaction
     */
    public function create(array $data): Transaction;

    /**
     * Encontra uma transação pelo seu ID.
     *
     * @param string $id
     * @return Transaction|null
     */
    public function findById(string $id): ?Transaction;

    /**
     * Atualiza o status de uma transação.
     *
     * @param Transaction $transaction A instância da transação.
     * @param TransactionStatus $status O novo status da transação.
     * @return bool
     */
    public function updateStatus(Transaction $transaction, TransactionStatus $status): bool;
}
