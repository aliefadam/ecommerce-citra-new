<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\CompanyScope;
use App\Http\Middleware\EnsureCheckoutAccess;
use App\Http\Middleware\EnsureMobileSpaEnabled;
use App\Http\Middleware\LocalizeStorefrontUi;
use App\Http\Middleware\RedirectMobileToSpa;
use App\Http\Middleware\RequestContext;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
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
        $middleware->statefulApi();
        $middleware->append(RequestContext::class);
        $middleware->web(append: [SetLocale::class, LocalizeStorefrontUi::class, RedirectMobileToSpa::class]);
        $middleware->alias([
            'admin' => AdminOnly::class,
            'admin.permission' => AdminPermission::class,
            'company.scope' => CompanyScope::class,
            'checkout.access' => EnsureCheckoutAccess::class,
            'mobile.spa' => EnsureMobileSpaEnabled::class,
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
                $e instanceof AuthenticationException => 401,
                $e instanceof AuthorizationException => 403,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                $e instanceof ModelNotFoundException => 404,
                $e instanceof ValidationException => 422,
                default => 500,
            };

            $message = match ($status) {
                401 => 'Sesi tidak tersedia atau sudah berakhir.',
                403 => 'Anda tidak memiliki akses ke resource ini.',
                404 => 'Resource tidak ditemukan.',
                429 => 'Terlalu banyak permintaan. Coba lagi nanti.',
                500 => 'Terjadi kesalahan pada server.',
                default => $e->getMessage() ?: 'Permintaan tidak dapat diproses.',
            };

            $code = match ($status) {
                401 => 'unauthenticated',
                403 => 'forbidden',
                404 => 'not_found',
                422 => 'validation_failed',
                429 => 'rate_limited',
                500 => 'server_error',
                default => 'request_failed',
            };

            $payload = [
                'code' => $code,
                'message' => $message,
                'request_id' => $request->attributes->get('request_id'),
            ];
            if ($e instanceof ValidationException) {
                $payload['errors'] = $e->errors();
            }

            $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

            return response()->json($payload, $status, $headers);
        });
    })->create();
