<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Middleware\ThrottleRequests; // Importar o middleware de throttle padrão
use OpenApi\Attributes as OA; // Import OpenApi Attributes

#[OA\Schema(
    schema: "TooManyRequestsError",
    title: "Too Many Requests Error",
    description: "Error response when the rate limit for an endpoint is exceeded.",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Too Many Attempts."),
        new OA\Property(property: "exception", type: "string", example: "Symfony\\Component\\HttpKernel\\Exception\\TooManyRequestsHttpException"),
    ],
    type: "object"
)]
class TransactionThrottle extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     * @throws \Illuminate\Routing\Exceptions\MissingRateLimiterException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): Response
    {
        // Se o usuário não estiver autenticado, não aplica o throttle para ele
        // (o middleware auth:sanctum já cuidará de barrar a requisição antes)
        if (!$request->user()) {
            return $next($request);
        }

        // Usa o ID do usuário autenticado como chave para o throttle,
        // garantindo que o limite seja por usuário.
        // O nome da chave é 'transaction.transfer' para ser específico a esta ação.
        // Isso isola o limite de outras throttles que possam existir.
        $key = 'transaction.transfer:' . $request->user()->id;

        // Chama o método handle do ThrottleRequests pai, passando a chave personalizada
        // e os parâmetros de limite de requisições e tempo de decaimento.
        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $key);
    }
}
