<?php

namespace App\Providers;

use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind 'current_actor' into the container dynamically per request
        $this->app->scoped('current_actor', function () {
            /** @var Request $request */
            $request = app('request');
            $user = $request->user();

            if (! $user) {
                return null;
            }

            // Staff user
            if ($user->user_type === 'staff') {
                return $user->staff;
            }

            // Shared Volunteer account
            if ($request->hasHeader('X-Volunteer-ID')) {
                return Volunteer::where('is_active', true)
                    ->find($request->header('X-Volunteer-ID'));
            }

            return null;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}