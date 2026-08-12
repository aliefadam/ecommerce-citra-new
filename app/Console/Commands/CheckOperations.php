<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CheckOperations extends Command
{
    protected $signature = 'ops:check';

    protected $description = 'Memeriksa database, queue, disk, failed jobs, dan freshness backup untuk alerting';

    public function handle(): int
    {
        $results = [];
        $critical = false;

        try {
            DB::select('select 1');
            $results[] = ['database', 'PASS', 'reachable'];
        } catch (Throwable) {
            $results[] = ['database', 'CRITICAL', 'unavailable'];
            $critical = true;
        }

        if (Schema::hasTable('failed_jobs')) {
            $failed = DB::table('failed_jobs')->count();
            $results[] = ['failed_jobs', $failed > 0 ? 'CRITICAL' : 'PASS', (string) $failed];
            $critical = $critical || $failed > 0;
        }

        if (Schema::hasTable('jobs')) {
            $oldest = DB::table('jobs')->min('created_at');
            $ageMinutes = $oldest ? (int) floor((time() - (int) $oldest) / 60) : 0;
            $limit = max(1, (int) config('operations.queue_oldest_minutes', 10));
            $results[] = ['queue_oldest_minutes', $ageMinutes > $limit ? 'CRITICAL' : 'PASS', (string) $ageMinutes];
            $critical = $critical || $ageMinutes > $limit;
        }

        $diskTotal = @disk_total_space(storage_path());
        $diskFree = @disk_free_space(storage_path());
        $used = ($diskTotal && $diskFree !== false) ? (int) round((1 - ($diskFree / $diskTotal)) * 100) : null;
        $diskCritical = max(1, (int) config('operations.disk_critical_percent', 90));
        $diskWarning = max(1, (int) config('operations.disk_warning_percent', 80));
        $diskStatus = $used === null ? 'UNKNOWN' : ($used >= $diskCritical ? 'CRITICAL' : ($used >= $diskWarning ? 'WARNING' : 'PASS'));
        $results[] = ['disk_used_percent', $diskStatus, $used === null ? 'unknown' : (string) $used];
        $critical = $critical || $diskStatus === 'CRITICAL';

        $backupDisk = Storage::disk((string) config('operations.backup_disk', 'local'));
        $directory = trim((string) config('operations.backup_directory', 'backups'), '/');
        $backups = array_values(array_filter($backupDisk->files($directory), fn (string $path) => str_ends_with($path, '.zip')));
        $latest = $backups === [] ? null : max(array_map(fn (string $path) => $backupDisk->lastModified($path), $backups));
        $ageHours = $latest ? (int) floor((time() - $latest) / 3600) : null;
        $maxAge = max(1, (int) config('operations.backup_max_age_hours', 26));
        $backupOk = $ageHours !== null && $ageHours <= $maxAge;
        $results[] = ['backup_age_hours', $backupOk ? 'PASS' : 'CRITICAL', $ageHours === null ? 'missing' : (string) $ageHours];
        $critical = $critical || ! $backupOk;

        $this->table(['Check', 'Status', 'Value'], $results);

        return $critical ? self::FAILURE : self::SUCCESS;
    }
}
