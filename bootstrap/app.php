<?php

use F9Web\ApiResponseHelpers;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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

        // Optional: Add throttling for API routes
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, $request) {

            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null; 
            }

            $responder = new class {
                use ApiResponseHelpers;
            };

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $exception->getMessage() : 'Validation failed.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            // Authentication errors
            if ($exception instanceof AuthenticationException) {
                $message = config('app.debug') ? $exception->getMessage() : 'Authentication required.';
                return $responder->respondUnAuthenticated($message);
            }

            // Model not found errors
            if ($exception instanceof ModelNotFoundException) {
                $message = config('app.debug') ? $exception->getMessage() : 'Resource not found.';
                return $responder->respondNotFound($message);
            }

            // Route not found errors
            if ($exception instanceof NotFoundHttpException) {
                $message = config('app.debug') ? $exception->getMessage() : 'Endpoint not found.';
                return $responder->respondNotFound($message);
            }

            // Method not allowed errors
            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $exception->getMessage() : 'Method not allowed.',
                ], 405);
            }

            // Rate limiting errors
            if ($exception instanceof TooManyRequestsHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $exception->getMessage() : 'Too many requests. Please try again later.',
                ], 429);
            }

            // Generic HTTP exceptions
            if ($exception instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $exception->getMessage() : 'HTTP error occurred.',
                ], $exception->getStatusCode());
            }

            // Generic server errors
            $message = config('app.debug')
                ? $exception->getMessage()
                : 'An unexpected error occurred.';

            return $responder->respondError($message);
        });

        // Optional: Report exceptions to logging service
        $exceptions->reportable(function (Throwable $e) {
            // Custom reporting logic here if needed
        });
    })
    ->create();
