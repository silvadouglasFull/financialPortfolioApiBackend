<?php

namespace App\Services\Auth;


/**
 * Interface AuthServiceInterface
 *
 * Define o contrato para os serviços de autenticação.
 */
interface AuthServiceInterface
{
    /**
     * Tenta autenticar um usuário com as credenciais fornecidas.
     *
     * @param array $credentials Um array contendo 'email' e 'password'.
     * @return array Um array contendo o token de acesso e os dados do usuário.
     * @throws \App\Exceptions\AuthenticationException Se as credenciais forem inválidas.
     */
    public function attemptLogin(array $credentials): array;

    // Outros métodos de autenticação podem ser adicionados aqui no futuro,
    // como login social, logout, etc.
}
