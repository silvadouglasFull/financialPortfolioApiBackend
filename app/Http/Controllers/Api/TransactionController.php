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
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Transactions",
    description: "API Endpoints for managing financial transactions"
)]
class TransactionController extends Controller
{
    protected TransferServiceInterface $transferService;
    protected DepositServiceInterface $depositService;
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        TransferServiceInterface $transferService,
        DepositServiceInterface $depositService,
        UserRepositoryInterface $userRepository
    ) {
        $this->transferService = $transferService;
        $this->depositService = $depositService;
        $this->userRepository = $userRepository;
    }

    #[OA\Post(
        path: "/api/transactions/transfer",
        summary: "Perform a money transfer between users",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/TransferRequest"
            )
        ),
        tags: ["Transactions"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transfer performed successfully or denied with a reason.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transferência realizada com sucesso!"),
                        new OA\Property(property: "transaction", ref: "#/components/schemas/TransactionResponse"),
                        new OA\Property(property: "reason", type: "string", nullable: true, example: "Insufficient balance", description: "Reason for denial if status is DENIED.")
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
                description: "Forbidden - user type not allowed to transfer.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Usuários do tipo Lojista não podem realizar transferências."),
                        new OA\Property(property: "error", type: "string", example: "Tipo de usuário não permitido.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Payee not found.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Recebedor não encontrado."),
                        new OA\Property(property: "error", type: "string", example: "O ID do usuário recebedor não corresponde a um usuário existente.")
                    ]
                )
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
                        new OA\Property(property: "message", type: "string", example: "Erro ao realizar a transferência."),
                        new OA\Property(property: "error", type: "string", example: "Ocorreu um erro inesperado ao processar a transferência. Por favor, tente novamente mais tarde.")
                    ]
                )
            )
        ]
    )]
    public function transfer(TransferRequest $request): JsonResponse
    {
        $payer = $request->user();
        $payee = $this->userRepository->findById($request->payee_id);

        if (!$payee) {
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

            if ($transaction->status === TransactionStatus::COMPLETED) {
                return response()->json([
                    'message' => 'Transferência realizada com sucesso!',
                    'transaction' => $transaction->load(['payer', 'payee']),
                ], Response::HTTP_OK);
            } elseif ($transaction->status === TransactionStatus::DENIED) {
                return response()->json([
                    'message' => 'Transferência negada.',
                    'reason' => $transaction->reason,
                    'transaction' => $transaction->load(['payer', 'payee']),
                ], Response::HTTP_OK);
            } else {
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

    #[OA\Post(
        path: "/api/transactions/deposit",
        summary: "Perform a money deposit for the authenticated user",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/DepositRequest"
            )
        ),
        tags: ["Transactions"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Deposit performed successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Depósito realizado com sucesso!"),
                        new OA\Property(property: "transaction", ref: "#/components/schemas/TransactionResponse")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
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
                        new OA\Property(property: "message", type: "string", example: "Erro ao realizar o depósito."),
                        new OA\Property(property: "error", type: "string", example: "Ocorreu um erro inesperado ao processar o depósito. Por favor, tente novamente mais tarde.")
                    ]
                )
            )
        ]
    )]
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
