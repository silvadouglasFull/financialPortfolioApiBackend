<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Rotas da API principal
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rotas WEB principal
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // NOVO: Adiciona rotas de autenticação em um grupo separado da API
            Route::middleware('api') // Garante que use o middleware 'api'
                ->prefix('api')     // Garante o prefixo 'api'
                ->group(base_path('routes/auth.php')); // Ou 'routes/register.php'

            // Você pode até mesmo criar um grupo só para rotas de autenticação,
            // adicionando outro prefixo como /api/auth se quiser
            // Route::middleware('api')
            //     ->prefix('api/auth')
            //     ->group(base_path('routes/auth.php')); // Neste caso, o prefixo 'auth' já estaria no RouteServiceProvider
        });
    }
}
