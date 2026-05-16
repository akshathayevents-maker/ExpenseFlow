<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || (! auth()->user()->isAdmin() && ! auth()->user()->isManager())) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
