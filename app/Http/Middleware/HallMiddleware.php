<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HallMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, ['admin', 'manager'])) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
