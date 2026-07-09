<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel. In the
| new Laravel 11+ structure, this is also where we configure routing,
| middleware, and exception handling.
|
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registramos los alias de middleware personalizados que tenías en Kernel.php
        $middleware->alias([
            'auth' => \App\UI\Http\Middleware\Authenticate::class,
            'guest' => \App\UI\Http\Middleware\RedirectIfAuthenticated::class,
            'role.operaciones' => \App\UI\Http\Middleware\EnsureUserRole::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'acceso-app',
        ]);
    })
    ->withCommands([
        \App\UI\Console\Commands\TestDbConnection::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
