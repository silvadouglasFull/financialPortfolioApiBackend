<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReversalRequest; // Importar o Form Request
use App\Services\Reversal\ReversalServiceInterface; // Importar a Interface do Serviço
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class ReversalController extends Controller
{
    protected ReversalServiceInterface $reversalService;

    /**
     * Construtor do ReversalController.
     *
     * @param ReversalServiceInterface $reversalService
     */
    public function __construct(ReversalServiceInterface $reversalService)
    {
        $this->reversalService = $reversalService;
    }

    /**
     * Realiza a reversão de uma transação.
     *
     * @param ReversalRequest $request
     * @return JsonResponse
     */
    public function reverse(ReversalRequest $request): JsonResponse
    {
        $user = $request->user(); // O usuário autenticado que está solicitando a reversão
        $originalTransactionId = $request->original_transaction_id;
        $reason = $request->reason;

        Log::info("Requisição de reversão recebida para transaction_id: {$originalTransactionId} por user_id: {$user->id}. Motivo: {$reason}");

        try {
            // Delega a lógica de reversão para o serviço
            $reversalTransaction = $this->reversalService->performReversal(
                $originalTransactionId,
                $user, // Passa o usuário autenticado como quem reverteu
                $reason
            );

            return response()->json([
                'message' => 'Reversão realizada com sucesso!',
                'reversal_transaction' => $reversalTransaction, // A nova transação de REVERSAL
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao processar a reversão da transação: ' . $e->getMessage(), [
                'original_transaction_id' => $originalTransactionId,
                'reversed_by_user_id' => $user->id,
                'reason_input' => $reason,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);
            $statusCode = Response::HTTP_BAD_REQUEST;
            $message = 'Falha ao realizar a reversão.';
            if ($e->getCode() === Response::HTTP_FORBIDDEN) {
                $statusCode = Response::HTTP_FORBIDDEN;
                $message = $e->getMessage();
            }
            // Retorna uma resposta de erro adequada
            return response()->json([
                'message' => $message,
                'error' => $e->getMessage(),
            ], $statusCode); // Usar 400 Bad Request para erros de negócio/validação de serviço
        }
    }
}
