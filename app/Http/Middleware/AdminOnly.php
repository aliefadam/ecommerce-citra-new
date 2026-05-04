<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || strtolower((string) $user->role) !== 'admin') {
            $fallback = route('frontend.index');
            $previous = url()->previous();
            $target = ($previous && $previous !== $request->fullUrl()) ? $previous : $fallback;

            return redirect($target)->with('error', 'Anda tidak memiliki akses ke halaman admin.');
        }

        return $next($request);
    }
}

