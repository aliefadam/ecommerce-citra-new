<?php

namespace Tests\Unit;

use App\Services\RajaOngkirService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use ReflectionProperty;
use RuntimeException;
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

    public function test_cost_contract_normalizes_weight_and_retries_transient_failure(): void
    {
        config()->set('services.rajaongkir.base_url', 'https://rajaongkir.example/api/v1');
        config()->set('services.rajaongkir.api_key', 'secret-test-key');
        config()->set('services.rajaongkir.retry_times', 1);
        config()->set('services.rajaongkir.retry_sleep', 0);
        Http::fake([
            '*' => Http::sequence()
                ->push(['meta' => ['message' => 'busy']], 503)
                ->push([
                    'meta' => ['status' => 'success', 'code' => 200],
                    'data' => [['code' => 'jne', 'service' => 'REG', 'cost' => 12000]],
                ], 200),
        ]);

        $result = app(RajaOngkirService::class)->calculateDomesticCost(10, 20, 0, 'jne:sicepat');

        $this->assertSame('REG', $result[0]['service']);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://rajaongkir.example/api/v1/calculate/domestic-cost'
            && $request['origin'] === 10
            && $request['destination'] === 20
            && $request['weight'] === 1
            && $request['courier'] === 'jne:sicepat');
    }

    public function test_provider_error_is_sanitized_and_bounded(): void
    {
        config()->set('services.rajaongkir.base_url', 'https://rajaongkir.example/api/v1');
        config()->set('services.rajaongkir.api_key', 'secret-test-key');
        config()->set('services.rajaongkir.retry_times', 0);
        Http::fake(['*' => Http::response([
            'meta' => ['status' => 'error', 'message' => "Invalid\nrequest".str_repeat('x', 500)],
        ], 422)]);

        try {
            app(RajaOngkirService::class)->provinces();
            $this->fail('Expected RajaOngkir error.');
        } catch (RuntimeException $exception) {
            $this->assertStringNotContainsString("\n", $exception->getMessage());
            $this->assertLessThanOrEqual(300, mb_strlen($exception->getMessage()));
            $this->assertStringNotContainsString('secret-test-key', $exception->getMessage());
        }
    }

    private function setProperty(object $target, string $name, mixed $value): void
    {
        $property = new ReflectionProperty($target, $name);
        $property->setValue($target, $value);
    }
}
