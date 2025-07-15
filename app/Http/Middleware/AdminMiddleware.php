<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserTypeEnum; // Importe seu Enum

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if (!Auth::check()) {
            // Se não estiver autenticado, redireciona para a página de login
            // ou aborta com 401 Unauthorized para APIs
            // Para documentação OpenAPI, é importante indicar o que aconteceria em uma rota de API.
            // Para rotas web, o redirect é mais comum.
            if ($request->expectsJson()) {
                abort(401, 'Não autenticado.'); // Retorna JSON para APIs
            }
            return redirect()->route('login');
        }

        // Verifica se o usuário autenticado é um administrador
        // Certifique-se de que o campo 'user_type' no seu modelo User está configurado para um Enum ou string
        // E que seu enum App\Enums\UserTypeEnum::ADMIN esteja correto
        if ($request->user()->user_type !== UserTypeEnum::ADMIN) {
            // Se não for admin, redireciona para alguma página de acesso negado
            // ou aborta com 403 Forbidden
            if ($request->expectsJson()) {
                abort(403, 'Acesso não autorizado. Você não tem permissão de administrador.'); // Retorna JSON para APIs
            }
            return redirect()->route('home')->with('error', 'Você não tem permissão para acessar esta página.'); // Redireciona para web
        }

        return $next($request);
    }
}
