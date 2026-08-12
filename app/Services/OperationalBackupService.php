<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class OperationalBackupService
{
    public function create(): array
    {
        $password = $this->password();
        $disk = Storage::disk((string) config('operations.backup_disk', 'local'));
        $directory = trim((string) config('operations.backup_directory', 'backups'), '/');
        $name = 'ecommerce-citra-'.now()->utc()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.zip';
        $target = $directory.'/'.$name;
        $temporary = $this->temporaryPath('backup-');
        $database = $this->databaseSnapshot();

        try {
            $zip = new ZipArchive;
            if ($zip->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Arsip backup tidak dapat dibuat.');
            }

            $manifest = ['created_at' => now()->utc()->toIso8601String(), 'files' => []];
            $this->addEncrypted($zip, $database['name'], $database['contents'], $password, $manifest);
            $this->addDiskDirectory($zip, 'local', 'tax-invoices', 'private/tax-invoices', $password, $manifest);
            $this->addDiskDirectory($zip, 'local', 'payment-proofs', 'private/payment-proofs', $password, $manifest);
            $this->addDiskDirectory($zip, 'public', 'payment-proofs', 'public/payment-proofs', $password, $manifest);
            $this->addEncrypted($zip, 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), $password, $manifest, false);
            $zip->close();

            $stream = fopen($temporary, 'rb');
            if (! is_resource($stream) || ! $disk->writeStream($target, $stream)) {
                throw new RuntimeException('Arsip backup tidak dapat disimpan ke backup disk.');
            }
            fclose($stream);
            $this->prune($disk, $directory);

            return ['path' => $target, 'size' => (int) $disk->size($target), 'files' => count($manifest['files'])];
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    public function verify(string $path): array
    {
        $disk = Storage::disk((string) config('operations.backup_disk', 'local'));
        if (! $disk->exists($path)) {
            throw new RuntimeException('Artifact backup tidak ditemukan.');
        }

        $temporary = $this->temporaryPath('restore-');
        $zip = null;
        try {
            $source = $disk->readStream($path);
            $target = fopen($temporary, 'wb');
            if (! is_resource($source) || ! is_resource($target)) {
                throw new RuntimeException('Artifact backup tidak dapat dibaca.');
            }
            stream_copy_to_stream($source, $target);
            fclose($source);
            fclose($target);

            $zip = new ZipArchive;
            if ($zip->open($temporary) !== true) {
                throw new RuntimeException('Artifact backup bukan ZIP yang valid.');
            }
            $zip->setPassword($this->password());
            $manifestJson = $zip->getFromName('manifest.json');
            if (! is_string($manifestJson)) {
                throw new RuntimeException('Manifest backup tidak dapat didekripsi.');
            }
            $manifest = json_decode($manifestJson, true, flags: JSON_THROW_ON_ERROR);
            foreach ((array) ($manifest['files'] ?? []) as $name => $hash) {
                if ($this->unsafeArchiveName((string) $name)) {
                    throw new RuntimeException('Artifact backup memiliki path tidak aman.');
                }
                $contents = $zip->getFromName((string) $name);
                if (! is_string($contents) || ! hash_equals((string) $hash, hash('sha256', $contents))) {
                    throw new RuntimeException('Integrity check artifact backup gagal.');
                }
            }
            $count = count((array) ($manifest['files'] ?? []));
            $zip->close();
            $zip = null;

            return ['created_at' => $manifest['created_at'] ?? null, 'files' => $count, 'integrity' => 'ok'];
        } finally {
            if ($zip instanceof ZipArchive) {
                $zip->close();
            }
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    private function databaseSnapshot(): array
    {
        $connection = (string) config('database.default');
        $config = (array) config('database.connections.'.$connection, []);
        if (($config['driver'] ?? null) === 'mysql') {
            return $this->mysqlSnapshot($config);
        }

        if (($config['driver'] ?? null) !== 'sqlite') {
            throw new RuntimeException('Driver database belum didukung oleh backup aplikasi. Gunakan managed backup sesuai runbook.');
        }

        $path = (string) ($config['database'] ?? '');
        if ($path === '' || $path === ':memory:' || ! is_file($path)) {
            throw new RuntimeException('Database SQLite fisik tidak tersedia untuk backup.');
        }

        $snapshot = $this->temporaryPath('sqlite-snapshot-');
        @unlink($snapshot);

        try {
            $quoted = str_replace("'", "''", str_replace('\\', '/', $snapshot));
            DB::connection($connection)->statement("VACUUM INTO '{$quoted}'");
            $contents = file_get_contents($snapshot);
            if (! is_string($contents)) {
                throw new RuntimeException('Snapshot SQLite tidak dapat dibaca.');
            }

            return ['name' => 'database/database.sqlite', 'contents' => $contents];
        } finally {
            if (is_file($snapshot)) {
                @unlink($snapshot);
            }
        }
    }

    private function mysqlSnapshot(array $config): array
    {
        $binary = trim((string) config('operations.mysqldump_binary', 'mysqldump'));
        $database = trim((string) ($config['database'] ?? ''));
        if ($binary === '' || $database === '') {
            throw new RuntimeException('Konfigurasi mysqldump/database belum lengkap.');
        }

        $command = [
            $binary,
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? '3306'),
            '--user='.(string) ($config['username'] ?? ''),
            '--result-file='.($snapshot = $this->temporaryPath('mysql-snapshot-')),
            $database,
        ];

        try {
            $process = new Process($command, null, ['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
            $process->setTimeout(600);
            $process->run();
            if (! $process->isSuccessful() || ! is_file($snapshot)) {
                throw new RuntimeException('mysqldump gagal. Periksa koneksi database dan binary pada operational log.');
            }
            $contents = file_get_contents($snapshot);
            if (! is_string($contents) || $contents === '') {
                throw new RuntimeException('Snapshot MySQL kosong atau tidak dapat dibaca.');
            }

            return ['name' => 'database/database.sql', 'contents' => $contents];
        } finally {
            if (is_file($snapshot)) {
                @unlink($snapshot);
            }
        }
    }

    private function addDiskDirectory(ZipArchive $zip, string $diskName, string $directory, string $prefix, string $password, array &$manifest): void
    {
        $disk = Storage::disk($diskName);
        foreach ($disk->allFiles($directory) as $path) {
            $contents = $disk->get($path);
            $relative = ltrim(substr($path, strlen($directory)), '/');
            $this->addEncrypted($zip, $prefix.'/'.$relative, $contents, $password, $manifest);
        }
    }

    private function addEncrypted(ZipArchive $zip, string $name, string $contents, string $password, array &$manifest, bool $track = true): void
    {
        if (! $zip->addFromString($name, $contents) || ! $zip->setEncryptionName($name, ZipArchive::EM_AES_256, $password)) {
            throw new RuntimeException('File gagal ditambahkan ke arsip terenkripsi.');
        }
        if ($track) {
            $manifest['files'][$name] = hash('sha256', $contents);
        }
    }

    private function prune($disk, string $directory): void
    {
        $cutoff = now()->subDays(max(7, (int) config('operations.backup_retention_days', 30)))->getTimestamp();
        foreach ($disk->files($directory) as $path) {
            if (str_ends_with($path, '.zip') && $disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
            }
        }
    }

    private function password(): string
    {
        $password = (string) config('operations.backup_password', '');
        if (strlen($password) < 16) {
            throw new RuntimeException('OPS_BACKUP_PASSWORD minimal 16 karakter wajib dikonfigurasi.');
        }

        return $password;
    }

    private function temporaryPath(string $prefix): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);
        if ($path === false) {
            throw new RuntimeException('Temporary file tidak dapat dibuat.');
        }

        return $path;
    }

    private function unsafeArchiveName(string $name): bool
    {
        return $name === ''
            || str_contains(str_replace('\\', '/', $name), '../')
            || str_starts_with($name, '/')
            || str_starts_with($name, '\\');
    }
}
