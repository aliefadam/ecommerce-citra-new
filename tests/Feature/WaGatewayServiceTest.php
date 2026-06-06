<?php

namespace Tests\Feature;

use App\Services\WaGatewayService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WaGatewayServiceTest extends TestCase
{
    public function test_prepare_store_uses_internal_token_and_payload(): void
    {
        config()->set('services.wa_gateway.url', 'https://wa.example.test');
        config()->set('services.wa_gateway.token', 'secret-token');

        Http::fake([
            'https://wa.example.test/api/stores' => Http::response([
                'success' => true,
                'storeId' => 'store-1',
            ], 201),
        ]);

        $service = app(WaGatewayService::class);
        $result = $service->prepareStore('Toko Demo', 'store-1', [
            'perMinute' => 10,
            'perDay' => 200,
            'perMonth' => 3000,
        ]);

        $this->assertTrue($result['success']);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://wa.example.test/api/stores'
                && $request->hasHeader('X-Internal-Token', 'secret-token')
                && $request['name'] === 'Toko Demo'
                && $request['storeId'] === 'store-1'
                && $request['limits']['perMinute'] === 10
                && $request['limits']['perDay'] === 200
                && $request['limits']['perMonth'] === 3000;
        });
    }

    public function test_prepare_store_treats_conflict_as_ready(): void
    {
        config()->set('services.wa_gateway.url', 'https://wa.example.test');
        config()->set('services.wa_gateway.token', 'secret-token');

        Http::fake([
            'https://wa.example.test/api/stores' => Http::response([
                'message' => 'Toko sudah ada',
            ], 409),
        ]);

        $service = app(WaGatewayService::class);
        $result = $service->prepareStore('Toko Demo', 'store-1', [
            'perMinute' => 10,
            'perDay' => 200,
            'perMonth' => 3000,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('Toko sudah tersedia di WA Gateway.', $result['message']);
    }

    public function test_status_can_be_loaded_after_prepare_when_store_is_missing(): void
    {
        config()->set('services.wa_gateway.url', 'https://wa.example.test');
        config()->set('services.wa_gateway.token', 'secret-token');

        Http::fake([
            'https://wa.example.test/api/stores/boq-ecommerce/whatsapp/status' => Http::sequence()
                ->push(['message' => 'Toko tidak ditemukan'], 404)
                ->push(['connected' => true, 'status' => 'connected'], 200),
            'https://wa.example.test/api/stores' => Http::response([
                'success' => true,
                'storeId' => 'boq-ecommerce',
            ], 201),
        ]);

        $service = app(WaGatewayService::class);

        try {
            $service->status('boq-ecommerce');
        } catch (\RuntimeException $e) {
            $service->prepareStore('Toko Demo', 'boq-ecommerce', [
                'perMinute' => 10,
                'perDay' => 200,
                'perMonth' => 3000,
            ]);
        }

        $result = $service->status('boq-ecommerce');

        $this->assertTrue($result['connected']);
        Http::assertSentCount(3);
    }
}
