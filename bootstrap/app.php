<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // alias middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('voucher:assign')->everyMinute();

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
