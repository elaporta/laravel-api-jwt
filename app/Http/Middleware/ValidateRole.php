<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        $roles = explode('|', $roles);

        if(auth()->check() && in_array($request->user()->role, $roles)){
            return $next($request);
        }
        else{
            return response()->json(['error' => 'Forbidden'], 403);
        }
    }
}
