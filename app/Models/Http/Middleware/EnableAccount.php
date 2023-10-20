<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnableAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user()->status == 'disable') {
            Auth::logout();
            return redirect()->to('/')->withErrors(['error' => 'Your account is inactive. Please contact to Administrator']);
        }

        return $next($request);
    }
}
