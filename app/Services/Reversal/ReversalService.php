<?php

namespace App\Services\Reversal;

use App\Models\User;
use App\Models\Transaction;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\Reversal\TransactionReversalRepositoryInterface;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\TransactionReversalStatus;
use App\Enums\UserTypeEnum; // Importar o UserTypeEnum
use App\Events\TransactionReversed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception; // Usaremos a Exception genérica, ou você pode criar uma específica
use Illuminate\Http\Response;

class ReversalService implements ReversalServiceInterface
{
    protected UserRepositoryInterface $userRepository;
    protected TransactionRepositoryInterface $transactionRepository;
    protected TransactionReversalRepositoryInterface $transactionReversalRepository;

    /**
     * Construtor do ReversalService.
     *
     * @param UserRepositoryInterface $userRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param TransactionReversalRepositoryInterface $transactionReversalRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        TransactionRepositoryInterface $transactionRepository,
        TransactionReversalRepositoryInterface $transactionReversalRepository
    ) {
        $this->userRepository = $userRepository;
        $this->transactionRepository = $transactionRepository;
        $this->transactionReversalRepository = $transactionReversalRepository;
    }

    /**
     * Realiza a reversão de uma transação existente.
     *
     * @param string $originalTransactionId O ID da transação original a ser revertida.
     * @param User $reversedBy O usuário que está solicitando/executando a reversão.
     * @param string $reason O motivo da reversão.
     * @return Transaction A nova transação do tipo REVERSAL criada.
     * @throws Exception Se a reversão não puder ser processada.
     */
    public function performReversal(string $originalTransactionId, User $reversedBy, string $reason): Transaction
    {
        Log::info("Iniciando reversão para a transação: {$originalTransactionId} pelo usuário: {$reversedBy->id}. Motivo: {$reason}");

        if ($reversedBy->user_type !== UserTypeEnum::ADMIN) {
            Log::warning("Tentativa de reversão negada. Usuário #{$reversedBy->id} não é ADMIN. Tipo: {$reversedBy->user_type->value}");

            // Registrar a tentativa de reversão negada
            $this->transactionReversalRepository->create([
                'original_transaction_id' => $originalTransactionId,
                'reversal_transaction_id' => null, // Não há transação de reversão criada
                'reversed_by_user_id' => $reversedBy->id,
                'reason' => $reason . " (Negada: Usuário não ADMIN)",
                'status' => TransactionReversalStatus::DENIED,
            ]);

            throw new Exception("Apenas usuários administradores podem reverter transações.", Response::HTTP_FORBIDDEN);
        }

        $originalTransaction = $this->transactionRepository->findById($originalTransactionId);

        // 1. Validar se a transação original existe
        if (!$originalTransaction) {
            Log::error("Tentativa de reverter transação inexistente: {$originalTransactionId}");
            throw new Exception("Transação original não encontrada.", Response::HTTP_BAD_REQUEST);
        }

        // 2. Validar o status da transação original (não pode ser já revertida, negada ou falha)
        if (
            $originalTransaction->status === TransactionStatus::REVERSED ||
            $originalTransaction->status === TransactionStatus::DENIED ||
            $originalTransaction->status === TransactionStatus::FAILED
        ) {
            Log::warning("Tentativa de reverter transação com status inválido: {$originalTransactionId}, Status: {$originalTransaction->status->value}");
            throw new Exception("Não é possível reverter uma transação com status '{$originalTransaction->status->value}'.");
        }

        // 3. Verificar se a transação já possui um registro de reversão para evitar duplicações
        if ($this->transactionReversalRepository->findByOriginalTransactionId($originalTransactionId)) {
            Log::warning("Tentativa de reverter transação já em processo de reversão ou já revertida: {$originalTransactionId}");
            throw new Exception("Esta transação já possui um registro de reversão pendente ou concluída.", Response::HTTP_BAD_REQUEST);
        }

        // Recuperar pagador e recebedor da transação original
        $payer = $originalTransaction->payer; // Pode ser null para DEPOSIT
        $payee = $originalTransaction->payee;

        // Iniciar transação de banco de dados para garantir atomicidade
        DB::beginTransaction();

        $transactionReversalRecord = null; // Inicializar para garantir que esteja definido para o catch

        try {
            // 4. Registrar a intenção de reversão na tabela transaction_reversals (status PENDING)
            $transactionReversalRecord = $this->transactionReversalRepository->create([
                'original_transaction_id' => $originalTransaction->id,
                'reversal_transaction_id' => null, // Será atualizado após a criação da nova transação de REVERSAL
                'reversed_by_user_id' => $reversedBy->id,
                'reason' => $reason,
                'status' => TransactionReversalStatus::PENDING,
            ]);

            // 5. Reverter os saldos dos usuários
            if ($originalTransaction->type === TransactionType::TRANSFER) {
                // Transferência: Pagador (payer) recupera o valor, Recebedor (payee) perde o valor
                if (!$payer) { // Apenas um fallback, payer deve existir para TRANSFER
                    throw new Exception("Pagador da transação original não encontrado para reversão de transferência.", Response::HTTP_BAD_REQUEST);
                }
                $this->userRepository->updateBalance($payer, $originalTransaction->amount); // Pagador recupera
                $this->userRepository->updateBalance($payee, -$originalTransaction->amount); // Recebedor perde
                Log::info("Saldos revertidos para transferência. Payer: {$payer->id}, Payee: {$payee->id}, Amount: {$originalTransaction->amount}");
            } elseif ($originalTransaction->type === TransactionType::DEPOSIT) {
                // Depósito: Recebedor (payee) perde o valor
                $this->userRepository->updateBalance($payee, -$originalTransaction->amount); // Recebedor perde
                Log::info("Saldos revertidos para depósito. Payee: {$payee->id}, Amount: {$originalTransaction->amount}");
            } else {
                Log::error("Tipo de transação original não suportado para reversão: {$originalTransaction->type->value}");
                throw new Exception("Tipo de transação não suportado para reversão.");
            }

            // 6. Criar uma nova transação do tipo REVERSAL na tabela 'transactions'
            // O valor da reversão é o mesmo da original, mas com o tipo REVERSAL
            // Ajuste para o caso de depósito: Payer da REVERSAL é o Payee da original, Payee da REVERSAL é nulo
            $reversalTransaction = $this->transactionRepository->create([
                'payer_id' => $originalTransaction->type === TransactionType::DEPOSIT ? $originalTransaction->payee_id : $originalTransaction->payee_id,
                'payee_id' => $originalTransaction->type === TransactionType::DEPOSIT ? null : $originalTransaction->payer_id,
                'amount' => $originalTransaction->amount,
                'status' => TransactionStatus::COMPLETED, // A transação de REVERSAL em si foi "completada"
                'type' => TransactionType::REVERSAL,
                'reverted_from' => $originalTransaction->id, // Referência à transação original
                'reason' => $reason,
            ]);
            Log::info("Nova transação de REVERSAL criada: {$reversalTransaction->id}");


            // 7. Atualizar o status da transação original para REVERSED
            $this->transactionRepository->updateStatus($originalTransaction, TransactionStatus::REVERSED, $reason);
            Log::info("Status da transação original atualizado para REVERSED: {$originalTransaction->id}");

            // 8. Atualizar o registro de reversão com o ID da nova transação de REVERSAL e status COMPLETED
            $this->transactionReversalRepository->update($transactionReversalRecord, [
                'reversal_transaction_id' => $reversalTransaction->id,
                'status' => TransactionReversalStatus::COMPLETED,
            ]);
            Log::info("Registro de reversão atualizado para COMPLETED: {$transactionReversalRecord->id}");

            DB::commit(); // Confirmar todas as alterações no banco de dados

            // 9. Disparar o evento TransactionReversed
            TransactionReversed::dispatch(
                $originalTransaction->fresh(), // Carregar a versão mais recente da transação original
                $reversalTransaction,
                $transactionReversalRecord->fresh(), // Carregar a versão mais recente do registro de reversão
                $payer,
                $payee,
                $originalTransaction->amount,
                $reason
            );
            Log::info("Evento TransactionReversed disparado para a transação original: {$originalTransaction->id}");

            return $reversalTransaction; // Retornar a nova transação de REVERSAL

        } catch (Exception $e) {
            DB::rollBack(); // Reverter todas as alterações em caso de erro
            // Atualizar o status do registro de reversão para FAILED se ele foi criado no try
            if ($transactionReversalRecord && $transactionReversalRecord->exists) {
                $this->transactionReversalRepository->update($transactionReversalRecord, [
                    'status' => TransactionReversalStatus::FAILED,
                    'reason' => $reason . " (Falha interna: " . $e->getMessage() . ")" // Adicionar detalhe da falha
                ]);
            }

            Log::error('Erro ao realizar a reversão da transação: ' . $e->getMessage(), [
                'original_transaction_id' => $originalTransactionId,
                'reversed_by_user_id' => $reversedBy->id,
                'reason_input' => $reason,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);
            throw new Exception("Não foi possível processar a reversão: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
