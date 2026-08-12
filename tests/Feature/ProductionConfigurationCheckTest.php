<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionConfigurationCheckTest extends TestCase
{
    public function test_unsafe_configuration_fails_release_guard(): void
    {
        config()->set('app.debug', true);
        config()->set('app.url', 'http://example.test');
        config()->set('operations.backup_disk', 'local');

        $this->artisan('ops:production-check')
            ->expectsOutputToContain('APP_DEBUG')
            ->expectsOutputToContain('FAIL')
            ->assertFailed();
    }
}
