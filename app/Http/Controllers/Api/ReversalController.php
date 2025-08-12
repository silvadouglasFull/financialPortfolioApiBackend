<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReversalRequest; // Importar o Form Request
use App\Services\Reversal\ReversalServiceInterface; // Importar a Interface do Serviço
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA; // Importe a classe de anotações

#[OA\Tag(
    name: "Transactions",
    description: "API Endpoints for managing financial transactions"
)]
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

    #[OA\Post(
        path: "/api/transactions/reverse",
        summary: "Reverse a completed transaction",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/ReversalRequest"
            )
        ),
        tags: ["Transactions"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction reversed successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Reversal successful!"),
                        new OA\Property(property: "reversal_transaction", ref: "#/components/schemas/TransactionResponse") // Assumindo um schema para Transaction
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad request, e.g., invalid transaction ID, already reversed, or business rule violation.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Falha ao realizar a reversão."),
                        new OA\Property(property: "error", type: "string", example: "Transaction not found or not eligible for reversal.")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - user does not have permission to reverse this transaction.",
                content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedReversalError")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error - invalid input data.",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Ocorreu um erro interno."),
                        new OA\Property(property: "error", type: "string", example: "Detalhes técnicos do erro.")
                    ]
                )
            )
        ]
    )]
    public function reverse(ReversalRequest $request): JsonResponse
    {
        $user = $request->user();
        $originalTransactionId = $request->original_transaction_id;
        $reason = $request->reason;

        Log::info("Requisição de reversão recebida para transaction_id: {$originalTransactionId} por user_id: {$user->id}. Motivo: {$reason}");

        try {
            $reversalTransaction = $this->reversalService->performReversal(
                $originalTransactionId,
                $user,
                $reason
            );

            return response()->json([
                'message' => 'Reversal successful!',
                'reversal_transaction' => $reversalTransaction,
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

            // Check for specific exceptions to return more precise status codes
            if ($e instanceof \App\Exceptions\UnauthorizedReversalException) { // Example for your custom exception
                $statusCode = Response::HTTP_FORBIDDEN;
                $message = $e->getMessage(); // Use the message from your custom exception
            } elseif ($e->getCode() === Response::HTTP_FORBIDDEN) { // Fallback if exception doesn't extend from specific class
                $statusCode = Response::HTTP_FORBIDDEN;
                $message = $e->getMessage();
            }
            return response()->json([
                'message' => $message,
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
