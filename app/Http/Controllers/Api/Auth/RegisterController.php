<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller; // Base Controller
use App\Http\Requests\Auth\RegisterRequest; // O Request que já criamos
use App\Services\User\UserServiceInterface; // A interface do UserService
use Illuminate\Http\JsonResponse; // Para retornar respostas JSON
use Illuminate\Http\Response; // Para códigos de status HTTP
use Illuminate\Support\Facades\Log; // Para logar erros
use Throwable; // Para capturar exceções genéricas

/**
 * Class RegisterController
 *
 * Responsável por gerenciar o processo de registro de novos usuários.
 * Orquestra a validação dos dados de entrada e a criação do usuário através do UserService.
 */
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

    /**
     * Lida com a requisição de registro de um novo usuário.
     *
     * @param RegisterRequest $request A requisição HTTP validada.
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Os dados já foram validados pelo RegisterRequest e RegisterValidationService
            $validatedData = $request->validated();

            // Chama o UserService para criar o usuário com a lógica de negócio
            $user = $this->userService->createUser($validatedData);

            // Retorna uma resposta JSON de sucesso
            return response()->json([
                'message' => 'Usuário registrado com sucesso!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'document' => $user->document,
                    'user_type' => $user->user_type->value, // Acessa o valor string do Enum
                    'balance' => $user->balance,
                ],
            ], Response::HTTP_CREATED); // Código 201 Created

        } catch (Throwable $e) {
            // Captura qualquer exceção não tratada especificamente pelos FormRequests ou Services
            Log::error("Erro no registro de usuário: " . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);

            // Retorna uma resposta de erro genérica
            return response()->json([
                'message' => 'Ocorreu um erro ao tentar registrar o usuário. Por favor, tente novamente mais tarde.',
                'error' => $e->getMessage(), // Em produção, evite expor a mensagem exata do erro
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // Código 500 Internal Server Error
        }
    }
}
