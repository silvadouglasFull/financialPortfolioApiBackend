<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TransferRequest;
use App\Http\Requests\DepositRequest;
use App\Services\Transfer\TransferServiceInterface;
use App\Services\Deposit\DepositServiceInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Enums\TransactionStatus; // Importar TransactionStatus
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    protected TransferServiceInterface $transferService;
    protected DepositServiceInterface $depositService;
    protected UserRepositoryInterface $userRepository;

    /**
     * Construtor do TransactionController.
     *
     * @param TransferServiceInterface $transferService
     * @param DepositServiceInterface $depositService
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        TransferServiceInterface $transferService,
        DepositServiceInterface $depositService,
        UserRepositoryInterface $userRepository
    ) {
        $this->transferService = $transferService;
        $this->depositService = $depositService;
        $this->userRepository = $userRepository;
    }

    /**
     * Realiza uma transferência de dinheiro.
     *
     * @param TransferRequest $request
     * @return JsonResponse
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $payer = $request->user();
        $payee = $this->userRepository->findById($request->payee_id);

        if (!$payee) {
            // Este caso deve ser raro se o TransferRequest já valida payee_id,
            // mas é um fallback para garantir que o recebedor existe.
            Log::error('Recebedor não encontrado após validação de TransferRequest.', ['payee_id' => $request->payee_id]);
            return response()->json([
                'message' => 'Erro interno. Recebedor não encontrado.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $transaction = $this->transferService->performTransfer(
                $payer,
                $payee,
                $request->amount
            );

            // Verificar o status da transação retornada pelo serviço
            if ($transaction->status === TransactionStatus::COMPLETED) {
                return response()->json([
                    'message' => 'Transferência realizada com sucesso!',
                    'transaction' => $transaction->load(['payer', 'payee']),
                ], Response::HTTP_OK);
            } elseif ($transaction->status === TransactionStatus::DENIED) {
                // Se a transação foi negada pelo serviço e registrada como DENIED
                return response()->json([
                    'message' => 'Transferência negada.',
                    'reason' => $transaction->reason, // O motivo da negação
                    'transaction' => $transaction->load(['payer', 'payee']), // Retornar a transação negada
                ], Response::HTTP_OK); // Ainda retorna 200 OK, pois a operação de registro foi um sucesso
            } else {
                // Caso um status inesperado seja retornado
                Log::error('Status de transação inesperado retornado pelo TransferService.', [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status->value,
                    'payer_id' => $payer->id,
                    'payee_id' => $payee->id,
                    'amount' => $request->amount,
                ]);
                return response()->json([
                    'message' => 'Erro interno. Status de transação inesperado.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            // Este catch agora deve ser apenas para erros *inesperados* do sistema,
            // não para regras de negócio (saldo insuficiente, lojista) que já são tratadas no serviço.
            Log::error('Falha inesperada na transferência de dinheiro: ' . $e->getMessage(), [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $request->amount,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Erro ao realizar a transferência.',
                'error' => 'Ocorreu um erro inesperado ao processar a transferência. Por favor, tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Realiza um depósito de dinheiro para o usuário autenticado.
     *
     * @param DepositRequest $request
     * @return JsonResponse
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        $user = $request->user();
        $amount = $request->amount;

        try {
            $transaction = $this->depositService->performDeposit($user, $amount);

            return response()->json([
                'message' => 'Depósito realizado com sucesso!',
                'transaction' => $transaction,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Falha no depósito de dinheiro: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Erro ao realizar o depósito.',
                'error' => 'Ocorreu um erro inesperado ao processar o depósito. Por favor, tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
