<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Services\WaGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class AdminWhatsappGatewayController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9._-]+$/'],
            'per_minute' => ['required', 'integer', 'min:1', 'max:10000'],
            'per_day' => ['required', 'integer', 'min:1', 'max:1000000'],
            'per_month' => ['required', 'integer', 'min:1', 'max:10000000'],
        ]);

        StoreSetting::setMany([
            'wa_gateway_store_id' => $validated['store_id'],
            'wa_gateway_per_minute' => (string) $validated['per_minute'],
            'wa_gateway_per_day' => (string) $validated['per_day'],
            'wa_gateway_per_month' => (string) $validated['per_month'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting WhatsApp Gateway berhasil disimpan.',
            'data' => $this->settings(),
        ]);
    }

    public function prepare(WaGatewayService $gateway): JsonResponse
    {
        return $this->run(function () use ($gateway) {
            $settings = $this->settings();
            $result = $gateway->prepareStore(
                StoreSetting::values()['store_name'] ?? 'Ecommerce Citra',
                $settings['storeId'],
                [
                    'perMinute' => $settings['limits']['perMinute'],
                    'perDay' => $settings['limits']['perDay'],
                    'perMonth' => $settings['limits']['perMonth'],
                ]
            );

            return [
                'message' => (string) ($result['message'] ?? 'Toko siap dipakai di WA Gateway.'),
                'data' => $result,
            ];
        });
    }

    public function connect(Request $request, WaGatewayService $gateway): JsonResponse
    {
        return $this->run(function () use ($request, $gateway) {
            $result = $this->withPreparedStore($gateway, fn () => $gateway->connect($this->storeId(), $request->boolean('reset')));

            return [
                'message' => (string) ($result['message'] ?? 'Sesi WhatsApp sedang disiapkan.'),
                'data' => $result,
            ];
        });
    }

    public function disconnect(WaGatewayService $gateway): JsonResponse
    {
        return $this->run(function () use ($gateway) {
            $result = $gateway->disconnect($this->storeId());

            return [
                'message' => (string) ($result['message'] ?? 'Sesi WhatsApp berhasil diputuskan.'),
                'data' => $result,
            ];
        });
    }

    public function status(WaGatewayService $gateway): JsonResponse
    {
        return $this->run(fn () => [
            'message' => 'Status WhatsApp Gateway berhasil dimuat.',
            'data' => $this->withPreparedStore($gateway, fn () => $gateway->status($this->storeId())),
        ]);
    }

    public function qr(WaGatewayService $gateway): JsonResponse
    {
        return $this->run(fn () => [
            'message' => 'QR WhatsApp berhasil dimuat.',
            'data' => $this->withPreparedStore($gateway, fn () => $gateway->qr($this->storeId())),
        ]);
    }

    public function qrRaw(WaGatewayService $gateway): Response
    {
        $response = $this->withPreparedStore($gateway, fn () => $gateway->qrRaw($this->storeId()));

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type', 'image/png'),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function usage(WaGatewayService $gateway): JsonResponse
    {
        return $this->run(fn () => [
            'message' => 'Kuota WhatsApp Gateway berhasil dimuat.',
            'data' => $this->withPreparedStore($gateway, fn () => $gateway->usage($this->storeId())),
        ]);
    }

    private function run(callable $callback): JsonResponse
    {
        try {
            $payload = $callback();

            return response()->json([
                'success' => true,
                'message' => $payload['message'] ?? 'Berhasil.',
                'data' => $payload['data'] ?? [],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function settings(): array
    {
        $settings = StoreSetting::values();

        return [
            'storeId' => $this->normalizeStoreId((string) ($settings['wa_gateway_store_id'] ?? 'boq-ecommerce')),
            'limits' => [
                'perMinute' => (int) ($settings['wa_gateway_per_minute'] ?? 10),
                'perDay' => (int) ($settings['wa_gateway_per_day'] ?? 200),
                'perMonth' => (int) ($settings['wa_gateway_per_month'] ?? 3000),
            ],
        ];
    }

    private function storeId(): string
    {
        return $this->settings()['storeId'];
    }

    private function normalizeStoreId(string $storeId): string
    {
        $storeId = trim($storeId);
        foreach (['session-store-session-', 'session-store-', 'session-'] as $prefix) {
            if (str_starts_with($storeId, $prefix)) {
                return substr($storeId, strlen($prefix));
            }
        }

        return $storeId !== '' ? $storeId : 'boq-ecommerce';
    }

    private function withPreparedStore(WaGatewayService $gateway, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (RuntimeException $e) {
            if (! str_contains(strtolower($e->getMessage()), 'toko tidak ditemukan')) {
                throw $e;
            }

            $settings = $this->settings();
            $gateway->prepareStore(
                StoreSetting::values()['store_name'] ?? 'Ecommerce Citra',
                $settings['storeId'],
                [
                    'perMinute' => $settings['limits']['perMinute'],
                    'perDay' => $settings['limits']['perDay'],
                    'perMonth' => $settings['limits']['perMonth'],
                ],
            );

            return $callback();
        }
    }
}
