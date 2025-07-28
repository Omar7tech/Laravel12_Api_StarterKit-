<?php

use App\Http\Controllers\Auth\DeleteAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;


Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
            Route::get('/user', function (Request $request) {
                return $request->user();
            });
            Route::post('/delete-account', [DeleteAccountController::class, 'store']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

    });

    require __DIR__ . '/routes-v1.php';
});


/**
 * -----------------------------------------------------------------------------
 * 🧰 Laravel API Response Helpers — Available Methods (via ApiResponseHelpers)
 * -----------------------------------------------------------------------------
 * ✅ respondWithSuccess(array|Arrayable|JsonSerializable|null $contents = null)
 *     → Returns 200 OK with ['success' => true] or provided data.
 *
 * ✅ respondOk(string $message)
 *     → Returns 200 OK with a custom message.
 *
 * ✅ respondCreated(array|Arrayable|JsonSerializable|null $data = null)
 *     → Returns 201 Created with optional data.
 *
 * ✅ respondNoContent(array|Arrayable|JsonSerializable|null $data = null)
 *     → Returns 204 No Content (optionally with legacy data).
 *
 * ✅ respondNotFound(string|Exception $message, ?string $key = 'error')
 *     → Returns 404 Not Found with error message.
 *
 * ✅ respondUnAuthenticated(?string $message = null)
 *     → Returns 401 Unauthorized.
 *
 * ✅ respondForbidden(?string $message = null)
 *     → Returns 403 Forbidden.
 *
 * ✅ respondError(?string $message = null)
 *     → Returns 400 Bad Request with error message.
 *
 * ✅ setDefaultSuccessResponse(?array $content = null): self
 *     → Overrides default success payload for respondWithSuccess.
 * -----------------------------------------------------------------------------
 */
