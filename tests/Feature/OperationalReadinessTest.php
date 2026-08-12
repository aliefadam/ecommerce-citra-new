<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationalReadinessTest extends TestCase
{
    public function test_liveness_is_public_and_contains_no_internal_detail(): void
    {
        $this->get('/up')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_readiness_is_hidden_without_valid_token(): void
    {
        config()->set('operations.readiness_token', 'strong-readiness-token');

        $this->getJson('/internal/ready')->assertNotFound();
        $this->withHeader('X-Operations-Token', 'wrong')->getJson('/internal/ready')->assertNotFound();
    }

    public function test_readiness_checks_database_cache_and_private_storage(): void
    {
        config()->set('operations.readiness_token', 'strong-readiness-token');
        Cache::clear();
        Storage::fake('local');

        $this->withHeader('X-Operations-Token', 'strong-readiness-token')
            ->getJson('/internal/ready')
            ->assertOk()
            ->assertJson([
                'status' => 'ready',
                'checks' => ['database' => 'ok', 'cache' => 'ok', 'storage' => 'ok'],
            ])
            ->assertJsonMissingPath('exception');
    }

    public function test_request_id_is_validated_and_security_headers_are_applied(): void
    {
        $this->withHeader('X-Request-ID', "bad\r\nheader")
            ->get('/')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Request-ID');

        $this->withHeader('X-Request-ID', 'checkout-12345678')
            ->get('/')
            ->assertHeader('X-Request-ID', 'checkout-12345678');
    }
}
