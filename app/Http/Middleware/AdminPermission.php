<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user || !$user->canAccessAdminPanel()) {
            return redirect()->route('frontend.index')->with('error', 'You do not have access to the admin area.');
        }

        foreach ($permissions as $permission) {
            if ($user->hasAdminPermission($permission)) {
                return $next($request);
            }
        }

        return redirect()->route('pages.index')->with('error', 'You do not have permission to access that page.');
    }
}
