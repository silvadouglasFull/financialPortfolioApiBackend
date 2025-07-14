<?php

namespace App\Services\User;

use App\Models\User;

/**
 * Interface UserServiceInterface
 * Define o contrato para as operações de lógica de negócio relacionadas a usuários.
 */
interface UserServiceInterface
{
    /**
     * Cria um novo usuário.
     *
     * @param array $userData Dados do usuário, já validados.
     * @return User O objeto User criado.
     * @throws \Exception Se houver um problema na criação.
     */
    public function createUser(array $userData): User;
}
