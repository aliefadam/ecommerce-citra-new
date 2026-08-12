<?php

namespace Tests\Unit;

use App\Services\OperationalBackupService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationalBackupServiceTest extends TestCase
{
    public function test_backup_is_encrypted_and_restore_verification_checks_integrity(): void
    {
        Storage::fake('backup-test');
        Storage::fake('local');
        Storage::fake('public');
        $database = tempnam(sys_get_temp_dir(), 'ops-db-');
        $pdo = new \PDO('sqlite:'.$database);
        $pdo->exec('CREATE TABLE smoke (id INTEGER PRIMARY KEY, value TEXT)');
        $pdo->exec("INSERT INTO smoke (value) VALUES ('sqlite-test-content')");
        $pdo = null;

        config()->set('database.default', 'ops-test');
        config()->set('database.connections.ops-test', ['driver' => 'sqlite', 'database' => $database]);
        config()->set('operations.backup_disk', 'backup-test');
        config()->set('operations.backup_directory', 'backups');
        config()->set('operations.backup_password', 'very-strong-test-password');
        Storage::disk('local')->put('tax-invoices/1/invoice.pdf', 'private-invoice');
        Storage::disk('public')->put('payment-proofs/proof.webp', 'proof-image');

        try {
            $result = app(OperationalBackupService::class)->create();
            Storage::disk('backup-test')->assertExists($result['path']);
            $raw = Storage::disk('backup-test')->get($result['path']);
            $this->assertStringNotContainsString('private-invoice', $raw);
            $this->assertStringNotContainsString('proof-image', $raw);

            $verification = app(OperationalBackupService::class)->verify($result['path']);
            $this->assertSame('ok', $verification['integrity']);
            $this->assertSame(3, $verification['files']);
        } finally {
            @unlink($database);
        }
    }

    public function test_backup_requires_a_strong_password(): void
    {
        config()->set('operations.backup_password', 'short');

        $this->expectExceptionMessage('minimal 16 karakter');
        app(OperationalBackupService::class)->create();
    }
}
