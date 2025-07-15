<?php

namespace App\Services\Deposit;

use App\Models\User;
use App\Models\Transaction;

/**
 * Interface DepositServiceInterface
 *
 * Define o contrato para os serviços de depósito de dinheiro.
 */
interface DepositServiceInterface
{
    /**
     * Realiza um depósito de dinheiro para um usuário.
     *
     * @param User $user O usuário para quem o depósito será feito.
     * @param float $amount O valor a ser depositado.
     * @return Transaction A transação de depósito criada e processada.
     * @throws \Exception Se o depósito não puder ser concluído.
     */
    public function performDeposit(User $user, float $amount): Transaction;
}
