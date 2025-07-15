<?php

namespace App\Services\Reversal;

use App\Models\Transaction; // Para o tipo de retorno
use App\Models\User;        // Para o usuário solicitante

interface ReversalServiceInterface
{
    /**
     * Realiza a reversão de uma transação existente.
     *
     * @param string $originalTransactionId O ID da transação original a ser revertida.
     * @param User $reversedBy O usuário que está solicitando/executando a reversão (pode ser um administrador).
     * @param string $reason O motivo da reversão.
     * @return Transaction A nova transação do tipo REVERSAL criada.
     * @throws \Exception Se a reversão não puder ser processada (ex: transação não encontrada, já revertida, etc.).
     */
    public function performReversal(string $originalTransactionId, User $reversedBy, string $reason): Transaction;
}
