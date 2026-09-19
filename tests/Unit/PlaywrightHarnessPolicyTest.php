<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PlaywrightHarnessPolicyTest extends TestCase
{
    public function test_regular_e2e_suite_does_not_write_tracked_documentation_screenshots(): void
    {
        $browserTests = glob(dirname(__DIR__).'/Browser/*.spec.cjs') ?: [];
        $this->assertNotEmpty($browserTests);

        foreach ($browserTests as $browserTest) {
            $source = (string) file_get_contents($browserTest);

            $this->assertStringNotContainsString(
                'docs/frontend-baseline/screenshots',
                str_replace('\\', '/', $source),
                basename($browserTest).' tidak boleh menulis artifact ke file dokumentasi yang dilacak Git.'
            );
        }
    }
}
