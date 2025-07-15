<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface UserRepositoryInterface
 * Define o contrato para operações de persistência de usuário.
 */
interface UserRepositoryInterface
{
    /**
     * Cria um novo usuário no banco de dados.
     *
     * @param array $data Os dados do usuário.
     * @return User O objeto User criado.
     */
    public function create(array $data): User;

    /**
     * Busca um usuário pelo ID.
     *
     * @param string $id
     * @return User|null
     */
    public function findById(string $id): ?User;

    /**
     * Busca um usuário pelo e-mail.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Busca um usuário pelo documento (CPF/CNPJ).
     *
     * @param string $document
     * @return User|null
     */
    public function findByDocument(string $document): ?User;

    /**
     * Atualiza um usuário existente.
     *
     * @param User $user O objeto User a ser atualizado.
     * @param array $data Os dados para atualização.
     * @return User
     */
    public function update(User $user, array $data): User;
    /**
     * Atualiza o saldo de um usuário.
     *
     * @param User $user A instância do usuário.
     * @param float $amount O valor a ser adicionado/subtraído do saldo.
     * @return bool
     */
    public function updateBalance(User $user, float $amount): bool;


    /**
     * Obtém todos os usuários, exceto um ID específico.
     *
     * @param int|null $excludedId O ID do usuário a ser excluído da lista.
     * @return Collection<User>
     */
    public function getAllExcept(?int $excludedId = null): Collection;
}
