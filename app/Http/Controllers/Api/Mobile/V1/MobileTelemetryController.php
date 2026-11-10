<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\MobileAnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobileTelemetryController extends Controller
{
    private const EVENTS = [
        'app_opened',
        'install_prompted',
        'install_accepted',
        'search_submitted',
        'search_results_viewed',
        'product_viewed',
        'variant_selected',
        'cart_item_added',
        'cart_viewed',
        'checkout_started',
        'checkout_step_completed',
        'checkout_validation_failed',
        'payment_initiated',
        'payment_completed',
        'payment_failed',
        'payment_recovered',
        'route_error',
        'api_error',
        'session_expired',
        'service_worker_updated',
        'route_viewed',
    ];

    private const CONTEXT_KEYS = [
        'status',
        'step',
        'outcome',
        'item_count',
        'result_count',
        'duration_ms',
        'online',
        'install_state',
        'error_type',
    ];

    public function store(Request $request): JsonResponse
    {
        abort_unless(config('mobile-spa.analytics_enabled'), 404);
        $validated = $request->validate([
            'event' => ['required', 'string', Rule::in(self::EVENTS)],
            'version' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.:-]+$/'],
            'route' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_.:-]+$/'],
            'context' => ['nullable', 'array', 'max:10'],
            'context.status' => ['nullable', Rule::in([
                '0', '200', '201', '202', '204', '400', '401', '403', '404', '409', '422', '429', '500', '502', '503',
                'pending', 'menunggu_pembayaran', 'menunggu_verifikasi', 'paid', 'settlement', 'capture', 'process',
                'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'dibatalkan', 'cancel', 'cancelled',
                'expire', 'deny', 'failure',
            ])],
            'context.step' => ['nullable', Rule::in(['order_created', 'order_create'])],
            'context.outcome' => ['nullable', Rule::in(['query', 'browse', 'success', 'failed'])],
            'context.item_count' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'context.result_count' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'context.duration_ms' => ['nullable', 'integer', 'min:0', 'max:600000'],
            'context.online' => ['nullable', 'boolean'],
            'context.install_state' => ['nullable', Rule::in(['available', 'installed', 'update_available'])],
            'context.error_type' => ['nullable', Rule::in(['client_error', 'unhandled_rejection', 'server', 'request'])],
        ]);
        $context = collect($validated['context'] ?? [])
            ->only(self::CONTEXT_KEYS)
            ->map(fn ($value) => is_string($value) ? mb_substr($value, 0, 80) : $value)
            ->all();

        $sessionSource = (string) ($request->cookie((string) config('session.cookie'))
            ?: $request->ip().'|'.$request->userAgent());

        MobileAnalyticsEvent::query()->create([
            'event_name' => $validated['event'],
            'surface' => 'mobile_spa',
            'app_version' => $validated['version'],
            'route_name' => $validated['route'] ?? null,
            'session_hash' => hash_hmac('sha256', $sessionSource, (string) config('app.key')),
            'context' => $context ?: null,
            'occurred_at' => now(),
        ]);

        return response()->json(null, 202);
    }
}
