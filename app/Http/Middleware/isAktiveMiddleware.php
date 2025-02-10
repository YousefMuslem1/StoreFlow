<?php

namespace App\Http\Middleware;

use App\Enums\UserRoleStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isAktiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Check the user's status
            if (Auth::user()->status == UserRoleStatus::NOT_ACTIVE) {
                // Logout the user
                Auth::logout();
                // Redirect with a message
                return redirect('/login')->withErrors('Your account is inactive. Please contact support.');
            }
        }

        // Allow request to proceed if status is 1
        return $next($request);
    }
}
