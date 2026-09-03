<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
