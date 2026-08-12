<?php

namespace Tests\Unit;

use App\Services\MidtransApiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MidtransApiServiceTest extends TestCase
{
    public function test_status_uses_sandbox_basic_auth_and_retries_safe_request(): void
    {
        config()->set('services.midtrans.mode', 'sandbox');
        config()->set('services.midtrans.server_key', 'sandbox-secret');
        config()->set('services.midtrans.retry_times', 1);
        config()->set('services.midtrans.retry_sleep', 0);
        Http::fake([
            '*' => Http::sequence()
                ->push(['status_message' => 'temporary'], 503)
                ->push(['order_id' => 'ORDER-1', 'transaction_status' => 'pending'], 200),
        ]);

        $result = app(MidtransApiService::class)->status('ORDER-1');

        $this->assertSame('pending', $result['transaction_status']);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.sandbox.midtrans.com/v2/ORDER-1/status'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sandbox-secret:')));
    }

    public function test_status_rejects_invalid_provider_response_without_exposing_key(): void
    {
        config()->set('services.midtrans.mode', 'sandbox');
        config()->set('services.midtrans.server_key', 'never-leak-this-key');
        config()->set('services.midtrans.retry_times', 0);
        Http::fake(['*' => Http::response(['unexpected' => true], 200)]);

        try {
            app(MidtransApiService::class)->status('ORDER-2');
            $this->fail('Expected invalid response exception.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('tidak valid', $exception->getMessage());
            $this->assertStringNotContainsString('never-leak-this-key', $exception->getMessage());
        }
    }
}
