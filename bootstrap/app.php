<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Configurar model bindings para nombres en español
            Route::bind('estudiante', function ($value) {
                return \App\Models\Student::findOrFail($value);
            });

            Route::bind('carrera', function ($value) {
                return \App\Models\CareerFeeConfig::findOrFail($value);
            });

            Route::bind('producto', function ($value) {
                return \App\Models\Product::findOrFail($value);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'punto_venta' => \App\Http\Middleware\PuntoVentaMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'box_menu' => \App\Http\Middleware\BoxMenuMiddleware::class,
            'postgrado_menu' => \App\Http\Middleware\PostgradoMenuMiddleware::class,
            'odonto_menu' => \App\Http\Middleware\OdontoMenuMiddleware::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AdminLTEServiceProvider::class,
        \App\Providers\HelperServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
