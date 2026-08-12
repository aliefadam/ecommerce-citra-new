<?php

namespace App\Console\Commands;

use App\Services\OperationalBackupService;
use Illuminate\Console\Command;
use Throwable;

class CreateOperationalBackup extends Command
{
    protected $signature = 'ops:backup';

    protected $description = 'Membuat backup SQLite dan file operasional penting yang terenkripsi';

    public function handle(OperationalBackupService $backup): int
    {
        try {
            $result = $backup->create();
            $this->info("Backup berhasil: {$result['path']} ({$result['files']} file, {$result['size']} byte)");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Backup gagal. Periksa operational log; detail sensitif tidak ditampilkan.');

            return self::FAILURE;
        }
    }
}
