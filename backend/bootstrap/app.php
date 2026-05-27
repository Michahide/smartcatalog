<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Apply CORS to every request — must come before auth middleware
        $middleware->prepend(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        });
    })->create();
