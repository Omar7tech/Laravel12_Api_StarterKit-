<?php

use F9Web\ApiResponseHelpers;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, $request) {

            $responder = new class {
                use ApiResponseHelpers;
            };

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => method_exists($exception, 'errors') ? $exception->errors() : [],
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return $responder->respondUnAuthenticated('Unauthorized.');
            }

            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                return $responder->respondNotFound($exception->getMessage());
            }

            if ($exception instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'HTTP error.',
                ], $exception->getStatusCode());
            }

            
            return $responder->respondError(config('app.debug') ? $exception->getMessage() : 'Server Error.');



        });
    })->create();
