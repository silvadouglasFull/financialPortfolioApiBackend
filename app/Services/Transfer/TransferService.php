<?php

namespace App\Services\Transfer;

use App\Models\User;
use App\Models\Transaction;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Enums\TransactionStatus;
use App\Enums\UserTypeEnum; // Importar UserTypeEnum
use App\Enums\TransactionType; // Importar TransactionType
use App\Events\MoneyTransferred; // Evento de sucesso
use App\Events\TransactionDenied; // Novo evento para transação negada
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class TransferService
 *
 * Implementa a lógica de negócio para a transferência de dinheiro entre usuários.
 */
class TransferService implements TransferServiceInterface
{
    protected UserRepositoryInterface $userRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * Construtor do TransferService.
     *
     * @param UserRepositoryInterface $userRepository O repositório de usuários.
     * @param TransactionRepositoryInterface $transactionRepository O repositório de transações.
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Realiza uma transferência de dinheiro entre um pagador e um recebedor.
     *
     * @param User $payer O usuário pagador.
     * @param User $payee O usuário recebedor.
     * @param float $amount O valor a ser transferido.
     * @return Transaction A transação criada, seja COMPLETED ou DENIED.
     * @throws Exception Se ocorrer um erro inesperado durante o processo.
     */
    public function performTransfer(User $payer, User $payee, float $amount): Transaction
    {
        // 1. Validar tipo de usuário do pagador (Lojista não pode transferir dinheiro)
        if ($payer->user_type === UserTypeEnum::MERCHANT) {
            $reason = "Usuário do tipo Lojista não autorizado a realizar transferências.";
            Log::warning('Tentativa de transferência por usuário Lojista.', [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'reason' => $reason
            ]);

            // Registrar a transação como DENIED
            $deniedTransaction = $this->transactionRepository->create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'status' => TransactionStatus::DENIED,
                'type' => TransactionType::TRANSFER,
                'reason' => $reason,
            ]);

            // Disparar evento de transação negada
            TransactionDenied::dispatch($deniedTransaction, $payer, $payee, $amount, $reason);

            return $deniedTransaction; // Retornar a transação negada
        }

        // 2. Validar saldo do pagador
        if ($payer->balance < $amount) {
            $reason = "Saldo insuficiente para realizar a transferência.";
            Log::warning('Tentativa de transferência com saldo insuficiente.', [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'current_balance' => $payer->balance,
                'reason' => $reason
            ]);

            // Registrar a transação como DENIED
            $deniedTransaction = $this->transactionRepository->create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'status' => TransactionStatus::DENIED,
                'type' => TransactionType::TRANSFER,
                'reason' => $reason,
            ]);

            // Disparar evento de transação negada
            TransactionDenied::dispatch($deniedTransaction, $payer, $payee, $amount, $reason);

            return $deniedTransaction; // Retornar a transação negada
        }

        // Se passou pelas validações, iniciar a transação de banco de dados
        DB::beginTransaction();

        try {
            // 3. Debitar do pagador
            $this->userRepository->updateBalance($payer, -$amount); // Debita o valor

            // 4. Creditar ao recebedor
            $this->userRepository->updateBalance($payee, $amount); // Credita o valor

            // 5. Criar registro da transação
            $transaction = $this->transactionRepository->create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED,
                'type' => TransactionType::TRANSFER,
                'reason' => null, // Sucesso não tem motivo de negação
            ]);

            DB::commit(); // Confirmar as alterações no banco de dados

            // 6. Disparar evento de dinheiro transferido
            MoneyTransferred::dispatch($transaction, $payer, $payee, $amount);

            return $transaction; // Retornar a transação bem-sucedida

        } catch (Exception $e) {
            DB::rollBack(); // Reverter todas as alterações em caso de erro
            Log::error('Erro inesperado na transferência de dinheiro: ' . $e->getMessage(), [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Para erros inesperados (não de regras de negócio), ainda lançamos a exceção.
            // Se você quisesse registrar *todos* os erros como FAILED, teria que criar
            // uma transação FAILED aqui e retorná-la, mas a instrução foi sobre DENIED.
            throw new Exception('Não foi possível concluir a transferência devido a um erro interno.');
        }
    }
}
