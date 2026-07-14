<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Middleware\BroadcastAuthMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    
     if (!$this->app->runningInConsole()) {
            // Register custom broadcast auth middleware
            Route::aliasMiddleware('broadcast.jwt.auth', BroadcastAuthMiddleware::class);

            // Register broadcasting route with middleware
            Broadcast::routes(['middleware' => ['broadcast.jwt.auth']]);

            // Load your channel definitions
            require base_path('routes/channels.php');
        }
        Paginator::useBootstrap();
    }
}
