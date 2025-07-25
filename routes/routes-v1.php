<?php
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->get('/routes', function () {
    if (env('APP_ENV') !== 'local' || App::environment('local') === false) {
        return response()->json([
            'success' => false,

        ]);
    }

    $allRoutes = collect(app('router')->getRoutes())
        ->filter(function ($route) {
            return str_starts_with($route->uri(), 'v1/') || str_starts_with($route->uri(), 'api/v1/');
        })
        ->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'middleware' => $route->middleware(),
            ];
        });

    $grouped = [
        'auth' => [],
        'guest' => [],
    ];

    foreach ($allRoutes as $route) {
        if (in_array('auth:sanctum', $route['middleware'])) {
            $grouped['auth'][] = [
                'method' => $route['method'],
                'uri' => $route['uri'],
            ];
        } else {
            $grouped['guest'][] = [
                'method' => $route['method'],
                'uri' => $route['uri'],
            ];
        }
    }

    return response()->json([
        'success' => true,
        'routes' => $grouped,
    ]);
});
