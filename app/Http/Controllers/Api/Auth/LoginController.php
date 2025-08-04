<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // Importar o LoginRequest
use App\Services\Auth\AuthServiceInterface; // Importar a interface do serviço de autenticação
use App\Exceptions\AuthenticationException; // Importar a exceção de autenticação
use App\Services\Auth\SetJWTCookie;
use Illuminate\Http\JsonResponse; // Para tipagem do retorno
use Illuminate\Http\Response; // Para constantes de status HTTP
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA; // Adicione esta linha para importar as anotações

/**
 * Class LoginController
 *
 * Controlador responsável por lidar com o login de usuários.
 */
#[OA\Tag(
    name: "Authentication",
    description: "API Endpoints para autenticação de usuários"
)]
#[OA\SecurityScheme(
    securityScheme: "apiToken",
    type: "apiKey",
    in: "header",
    name: "Api-Token",
    description: "API Token de acesso global para serviços internos ou parceiros."
)]
class LoginController extends Controller
{
    protected AuthServiceInterface $authService;

    /**
     * Construtor do LoginController.
     *
     * @param AuthServiceInterface $authService O serviço de autenticação injetado.
     */
    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    #[OA\Post(
        path: "/login",
        summary: "Authenticate user and get API token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret_password")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login, returns authentication token and user data.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Login realizado com sucesso!"),
                        new OA\Property(property: "token", type: "string", example: "YOUR_SANCTUM_TOKEN"),
                        new OA\Property(property: "user", ref: "#/components/schemas/UserResponse")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Authentication failed due to invalid credentials.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error.",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Ocorreu um erro interno ao tentar realizar o login."),
                        new OA\Property(property: "error", type: "string", example: "Por favor, tente novamente mais tarde.")
                    ]
                )
            )
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->attemptLogin($request->only('email', 'password'));
            $cookie = new SetJWTCookie()->setCookie($result);
            return response()->json([
                'message' => 'Login realizado com sucesso!',
                'user' => $result['user'],
            ], Response::HTTP_OK)->cookie($cookie);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'Falha na autenticação.',
                'error' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            Log::error('Erro ao tentar Log in: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Ocorreu um erro interno ao tentar realizar o login.',
                'error' => 'Por favor, tente novamente mais tarde.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
