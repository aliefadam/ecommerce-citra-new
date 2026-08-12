<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GoLiveReadinessReview extends Command
{
    protected $signature = 'ops:go-live-review';

    protected $description = 'Menghasilkan keputusan GO/NO-GO dari bukti P0 release candidate';

    public function handle(): int
    {
        $checks = [
            $this->boolean('feature_freeze', (bool) config('release.feature_freeze'), 'Feature freeze aktif'),
            $this->text('release_owner', (string) config('release.owner'), 'Owner release ditetapkan'),
            $this->recentDate('owner_approval', (string) config('release.approved_at'), 14, 'Sign-off owner maksimal 14 hari'),
            $this->backupArtifact(),
            $this->recentDate('restore_drill', (string) config('release.restore_drill_at'), 90, 'Restore drill maksimal 90 hari'),
            $this->reportArtifact(),
            $this->recentDate('alert_delivery', (string) config('release.alert_tested_at'), 30, 'Alert delivery maksimal 30 hari'),
            $this->recentDate('production_smoke', (string) config('release.production_smoke_at'), 7, 'Production smoke maksimal 7 hari'),
            $this->recentDate('midtrans', (string) config('release.midtrans_certified_at'), 30, 'Midtrans certified maksimal 30 hari'),
            $this->recentDate('rajaongkir', (string) config('release.rajaongkir_certified_at'), 30, 'RajaOngkir certified maksimal 30 hari'),
            $this->recentDate('email', (string) config('release.email_certified_at'), 30, 'Email certified maksimal 30 hari'),
        ];

        if ((bool) config('release.whatsapp_required')) {
            $checks[] = $this->recentDate('whatsapp', (string) config('release.whatsapp_certified_at'), 30, 'WhatsApp certified maksimal 30 hari');
        } else {
            $checks[] = ['whatsapp', 'PASS', 'Tidak termasuk release scope'];
        }

        $this->table(['Gate', 'Status', 'Evidence'], $checks);
        $go = ! collect($checks)->contains(fn (array $check) => $check[1] !== 'PASS');
        $this->newLine();
        $go
            ? $this->info('GO UNTUK PILOT — seluruh bukti P0 tersedia. Tetap jalankan ops:production-check dan quality gate pada artifact final.')
            : $this->error('NO-GO — satu atau lebih bukti P0 belum tersedia/masih kedaluwarsa.');

        return $go ? self::SUCCESS : self::FAILURE;
    }

    private function boolean(string $name, bool $value, string $evidence): array
    {
        return [$name, $value ? 'PASS' : 'FAIL', $evidence];
    }

    private function text(string $name, string $value, string $evidence): array
    {
        return [$name, trim($value) !== '' ? 'PASS' : 'FAIL', $evidence];
    }

    private function recentDate(string $name, string $value, int $days, string $evidence): array
    {
        try {
            $date = CarbonImmutable::parse($value);
            $valid = $value !== '' && $date->betweenIncluded(now()->subDays($days), now()->addMinutes(5));
        } catch (Throwable) {
            $valid = false;
        }

        return [$name, $valid ? 'PASS' : 'FAIL', $evidence];
    }

    private function backupArtifact(): array
    {
        $path = trim((string) config('release.initial_backup_path'));
        $exists = $path !== '' && Storage::disk((string) config('operations.backup_disk', 'local'))->exists($path);

        return ['initial_backup', $exists ? 'PASS' : 'FAIL', 'Artifact backup awal tersedia pada backup disk'];
    }

    private function reportArtifact(): array
    {
        $path = trim((string) config('release.load_test_report'));
        $absolute = $path !== '' && ! str_contains($path, '..') ? base_path($path) : '';
        $exists = $absolute !== '' && is_file($absolute) && filesize($absolute) > 0;

        return ['load_test', $exists ? 'PASS' : 'FAIL', 'Report load/burst/soak release candidate tersedia'];
    }
}
