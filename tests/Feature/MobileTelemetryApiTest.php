<?php

namespace Tests\Feature;

use App\Models\MobileAnalyticsEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileTelemetryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile-spa.enabled' => true]);
    }

    public function test_telemetry_is_disabled_by_default(): void
    {
        config(['mobile-spa.analytics_enabled' => false]);

        $this->postJson('/api/mobile/v1/telemetry', [
            'event' => 'app_opened',
            'version' => 'rollout-6',
        ])->assertNotFound();
    }

    public function test_allowlisted_event_is_stored_without_personal_or_payment_context(): void
    {
        config(['mobile-spa.analytics_enabled' => true]);
        $this->postJson('/api/mobile/v1/telemetry', [
            'event' => 'checkout_step_completed',
            'version' => 'rollout-6',
            'route' => 'checkout',
            'context' => [
                'step' => 'order_created',
                'outcome' => 'success',
                'email' => 'customer@example.test',
                'address' => 'Jl. Rahasia',
                'payment_token' => 'secret-token',
            ],
        ])->assertAccepted();

        $event = MobileAnalyticsEvent::query()->firstOrFail();
        $this->assertSame('mobile_spa', $event->surface);
        $this->assertSame('rollout-6', $event->app_version);
        $this->assertSame(['step' => 'order_created', 'outcome' => 'success'], $event->context);
        $this->assertSame(64, strlen($event->session_hash));
        $this->assertStringNotContainsString('customer@example.test', $event->toJson());
        $this->assertStringNotContainsString('secret-token', $event->toJson());
    }

    public function test_unknown_event_and_unsafe_route_are_rejected(): void
    {
        config(['mobile-spa.analytics_enabled' => true]);
        $this->postJson('/api/mobile/v1/telemetry', [
            'event' => 'raw_payment_payload',
            'version' => 'rollout-6',
        ])->assertUnprocessable()->assertJsonValidationErrors('event');

        $this->postJson('/api/mobile/v1/telemetry', [
            'event' => 'route_viewed',
            'version' => 'rollout-6',
            'route' => 'search?email=customer@example.test',
        ])->assertUnprocessable()->assertJsonValidationErrors('route');

        $this->postJson('/api/mobile/v1/telemetry', [
            'event' => 'payment_failed',
            'version' => 'rollout-6',
            'context' => ['outcome' => ['payment_token' => 'secret-token']],
        ])->assertUnprocessable()->assertJsonValidationErrors('context.outcome');
    }
}
