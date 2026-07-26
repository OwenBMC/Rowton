<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Volunteer;

class ResolveActorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $actor = null;
        $user = $request->user();

        if ($user) {
            if ($user->user_type === 'staff') {
                // Ensure the 'staff' relationship is loaded
                $actor = $user->staff; 
            } elseif ($request->hasHeader('X-Volunteer-ID')) {
                $actor = Volunteer::where('is_active', true)
                    ->find($request->header('X-Volunteer-ID'));
            }
        }

        // Always bind 'current_actor' into the container (even if null)
        app()->instance('current_actor', $actor);

        return $next($request);
    }
}