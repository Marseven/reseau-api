<?php

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (AuthenticationException $e) {
            return ApiResponse::unauthorized($e->getMessage() ?: 'Non authentifié.');
        });

        $exceptions->renderable(function (ValidationException $e) {
            return ApiResponse::error('Erreur de validation.', 422, $e->errors());
        });

        $exceptions->renderable(function (NotFoundHttpException $e) {
            return ApiResponse::notFound('Ressource introuvable.');
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e) {
            return ApiResponse::forbidden($e->getMessage() ?: 'Non autorisé.');
        });
    })->create();
