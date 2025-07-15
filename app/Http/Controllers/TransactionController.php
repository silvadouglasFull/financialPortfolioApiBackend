<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest; // Importar o Form Request
use App\Services\Transfer\TransferServiceInterface; // Importar a interface do serviço de transferência
use App\Repositories\UserRepositoryInterface; // Importar o repositório de usuário para buscar o recebedor
use App\Services\Deposit\DepositServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response; // Para constantes de status HTTP
use Illuminate\Support\Facades\Log; // Para logs de erro
use Exception; // Para capturar exceções gerais

/**
 * Class TransactionController
 *
 * Controlador responsável por lidar com as operações de transação, especificamente transferências.
 */
class TransactionController extends Controller
{
    protected TransferServiceInterface $transferService;
    protected UserRepositoryInterface $userRepository; // Para buscar o payee
    protected DepositServiceInterface $depositService; // o DepositServiceInterface

    /**
     * Construtor do TransactionController.
     *
     * @param TransferServiceInterface $transferService O serviço de transferência injetado.
     * @param UserRepositoryInterface $userRepository O repositório de usuários injetado.
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
     * @param TransferRequest $request A requisição de transferência validada.
     * @return JsonResponse
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        // O usuário pagador é o usuário autenticado, já validado pelo TransferRequest->authorize()
        $payer = $request->user();

        // O recebedor (payee) é buscado pelo ID fornecido no request,
        // que já foi validado pelo TransferRequest (uuid, exists, not_in).
        $payee = $this->userRepository->findById($request->payee_id);

        // Se por algum motivo o payee não for encontrado (apesar da validação),
        // embora improvável com 'exists' rule, é bom ter uma verificação.
        if (!$payee) {
            Log::error('Recebedor não encontrado após validação de TransferRequest.', ['payee_id' => $request->payee_id]);
            return response()->json([
                'message' => 'Erro interno. Recebedor não encontrado.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            // Chama o serviço para executar a transferência
            $transaction = $this->transferService->performTransfer(
                $payer,
                $payee,
                $request->amount
            );

            // Retorna sucesso com os detalhes da transação
            return response()->json([
                'message' => 'Transferência realizada com sucesso!',
                'transaction' => $transaction->load(['payer', 'payee']), // Carrega os relacionamentos para retorno
                // Você pode querer retornar apenas alguns campos da transação por segurança
                // 'transaction' => $transaction->only(['id', 'payer_id', 'payee_id', 'amount', 'status', 'created_at']),
            ], Response::HTTP_OK); // 200 OK

        } catch (Exception $e) {
            // Captura qualquer exceção lançada pelo TransferService
            Log::error('Falha na transferência de dinheiro: ' . $e->getMessage(), [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $request->amount,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);

            $statusCode = Response::HTTP_BAD_REQUEST; // 400 Bad Request para erros de negócio (ex: saldo insuficiente)
            if ($e->getMessage() === 'Saldo insuficiente para realizar a transferência.') {
                $errorMessage = $e->getMessage();
            } else {
                // Para outros erros não esperados, retornar 500 e uma mensagem genérica
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $errorMessage = 'Ocorreu um erro inesperado ao processar a transferência. Por favor, tente novamente mais tarde.';
            }

            return response()->json([
                'message' => 'Erro ao realizar a transferência.',
                'error' => $errorMessage,
            ], $statusCode);
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
        $user = $request->user(); // Usuário autenticado é quem está depositando
        $amount = $request->amount;
        try {
            $transaction = $this->depositService->performDeposit($user, $amount);

            return response()->json([
                'message' => 'Depósito realizado com sucesso!',
                'transaction' => $transaction, // Retorna os detalhes da transação de depósito
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
