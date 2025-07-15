<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // Importar o LoginRequest
use App\Services\Auth\AuthServiceInterface; // Importar a interface do serviço de autenticação
use App\Exceptions\AuthenticationException; // Importar a exceção de autenticação
use Illuminate\Http\JsonResponse; // Para tipagem do retorno
use Illuminate\Http\Response; // Para constantes de status HTTP
use Illuminate\Support\Facades\Log;

/**
 * Class LoginController
 *
 * Controlador responsável por lidar com o login de usuários.
 */
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

    /**
     * Lida com a requisição de login de usuário.
     *
     * @param LoginRequest $request A requisição de login validada.
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Tenta autenticar o usuário usando o AuthService
            $result = $this->authService->attemptLogin($request->only('email', 'password'));
            // Retorna o token e os dados do usuário em caso de sucesso
            return response()->json([
                'message' => 'Login realizado com sucesso!',
                'token' => $result['token'],
                'user' => $result['user'],
            ], Response::HTTP_OK); // 200 OK

        } catch (AuthenticationException $e) {
            // Captura a exceção de autenticação personalizada para credenciais inválidas
            return response()->json([
                'message' => 'Falha na autenticação.',
                'error' => $e->getMessage(), // Ex: 'Credenciais inválidas.'
            ], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        } catch (\Exception $e) {
            // Captura quaisquer outras exceções inesperadas
            Log::error('Erro ao tentar fazer login: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Ocorreu um erro interno ao tentar realizar o login.',
                'error' => 'Por favor, tente novamente mais tarde.' // Mensagem mais genérica para o usuário
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500 Internal Server Error
        }
    }
}
