<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\Auth\RegisterValidationServiceInterface;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Facades\Log; // Importar para logar erros
use Illuminate\Pagination\LengthAwarePaginator; // Para o tipo de retorno

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
    protected RegisterValidationServiceInterface $registerValidationService;

    /**
     * Construtor do UserService.
     *
     * @param UserRepositoryInterface $userRepository
     * @param RegisterValidationServiceInterface $registerValidationService
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RegisterValidationServiceInterface $registerValidationService
    ) {
        $this->userRepository = $userRepository;
        $this->registerValidationService = $registerValidationService;
    }

    /**
     * Cria um novo usuário no sistema aplicando as regras de negócio.
     *
     * @param array $userData Os dados do usuário a serem criados.
     * @return User O objeto User criado.
     * @throws \Exception Se ocorrer um erro inesperado.
     */
    public function createUser(array $userData): User
    {
        $userData['balance'] = 0.00; // Garante saldo inicial

        try {
            $user = $this->userRepository->create($userData);
            Log::info("User created successfully: " . $user->email);
            return $user;
        } catch (Throwable $e) {
            Log::error("Failed to create user: " . $e->getMessage(), ['exception' => $e, 'userData' => $userData]);
            throw new \Exception("Failed to create user: " . $e->getMessage(), 0, $e);
        }
    }


    /**
     * Lista usuários de forma paginada.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws \Exception Se ocorrer um erro inesperado.
     */
    public function listUsers(int $perPage = 10): LengthAwarePaginator
    {
        try {
            // Delega a busca paginada ao repositório.
            return $this->userRepository->all($perPage);
        } catch (Throwable $e) {
            Log::error("Failed to list users: " . $e->getMessage(), ['exception' => $e]);
            throw new \Exception("Failed to retrieve users: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca um usuário pelo ID.
     *
     * @param int $userId
     * @return User|null
     * @throws \Exception Se ocorrer um erro inesperado.
     */
    public function findUserById(int $userId): ?User
    {
        try {
            return $this->userRepository->findById((string)$userId); // Assegura que o ID é string se findById esperar string
        } catch (Throwable $e) {
            Log::error("Failed to find user by ID: " . $e->getMessage(), ['exception' => $e, 'userId' => $userId]);
            throw new \Exception("Failed to find user: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Atualiza um usuário existente no sistema.
     *
     * @param User $user O objeto User a ser atualizado.
     * @param array $userData Os dados para atualização.
     * @return User
     * @throws \Exception Se ocorrer um erro inesperado.
     */
    public function updateUser(User $user, array $userData): User
    {
        try {
            $user = $this->userRepository->update($user, $userData);
            Log::info("User updated successfully: " . $user->email);
            return $user;
        } catch (ValidationException $e) {
            // Captura erros de validação de unicidade se forem lançados pelo serviço de validação
            throw $e;
        } catch (Throwable $e) {
            Log::error("Failed to update user: " . $e->getMessage(), ['exception' => $e, 'userId' => $user->id, 'userData' => $userData]);
            throw new \Exception("Failed to update user: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Exclui um usuário do sistema.
     *
     * @param User $user O objeto User a ser excluído.
     * @return bool
     * @throws \Exception Se ocorrer um erro inesperado.
     */
    public function deleteUser(User $user): bool
    {
        try {
            // Implemente aqui regras de negócio adicionais antes de excluir.
            // Ex: Verificar se o usuário tem transações pendentes ou saldo.
            // if ($user->balance > 0) {
            //     throw new \Exception("Cannot delete user with remaining balance.");
            // }
            // if ($user->hasPendingTransactions()) { // Exemplo de método no modelo
            //     throw new \Exception("Cannot delete user with pending transactions.");
            // }

            $result = $this->userRepository->delete($user);
            if ($result) {
                Log::info("User deleted successfully: " . $user->email);
            } else {
                Log::warning("Failed to delete user: " . $user->email . " (Repository returned false)");
            }
            return $result;
        } catch (Throwable $e) {
            Log::error("Failed to delete user: " . $e->getMessage(), ['exception' => $e, 'userId' => $user->id]);
            throw new \Exception("Failed to delete user: " . $e->getMessage(), 0, $e);
        }
    }
}
