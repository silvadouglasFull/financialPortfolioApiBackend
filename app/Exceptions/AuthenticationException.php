<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response; // Importar para usar constantes HTTP

/**
 * Class AuthenticationException
 *
 * Exceção personalizada para erros de autenticação (ex: credenciais inválidas).
 */
class AuthenticationException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): bool
    {
        // Você pode registrar a exceção em um serviço de logs externo aqui
        return false; // Retorna false para que a exceção seja renderizada pelo handler padrão
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Falha na autenticação.',
            'error' => $this->getMessage(), // A mensagem personalizada 'Credenciais inválidas.'
        ], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
    }
}
