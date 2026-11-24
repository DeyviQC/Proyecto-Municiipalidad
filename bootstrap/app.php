<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Keep signed provider payloads byte-for-byte intact before HMAC verification.
        $middleware->trimStrings(except: [fn ($request) => $request->is('pagos/izipay/retorno', 'pagos/izipay/notificacion')]);
        $middleware->validateCsrfTokens(except: ['pagos/taypi/notificacion', 'pagos/izipay/retorno', 'pagos/izipay/notificacion', 'pagos/mercadopago/notificacion', 'firma-peru/*/*/parametros', 'firma-peru/*/*/resultado']);
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
