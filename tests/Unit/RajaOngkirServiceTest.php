<?php

namespace Tests\Unit;

use App\Services\RajaOngkirService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use ReflectionProperty;
use Tests\TestCase;

class RajaOngkirServiceTest extends TestCase
{
    public function test_it_sends_waybill_tracking_request_with_awb_and_courier(): void
    {
        Http::fake([
            '*' => Http::response([
                'meta' => ['status' => 'success', 'code' => 200, 'message' => 'OK'],
                'data' => ['delivered' => false, 'manifest' => []],
            ]),
        ]);

        $service = new RajaOngkirService;
        $this->setProperty($service, 'baseUrl', 'https://rajaongkir.example/api/v1');
        $this->setProperty($service, 'apiKey', 'secret-test-key');

        $result = $service->trackWaybill('JNE123456', 'JNE');

        $this->assertFalse($result['delivered']);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://rajaongkir.example/api/v1/track/waybill?awb=JNE123456&courier=jne'
            && $request->hasHeader('key', 'secret-test-key'));
    }

    private function setProperty(object $target, string $name, mixed $value): void
    {
        $property = new ReflectionProperty($target, $name);
        $property->setValue($target, $value);
    }
}
