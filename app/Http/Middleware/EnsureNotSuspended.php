<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->suspended_at, 403, 'This account is suspended.');

        return $next($request);
    }
}
