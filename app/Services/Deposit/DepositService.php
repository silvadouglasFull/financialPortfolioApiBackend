<?php

namespace App\Services\Deposit;

use App\Models\User;
use App\Models\Transaction;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Enums\TransactionStatus; // Importar o Enum TransactionStatus
use App\Enums\TransactionType;   // Importar o novo Enum TransactionType
use App\Enums\UserTypeEnum;
use App\Events\MoneyDeposited;   // Importar o Evento (será criado na próxima etapa)
use Illuminate\Support\Facades\DB; // Para transações de banco de dados
use Illuminate\Support\Facades\Log; // Para logging de erros
use Exception; // Para capturar exceções genéricas
use Illuminate\Http\Response;

/**
 * Class DepositService
 *
 * Implementa a lógica de negócio para o depósito de dinheiro para usuários.
 */
class DepositService implements DepositServiceInterface
{
    protected UserRepositoryInterface $userRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * Construtor do DepositService.
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
     * Realiza um depósito de dinheiro para um usuário.
     *
     * @param User $user O usuário para quem o depósito será feito.
     * @param float $amount O valor a ser depositado.
     * @return Transaction A transação de depósito criada e processada.
     * @throws Exception Se o depósito não puder ser concluído.
     */
    public function performDeposit(User $user, float $amount): Transaction
    {
        if ($user->user_type === UserTypeEnum::ADMIN) {
            throw new Exception("You do not have permission to use this feature", Response::HTTP_FORBIDDEN);
        }
        // 1. Iniciar uma transação de banco de dados para garantir atomicidade
        DB::beginTransaction();

        try {
            // 2. Atualizar saldo do usuário (creditar)
            $this->userRepository->updateBalance($user, $amount);

            // 3. Registrar a transação de depósito como COMPLETED
            $transaction = $this->transactionRepository->create([
                'payer_id' => $user->id,
                'payee_id' => $user->id, // O usuário logado é o recebedor
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED, // Depósito é concluído imediatamente
                'type' => TransactionType::DEPOSIT, // Definir o tipo da transação
            ]);

            DB::commit(); // Confirmar as alterações no banco de dados

            // 4. Disparar o evento MoneyDeposited
            MoneyDeposited::dispatch($transaction, $user, $amount);

            return $transaction; // Retornar a transação concluída

        } catch (Exception $e) {
            DB::rollBack(); // Reverter todas as alterações em caso de erro
            Log::error('Erro no depósito de dinheiro: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount,
                'exception' => $e
            ]);

            throw new Exception('Não foi possível concluir o depósito: ' . $e->getMessage());
        }
    }
}
