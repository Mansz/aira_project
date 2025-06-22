<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class InertiaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share common data with all Inertia responses
        Inertia::share([
            'errors' => function () {
                return Session::get('errors')
                    ? Session::get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                ];
            },
            'auth' => function () {
                $user = Auth::user();
                return $user ? [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'permissions' => $user->permissions ?? [],
                    ],
                ] : null;
            },
            'appName' => config('app.name'),
            'apiBaseUrl' => config('app.url') . '/api/v1',
        ]);

        // Handle Inertia requests differently for API clients
        if (request()->header('X-Inertia-Android')) {
            Inertia::setRootView('api');
        }
    }
}
