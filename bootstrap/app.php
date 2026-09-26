<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\CompanyScope;
use App\Http\Middleware\EnsureCheckoutAccess;
use App\Http\Middleware\RequestContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RequestContext::class);
        $middleware->alias([
            'admin' => AdminOnly::class,
            'admin.permission' => AdminPermission::class,
            'company.scope' => CompanyScope::class,
            'checkout.access' => EnsureCheckoutAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Open Catalog API: selalu balas JSON bersih & konsisten (tanpa stack
        // trace / nama model internal), baik saat debug maupun produksi.
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                $e instanceof ModelNotFoundException => 404,
                $e instanceof ValidationException => 422,
                default => 500,
            };

            $message = match ($status) {
                404 => 'Resource tidak ditemukan.',
                429 => 'Terlalu banyak permintaan. Coba lagi nanti.',
                500 => 'Terjadi kesalahan pada server.',
                default => $e->getMessage() ?: 'Permintaan tidak dapat diproses.',
            };

            $payload = ['message' => $message];
            if ($e instanceof ValidationException) {
                $payload['errors'] = $e->errors();
            }

            $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

            return response()->json($payload, $status, $headers);
        });
    })->create();
