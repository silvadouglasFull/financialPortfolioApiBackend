<?php

namespace App\Repositories\Reversal;

use App\Models\TransactionReversal;

interface TransactionReversalRepositoryInterface
{
    /**
     * Cria um novo registro de reversão de transação.
     *
     * @param array $data
     * @return TransactionReversal
     */
    public function create(array $data): TransactionReversal;

    /**
     * Encontra um registro de reversão de transação pelo ID.
     *
     * @param string $id
     * @return TransactionReversal|null
     */
    public function findById(string $id): ?TransactionReversal;

    /**
     * Atualiza um registro de reversão de transação.
     *
     * @param TransactionReversal $reversal
     * @param array $data
     * @return bool
     */
    public function update(TransactionReversal $reversal, array $data): bool;

    /**
     * Encontra um registro de reversão de transação pela ID da transação original.
     *
     * @param string $originalTransactionId
     * @return TransactionReversal|null
     */
    public function findByOriginalTransactionId(string $originalTransactionId): ?TransactionReversal;
}
