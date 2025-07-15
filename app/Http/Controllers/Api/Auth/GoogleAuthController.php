<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite; // Importar Socialite
use App\Services\Auth\AuthServiceInterface; // Importar o serviço de autenticação
use App\Repositories\UserRepositoryInterface; // Importar o repositório de usuário para buscar/criar
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB; // Para transações de banco de dados
use Illuminate\Support\Facades\Log; // Para logs de erro
use App\Enums\UserTypeEnum; // Para o UserTypeEnum
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Class GoogleAuthController
 *
 * Controlador responsável por lidar com a autenticação de usuários via Google Socialite.
 */
class GoogleAuthController extends Controller
{
    protected AuthServiceInterface $authService;
    protected UserRepositoryInterface $userRepository; // Precisamos do repositório para buscar e criar usuários

    /**
     * Construtor do GoogleAuthController.
     *
     * @param AuthServiceInterface $authService O serviço de autenticação injetado.
     * @param UserRepositoryInterface $userRepository O repositório de usuário injetado.
     */
    public function __construct(AuthServiceInterface $authService, UserRepositoryInterface $userRepository)
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    /**
     * Redireciona o usuário para a página de autenticação do Google.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Lida com o callback do Google após a autenticação do usuário.
     *
     * @return JsonResponse
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            // Obtém os detalhes do usuário do Google
            $googleUser = Socialite::driver('google')->user();

            // Tenta encontrar o usuário pelo e-mail
            $user = $this->userRepository->findByEmail($googleUser->getEmail());

            // Se o usuário não existir, cria um novo
            if (!$user) {
                // Transação para garantir atomicidade na criação do usuário
                DB::beginTransaction();
                try {
                    $userData = [
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        // Para CPF/CNPJ, você pode definir um valor padrão
                        // ou exigir que o usuário o forneça após o primeiro login social.
                        // Para simplificar, vamos definir um valor 'social' ou nulo e exigir preenchimento.
                        // Em uma aplicação real, você pode ter um campo "document" nullable
                        // e/ou um fluxo de "onboarding" após o login social.
                        'document' => null, // Ou algum valor padrão/placeholder
                        'password' => Hash::make(Str::random(40)), // Senha aleatória para login social
                        'user_type' => UserTypeEnum::COMMON->value, // Tipo padrão para novos usuários sociais
                        'google_id' => $googleUser->getId(), // Salvar o ID do Google
                    ];

                    $user = $this->userRepository->create($userData);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao criar usuário via Google Socialite: ' . $e->getMessage(), ['google_user_email' => $googleUser->getEmail(), 'exception' => $e]);
                    return response()->json([
                        'message' => 'Ocorreu um erro ao tentar criar o usuário via Google.',
                        'error' => 'Por favor, tente novamente.',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                // Se o usuário existir, e se for um login social pela primeira vez com um usuário existente,
                // pode ser útil vincular o google_id aqui.
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                }
            }

            // Gera um token Sanctum para o usuário (novo ou existente)
            $token = $user->createToken('auth_token_google')->plainTextToken;

            return response()->json([
                'message' => 'Login com Google realizado com sucesso!',
                'token' => $token,
                'user' => $user->makeHidden(['password']), // Oculta a senha antes de retornar
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Lida com erros durante o processo do Socialite (ex: usuário cancelou, problemas de configuração)
            Log::error('Erro no callback do Google Socialite: ' . $e->getMessage(), ['exception' => $e]);

            // Redirecionar para uma URL de erro no frontend ou retornar JSON de erro
            return response()->json([
                'message' => 'Falha na autenticação com Google.',
                'error' => 'Não foi possível autenticar com sua conta Google. Por favor, tente novamente.',
            ], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }
    }
}
