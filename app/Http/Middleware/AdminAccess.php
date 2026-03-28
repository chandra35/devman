<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->can('admin-access')) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
