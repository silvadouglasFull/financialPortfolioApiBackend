<?php

namespace App\Services\Transfer;

use App\Models\User;
use App\Models\Transaction;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Enums\TransactionStatus;
use App\Events\MoneyTransferred; // Importar o Evento (será criado na próxima etapa)
use Illuminate\Support\Facades\DB; // Para transações de banco de dados
use Illuminate\Support\Facades\Log; // Para logging de erros
use Exception; // Para capturar exceções genéricas

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
     * Realiza uma transferência de dinheiro entre dois usuários.
     *
     * @param User $payer O usuário que está enviando o dinheiro.
     * @param User $payee O usuário que está recebendo o dinheiro.
     * @param float $amount O valor a ser transferido.
     * @return Transaction A transação criada e processada.
     * @throws Exception Se a transferência não puder ser concluída (ex: saldo insuficiente).
     */
    public function performTransfer(User $payer, User $payee, float $amount): Transaction
    {
        // 1. Validação de saldo (além do Form Request, crucial aqui no serviço)
        if ($payer->balance < $amount) {
            throw new Exception('Saldo insuficiente para realizar a transferência.');
        }

        // 2. Iniciar uma transação de banco de dados para garantir atomicidade
        DB::beginTransaction();

        try {
            // 3. Registrar a transação inicialmente como PENDING
            $transaction = $this->transactionRepository->create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'status' => TransactionStatus::PENDING,
            ]);

            // 4. Atualizar saldos dos usuários
            // Debitar do pagador
            $this->userRepository->updateBalance($payer, -$amount);
            // Creditar no recebedor
            $this->userRepository->updateBalance($payee, $amount);

            // 5. Atualizar o status da transação para COMPLETED
            $this->transactionRepository->updateStatus($transaction, TransactionStatus::COMPLETED);

            DB::commit(); // Confirmar as alterações no banco de dados

            // 6. Disparar o evento MoneyTransferred
            MoneyTransferred::dispatch($transaction, $payer, $payee, $amount);

            return $transaction; // Retornar a transação concluída

        } catch (Exception $e) {
            DB::rollBack(); // Reverter todas as alterações em caso de erro
            Log::error('Erro na transferência de dinheiro: ' . $e->getMessage(), [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'exception' => $e
            ]);

            // Atualizar status da transação para FAILED se ela já foi criada
            if (isset($transaction) && $transaction->id) {
                // Tenta atualizar o status para FAILED (fora da transação principal,
                // em uma nova conexão se a falha anterior for irreversível, ou em outro mecanismo)
                // Para simplicidade, vamos considerar que o rollback já tratou o estado.
                // Em sistemas críticos, você pode querer um Job separado para lidar com falhas de transação
                // e tentativas de atualização de status, para garantir que o status FAILED seja gravado.
            }

            throw new Exception('Não foi possível concluir a transferência: ' . $e->getMessage());
        }
    }
}
