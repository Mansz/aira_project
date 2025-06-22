<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->user() && $request->user() instanceof \App\Models\Admin) {
                // Admin users get higher limits
                return Limit::perMinute(300)->by($request->user()->id);
            }
            
            if ($request->user()) {
                // Authenticated users get medium limits
                return Limit::perMinute(120)->by($request->user()->id);
            }

            // Public API gets lower limits
            return Limit::perMinute(60)->by($request->ip());
        });

        // Specific limits for authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Specific limits for streaming endpoints
        RateLimiter::for('streaming', function (Request $request) {
            return Limit::perMinute(180)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Admin routes
            Route::middleware(['web', 'auth:sanctum', 'verified', 'can:access-admin'])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        });
    }
}
