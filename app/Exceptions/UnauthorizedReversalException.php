<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class UnauthorizedReversalException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        // Você pode logar a exceção aqui, se quiser um log específico para ela
        // \Log::warning('Tentativa de reversão não autorizada: ' . $this->getMessage());
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function render($request): JsonResponse|Response
    {
        // Se a requisição espera JSON (API), retorna JSON
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Unauthorized to reverse this transaction.',
                'error' => $this->getMessage(),
            ], Response::HTTP_FORBIDDEN); // 403 Forbidden
        }

        // Caso contrário, redireciona ou retorna uma view de erro
        return response()->view('errors.403', [], Response::HTTP_FORBIDDEN);
    }
}
