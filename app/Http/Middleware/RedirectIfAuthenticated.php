<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // if (Auth::user()->type == UserRoleStatus::ADMIN) {
                //     return redirect(RouteServiceProvider::HOME);
                // } else {
                //     return redirect(RouteServiceProvider::USER_HOME);
                // }
                // return redirect(RouteServiceProvider::HOME);
                return redirect(Auth::user()->getRedirectRoute());
            }
        }
        return $next($request);
    }
}
