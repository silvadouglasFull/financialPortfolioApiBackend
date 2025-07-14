<?php

namespace App\Services\Auth;

use App\Exceptions\AuthenticationException; // Importar a exceção personalizada
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthService
 *
 * Implementa a lógica de negócio para a autenticação de usuários.
 */
class AuthService implements AuthServiceInterface
{
    /**
     * Tenta autenticar um usuário com as credenciais fornecidas e gera um token Sanctum.
     *
     * @param array $credentials Um array contendo 'email' e 'password'.
     * @return array Um array contendo o token de acesso e os dados do usuário.
     * @throws AuthenticationException Se as credenciais forem inválidas.
     */
    public function attemptLogin(array $credentials): array
    {
        // Tenta autenticar o usuário
        if (!Auth::attempt($credentials)) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        // Recupera o usuário autenticado
        $user = Auth::user();
        // Se o Auth::attempt() funcionou, $user não será nulo.
        // Geração do token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retorna o token e os dados do usuário
        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    // Outros métodos de autenticação (social, etc.) serão adicionados aqui.
}
