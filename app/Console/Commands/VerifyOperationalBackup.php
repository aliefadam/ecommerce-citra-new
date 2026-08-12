<?php

namespace App\Console\Commands;

use App\Services\OperationalBackupService;
use Illuminate\Console\Command;
use Throwable;

class VerifyOperationalBackup extends Command
{
    protected $signature = 'ops:restore-verify {path : Path artifact pada OPS_BACKUP_DISK}';

    protected $description = 'Mendekripsi dan memverifikasi hash backup tanpa menimpa data aktif';

    public function handle(OperationalBackupService $backup): int
    {
        try {
            $result = $backup->verify((string) $this->argument('path'));
            $this->info("Restore verification lulus: {$result['files']} file, integrity {$result['integrity']}.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Restore verification gagal. Data aktif tidak diubah.');

            return self::FAILURE;
        }
    }
}
