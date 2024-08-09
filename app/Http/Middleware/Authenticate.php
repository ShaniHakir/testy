<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
// app/Http/Middleware/Authenticate.php
public function handle(Request $request, Closure $next)
{
    // Check if the user is logged in or if they are accessing 2FA verification route
    if (!Auth::check() && $request->route()->getName() != 'two_factor.verify') {
        // User is not logged in and not accessing 2FA verify
        return redirect('/login')->withErrors(['You need to be logged in to access this page.']);
    }

    return $next($request);
}

}
