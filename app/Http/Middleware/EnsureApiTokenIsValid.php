<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o cabeçalho 'Api-Token' está presente e não está vazio
        if (!$request->header('Api-Token')) {
            return response()->json(['message' => 'API Token not provided.'], 401);
        }

        // Você pode adicionar uma lógica mais complexa aqui:
        // - Comparar o token com um valor configurado no .env (ideal para APIs internas ou de parceiros).
        // - Consultar um banco de dados para validar o token e o usuário associado.

        // Exemplo simples: compara com um token configurado no .env
        $validApiToken = env('API_GLOBAL_TOKEN'); // Certifique-se de definir API_GLOBAL_TOKEN no seu .env

        if ($request->header('Api-Token') !== $validApiToken) {
            return response()->json(['message' => 'Invalid API Token.'], 403);
        }

        return $next($request);
    }
}
