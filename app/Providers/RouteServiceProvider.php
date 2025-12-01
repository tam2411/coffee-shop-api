<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The namespace to assume when generating URLs to actions.
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, and other routes.
     */
    public function boot(): void
    {
        // Load Broadcast Channels (VERY IMPORTANT)
        Broadcast::routes();

        // Load custom channels.php
        $this->loadRoutesFrom(base_path('routes/channels.php'));

        // Define api & web routes
        Route::middleware('api')
            ->prefix('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
}
