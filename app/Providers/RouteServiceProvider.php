<?php

namespace App\Providers;

use App\Http\Middleware\TransactionThrottle;
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
        Route::aliasMiddleware('transaction.throttle', TransactionThrottle::class);
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            Route::middleware('api') // Garante que use o middleware 'api'
                ->prefix('api')     // Garante o prefixo 'api'
                ->group(base_path('routes/register.php'));

            Route::middleware('api') // Garante que use o middleware 'api'
                ->prefix('api')     // Garante o prefixo 'api'
                ->group(base_path('routes/auth.php'));

            Route::middleware('api') // Garante que use o middleware 'api'
                ->prefix('api')     // Garante o prefixo 'api'
                ->group(base_path('routes/transactions.php'));

            Route::middleware('api') // Garante que use o middleware 'api'
                ->prefix('api')     // Garante o prefixo 'api'
                ->group(base_path('routes/transactions.deposit.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
