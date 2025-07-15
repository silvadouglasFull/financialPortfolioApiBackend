<?php

namespace App\Repositories;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Classe EloquentUserRepository
 * Implementação do UserRepositoryInterface usando o Eloquent ORM.
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Cria um novo usuário no banco de dados.
     *
     * @param array $data Os dados do usuário.
     * @return User O objeto User criado.
     */
    public function create(array $data): User
    {
        // O hash da senha é tratado automaticamente pelo Mutator/Cast no Model User
        // O UUID é gerado automaticamente pelo trait HasUuids no Model User
        return User::create($data);
    }

    /**
     * Lista todos os usuários com paginação.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 10): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }
    /**
     * Busca um usuário pelo ID.
     *
     * @param string $id
     * @return User|null
     */
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    /**
     * Busca um usuário pelo e-mail.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Busca um usuário pelo documento (CPF/CNPJ).
     *
     * @param string $document
     * @return User|null
     */
    public function findByDocument(string $document): ?User
    {
        return User::where('document', $document)->first();
    }

    /**
     * Atualiza um usuário existente.
     *
     * @param User $user O objeto User a ser atualizado.
     * @param array $data Os dados para atualização.
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }
    /**
     * Atualiza o saldo de um usuário.
     *
     * @param User $user A instância do usuário.
     * @param float $amount O valor a ser adicionado/subtraído do saldo.
     * @return bool
     */
    public function updateBalance(User $user, float $amount): bool
    {
        // Garante que o balance seja atualizado corretamente, tratando floats.
        $user->balance += $amount;
        return $user->save();
    }
    /**
     * Obtém todos os usuários, exceto um ID específico.
     *
     * @param int|null $excludedId O ID do usuário a ser excluído da lista.
     * @return Collection<User>
     */
    public function getAllExcept(?int $excludedId = null): Collection
    {
        return User::where([
            ["id", "!=", $excludedId],
            ["user_type", "!=", UserTypeEnum::ADMIN]
        ])->get();
    }
    /**
     * Deleta um usuário.
     *
     * @param User $user
     * @return bool|null
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
