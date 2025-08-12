<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator; // Para o método listUsers

interface UserServiceInterface
{
    /**
     * Cria um novo usuário no sistema.
     *
     * @param array $userData Os dados do usuário.
     * @return User
     * @throws \Exception
     */
    public function createUser(array $userData): User;

    /**
     * Lista usuários de forma paginada.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listUsers(int $perPage = 10): LengthAwarePaginator;

    /**
     * Busca um usuário pelo ID.
     *
     * @param string $userId
     * @return User|null
     */
    public function findUserById(string $userId): ?User;

    /**
     * Atualiza um usuário existente no sistema.
     *
     * @param User $user O objeto User a ser atualizado.
     * @param array $userData Os dados para atualização.
     * @return User
     * @throws \Exception
     */
    public function updateUser(User $user, array $userData): User;

    /**
     * Exclui um usuário do sistema.
     *
     * @param User $user O objeto User a ser excluído.
     * @return bool
     * @throws \Exception
     */
    public function deleteUser(User $user): bool;
}
