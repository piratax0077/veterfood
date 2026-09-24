<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Route;

// En WAMP varios sitios comparten el mismo Apache y se pasaban los datos del .env entre ellos
// (la tienda terminaba consultando la base de VET-SDI). Asi cada sitio usa solo su propio .env.
Env::disablePutenv();

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/contabilidad.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api_contabilidad.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            '2fa' => \App\Http\Middleware\EnsureTwoFactorVerified::class,
            'secure.session' => \App\Http\Middleware\ExpireSensitiveSessionOnInactivity::class,
            'contabilidad.ability' => \App\Http\Middleware\EnsureContabilidadAbility::class,
            'contabilidad.centro' => \App\Http\Middleware\VerifyCentroMedicoAccess::class,
        ]);

        // Si la sesion del cliente se cierra sola (inactividad), que vuelva al inicio de la tienda
        // en vez de mostrarle el formulario de login del panel administrativo.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->routeIs('cliente.*', 'vouchers.usuario', 'encuesta.usuario*')
                ? route('tienda.inicio')
                : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
