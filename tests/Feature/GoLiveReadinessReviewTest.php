<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoLiveReadinessReviewTest extends TestCase
{
    public function test_review_is_no_go_when_p0_evidence_is_missing(): void
    {
        config()->set('release.feature_freeze', false);

        $this->artisan('ops:go-live-review')
            ->expectsOutputToContain('NO-GO')
            ->assertFailed();
    }

    public function test_review_is_go_when_all_required_evidence_is_current(): void
    {
        Storage::fake('release-backup');
        Storage::disk('release-backup')->put('backups/initial.zip', 'encrypted-artifact');
        $report = 'storage/framework/testing/load-report.json';
        if (! is_dir(dirname(base_path($report)))) {
            mkdir(dirname(base_path($report)), 0777, true);
        }
        file_put_contents(base_path($report), '{"thresholds":"passed"}');

        config()->set('operations.backup_disk', 'release-backup');
        config()->set('release', [
            'feature_freeze' => true,
            'owner' => 'Release Owner',
            'approved_at' => now()->toIso8601String(),
            'initial_backup_path' => 'backups/initial.zip',
            'restore_drill_at' => now()->toIso8601String(),
            'load_test_report' => $report,
            'alert_tested_at' => now()->toIso8601String(),
            'production_smoke_at' => now()->toIso8601String(),
            'midtrans_certified_at' => now()->toIso8601String(),
            'rajaongkir_certified_at' => now()->toIso8601String(),
            'email_certified_at' => now()->toIso8601String(),
            'whatsapp_required' => false,
            'whatsapp_certified_at' => '',
        ]);

        try {
            $this->artisan('ops:go-live-review')
                ->expectsOutputToContain('GO UNTUK PILOT')
                ->assertSuccessful();
        } finally {
            @unlink(base_path($report));
        }
    }
}
