<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->requestId($request);
        $request->attributes->set('request_id', $requestId);

        Log::withContext([
            'request_id' => $requestId,
            'environment' => app()->environment(),
            'route' => $request->route()?->getName() ?? $request->path(),
            'user_id' => $request->user()?->getAuthIdentifier(),
            'company_id' => $request->hasSession()
                ? ((int) $request->session()->get('admin_active_company_id', 0) ?: null)
                : null,
        ]);

        $startedAt = hrtime(true);
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            Log::error('HTTP request failed.', [
                'event' => 'http.request.failed',
                'request_id' => $requestId,
                'method' => $request->method(),
                'route' => $request->route()?->getName() ?? $request->path(),
                'exception_class' => $exception::class,
                'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            ]);
            Log::withoutContext();

            throw $exception;
        }

        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        Log::info('HTTP request completed.', [
            'event' => 'http.request.completed',
            'request_id' => $requestId,
            'method' => $request->method(),
            'route' => $request->route()?->getName() ?? $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
        ]);
        Log::withoutContext();

        return $response;
    }

    private function requestId(Request $request): string
    {
        $incoming = trim((string) $request->header('X-Request-ID'));

        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{7,63}$/', $incoming)
            ? $incoming
            : (string) Str::uuid();
    }
}
