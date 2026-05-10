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

        if (!$user || !$user->canAccessAdminPanel()) {
            $fallback = route('frontend.index');
            $previous = url()->previous();
            $target = ($previous && $previous !== $request->fullUrl()) ? $previous : $fallback;

            return redirect($target)->with('error', 'You do not have access to the admin area.');
        }

        return $next($request);
    }
}
