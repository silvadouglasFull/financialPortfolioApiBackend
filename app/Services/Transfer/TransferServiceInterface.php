<?php

namespace App\Services\Transfer;

use App\Models\Transaction;
use App\Models\User;

/**
 * Interface TransferServiceInterface
 *
 * Define o contrato para os serviços de transferência de dinheiro.
 */
interface TransferServiceInterface
{
    /**
     * Realiza uma transferência de dinheiro entre dois usuários.
     *
     * @param User $payer O usuário que está enviando o dinheiro.
     * @param User $payee O usuário que está recebendo o dinheiro.
     * @param float $amount O valor a ser transferido.
     * @return Transaction A transação criada e processada.
     * @throws \Exception Se a transferência não puder ser concluída (ex: saldo insuficiente).
     */
    public function performTransfer(User $payer, User $payee, float $amount): Transaction;
}
