<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller; // Base Controller
use App\Http\Requests\Auth\RegisterRequest; // O Request que já criamos
use App\Services\User\UserServiceInterface; // A interface do UserService
use Illuminate\Http\JsonResponse; // Para retornar respostas JSON
use Illuminate\Http\Response; // Para códigos de status HTTP
use Illuminate\Support\Facades\Log; // Para logar erros
use Throwable; // Para capturar exceções genéricas
use OpenApi\Attributes as OA; // Importe a classe de anotações

/**
 * Class RegisterController
 *
 * Responsável por gerenciar o processo de registro de novos usuários.
 * Orquestra a validação dos dados de entrada e a criação do usuário através do UserService.
 */
#[OA\Tag(
    name: "Authentication",
    description: "API Endpoints para autenticação de usuários"
)]
class RegisterController extends Controller
{
    /**
     * @var UserServiceInterface
     */
    protected UserServiceInterface $userService;

    /**
     * Construtor do RegisterController.
     * Injeta o UserService para lidar com a lógica de negócio de usuário.
     *
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    #[OA\Post(
        path: "/register",
        summary: "Register a new user",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/RegisterRequest" // Referencia o schema do RegisterRequest
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Usuário registrado com sucesso!"),
                        new OA\Property(property: "user", ref: "#/components/schemas/UserResponse") // Reutiliza o schema de UserResponse
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationError"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Ocorreu um erro ao tentar registrar o usuário. Por favor, tente novamente mais tarde."),
                        new OA\Property(property: "error", type: "string", example: "Detalhes técnicos do erro em ambiente de desenvolvimento.")
                    ]
                )
            )
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $user = $this->userService->createUser($validatedData);

            return response()->json([
                'message' => 'Usuário registrado com sucesso!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'document' => $user->document,
                    'user_type' => $user->user_type->value,
                    'balance' => $user->balance,
                ],
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error("Erro no registro de usuário: " . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);

            return response()->json([
                'message' => 'Ocorreu um erro ao tentar registrar o usuário. Por favor, tente novamente mais tarde.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
