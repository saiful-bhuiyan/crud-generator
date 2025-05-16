<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            Auth::user()->update([
                'last_seen' => now(),
                'ip_address' => $request->ip()
            ]);
        }

        return $next($request);
    }
}
