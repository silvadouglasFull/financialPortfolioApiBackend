<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UserRepositoryInterface; // Importar a interface do repositório
use App\Services\Auth\RegisterValidationServiceInterface; // Opcional: injetar aqui se o serviço for responsável por re-validar
use Illuminate\Validation\ValidationException; // Para lançar exceções de validação, se aplicável
use Throwable; // Para capturar exceções genéricas

/**
 * Classe UserService
 * Responsável pela lógica de negócio relacionada a usuários.
 * Implementa as regras de negócio para criação e manipulação de usuários.
 */
class UserService implements UserServiceInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * @var RegisterValidationServiceInterface
     */
    protected RegisterValidationServiceInterface $registerValidationService; // Injetado para acesso a validações

    /**
     * Construtor do UserService.
     *
     * @param UserRepositoryInterface $userRepository
     * @param RegisterValidationServiceInterface $registerValidationService
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RegisterValidationServiceInterface $registerValidationService // Injetar o serviço de validação
    ) {
        $this->userRepository = $userRepository;
        $this->registerValidationService = $registerValidationService;
    }

    /**
     * Cria um novo usuário no sistema aplicando as regras de negócio.
     *
     * @param array $userData Os dados do usuário a serem criados (já validados pelo RegisterRequest/Service).
     * @return User O objeto User criado.
     * @throws ValidationException Se alguma regra de unicidade falhar (apesar de já validado pelo Request).
     * @throws Throwable Se ocorrer um erro inesperado durante a criação.
     */
    public function createUser(array $userData): User
    {
        // Re-validação de unicidade (redundante mas como fallback se a Request falhar)
        // No entanto, o ideal é que a RegisterRequest já tenha garantido isso.
        // Se o RegisterRequest está usando RegisterValidationService, as validações de 'unique'
        // já foram tratadas lá. Este ponto é mais para regras de negócio que não são apenas de formato.

        // Exemplo: Saldo inicial garantido aqui, mesmo que o request envie outro valor
        $userData['balance'] = 0.00;

        try {
            // A senha já vem hasheada do RegisterRequest se o cast 'hashed' estiver no Model
            // ou se o mutator `setPasswordAttribute` estiver ativo.
            // Se não, faríamos Hash::make($userData['password']) aqui.

            $user = $this->userRepository->create($userData);

            // Outras lógicas de negócio após a criação do usuário, por exemplo:
            // - Envio de e-mail de boas-vindas
            // - Geração de evento UserRegisteredEvent::dispatch($user);

            return $user;
        } catch (Throwable $e) {
            // Logar o erro para depuração
            // Log::error("Erro ao criar usuário: " . $e->getMessage(), ['exception' => $e]);
            // Relançar uma exceção mais amigável ou específica para a aplicação
            throw new \Exception("Falha ao criar usuário: " . $e->getMessage(), 0, $e);
        }
    }

    // Métodos futuros para outras operações de usuário:
    // public function updateUser(User $user, array $data): User { ... }
    // public function getUserBalance(User $user): float { ... }
    // public function canTransfer(User $user): bool { ... } // Exemplo: merchants não podem transferir
}
