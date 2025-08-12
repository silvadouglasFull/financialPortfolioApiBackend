<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\JwtFromCookieMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TransactionThrottle;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('api', [
            EnsureApiTokenIsValid::class
        ]);
        $middleware->prepend([
            JwtFromCookieMiddleware::class
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'checkRole' => CheckRole::class,
            'throttle' => TransactionThrottle::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
