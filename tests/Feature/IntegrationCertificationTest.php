<?php

namespace Tests\Feature;

use App\Mail\IntegrationSmokeMail;
use App\Services\MidtransApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class IntegrationCertificationTest extends TestCase
{
    public function test_preflight_masks_credentials_and_passes_configured_providers(): void
    {
        $this->configureProviders();

        $this->artisan('integrations:certify', ['provider' => 'all'])
            ->expectsOutputToContain('Integration certification')
            ->doesntExpectOutputToContain('midtrans-secret')
            ->doesntExpectOutputToContain('raja-secret')
            ->doesntExpectOutputToContain('wa-secret')
            ->assertSuccessful();
    }

    public function test_midtrans_controlled_smoke_checks_matching_order(): void
    {
        $this->configureProviders();
        Http::fake(['https://api.sandbox.midtrans.com/*' => Http::response([
            'order_id' => 'SANDBOX-ORDER-1',
            'transaction_status' => 'pending',
        ])]);

        $this->artisan('integrations:certify', [
            'provider' => 'midtrans',
            '--execute' => true,
            '--order-id' => 'SANDBOX-ORDER-1',
        ])->expectsOutputToContain('Status provider terbaca: pending')->assertSuccessful();
    }

    public function test_rajaongkir_controlled_cost_smoke_uses_explicit_test_locations(): void
    {
        $this->configureProviders();
        Http::fake(['*' => Http::response([
            'meta' => ['status' => 'success', 'code' => 200],
            'data' => [['code' => 'jne', 'service' => 'REG', 'cost' => 12000]],
        ])]);

        $this->artisan('integrations:certify', [
            'provider' => 'rajaongkir',
            '--execute' => true,
            '--origin' => 10,
            '--destination' => 20,
        ])->expectsOutputToContain('1 opsi ongkir diterima')->assertSuccessful();
    }

    public function test_email_smoke_requires_allowlist_and_masks_recipient(): void
    {
        $this->configureProviders();
        config()->set('services.integration_certification.email_allowlist', ['ops@example.test']);
        Mail::fake();

        $this->artisan('integrations:certify', [
            'provider' => 'email',
            '--execute' => true,
            '--email' => 'ops@example.test',
        ])->expectsOutputToContain('op***@example.test')->assertSuccessful();

        Mail::assertSent(IntegrationSmokeMail::class, fn (IntegrationSmokeMail $mail) => $mail->hasTo('ops@example.test'));
    }

    public function test_email_smoke_rejects_recipient_outside_allowlist(): void
    {
        $this->configureProviders();
        config()->set('services.integration_certification.email_allowlist', ['ops@example.test']);
        Mail::fake();

        $this->artisan('integrations:certify', [
            'provider' => 'email',
            '--execute' => true,
            '--email' => 'outsider@example.test',
        ])->expectsOutputToContain('wajib ada di INTEGRATION_SMOKE_EMAIL_ALLOWLIST')->assertFailed();

        Mail::assertNothingSent();
    }

    public function test_whatsapp_controlled_smoke_only_reads_connected_status(): void
    {
        $this->configureProviders();
        Http::fake(['*' => Http::response(['connected' => true, 'status' => 'connected'])]);

        $this->artisan('integrations:certify', [
            'provider' => 'whatsapp',
            '--execute' => true,
            '--wa-store' => 'smoke-store',
        ])->expectsOutputToContain('Gateway reachable dan sesi connected')->assertSuccessful();
    }

    public function test_whatsapp_smoke_accepts_gateway_prefixed_store_identifier(): void
    {
        $this->configureProviders();
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/api/stores/store-smoke-store/whatsapp/status')) {
                return Http::response(['connected' => true, 'status' => 'connected']);
            }

            return Http::response(['message' => 'not found'], 404);
        });

        $this->artisan('integrations:certify', [
            'provider' => 'whatsapp',
            '--execute' => true,
            '--wa-store' => 'smoke-store',
        ])->expectsOutputToContain('Gateway reachable dan sesi connected')->assertSuccessful();
    }

    public function test_certification_failure_redacts_credentials_and_email_addresses(): void
    {
        $this->configureProviders();
        config()->set('services.midtrans.server_key', 'midtrans-secret');
        $midtrans = $this->mock(MidtransApiService::class);
        $midtrans->shouldReceive('mode')->andReturn('sandbox');
        $midtrans->shouldReceive('status')->once()->andThrow(
            new RuntimeException('midtrans-secret failed for ops@example.test')
        );

        $this->artisan('integrations:certify', [
            'provider' => 'midtrans',
            '--execute' => true,
            '--order-id' => 'SANDBOX-ORDER-2',
        ])->expectsOutputToContain('[REDACTED]')
            ->doesntExpectOutputToContain('midtrans-secret')
            ->doesntExpectOutputToContain('ops@example.test')
            ->assertFailed();
    }

    public function test_production_smoke_is_blocked_without_explicit_approval(): void
    {
        $this->configureProviders();
        config()->set('services.midtrans.mode', 'production');
        config()->set('services.integration_certification.allow_production', false);
        Http::preventStrayRequests();

        $this->artisan('integrations:certify', [
            'provider' => 'midtrans',
            '--execute' => true,
            '--order-id' => 'PROD-ORDER-1',
        ])->expectsOutputToContain('Production smoke diblokir')->assertFailed();

        Http::assertNothingSent();
    }

    private function configureProviders(): void
    {
        config()->set('services.midtrans.mode', 'sandbox');
        config()->set('services.midtrans.server_key', 'midtrans-secret');
        config()->set('services.midtrans.retry_times', 0);
        config()->set('services.rajaongkir.mode', 'sandbox');
        config()->set('services.rajaongkir.base_url', 'https://rajaongkir.example/api/v1');
        config()->set('services.rajaongkir.api_key', 'raja-secret');
        config()->set('services.rajaongkir.retry_times', 0);
        config()->set('services.wa_gateway.mode', 'sandbox');
        config()->set('services.wa_gateway.url', 'https://wa.example.test');
        config()->set('services.wa_gateway.token', 'wa-secret');
        config()->set('services.wa_gateway.retry_times', 0);
        config()->set('mail.default', 'smtp');
    }
}
