<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Daftarkan alias 'role' ke class RoleMiddleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // 2. Arahkan pengguna yang sudah login saat mengakses rute guest (/login)
        $middleware->redirectUsersTo(function (Request $request) {
            if (Auth::user()?->role === 'admin') {
                return '/admin/dashboard';
            }

            return '/pos/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();