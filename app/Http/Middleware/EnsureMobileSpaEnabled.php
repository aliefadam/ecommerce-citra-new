<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileSpaEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('mobile-spa.enabled'), 404);

        return $next($request);
    }
}
