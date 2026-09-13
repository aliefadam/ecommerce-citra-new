<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResetProductionData extends Command
{
    protected $signature = 'ops:reset-production-data
        {--force : Wajib digunakan ketika APP_ENV=production atau saat non-interaktif}
        {--without-admin : Jangan membuat akun super admin awal}
        {--keep-uploads : Jangan menghapus file unggahan milik data lama}';

    protected $description = 'Menghapus seluruh data aplikasi, menjalankan ulang migration, dan menyiapkan admin awal';

    /** @var array<string, list<string>> */
    private const UPLOAD_DIRECTORIES = [
        'public' => [
            'avatars',
            'banners',
            'companies',
            'content',
            'main-categories',
            'newsletter',
            'payment-proofs',
            'product-variants',
            'promo',
            'return-requests',
            'reviews',
            'store',
        ],
        'local' => [
            'payment-proofs',
            'tax-invoices',
        ],
    ];

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            $this->error('APP_ENV=production terdeteksi. Jalankan ulang dengan --force jika reset memang disengaja.');

            return self::FAILURE;
        }

        if (! $this->input->isInteractive() && ! $this->option('force')) {
            $this->error('Mode non-interaktif wajib menggunakan --force.');

            return self::FAILURE;
        }

        $admin = $this->collectInitialAdmin();
        if ($admin === false) {
            return self::FAILURE;
        }

        $this->warn('PERINGATAN: seluruh tabel dan data akan dihapus permanen.');
        $this->line('Termasuk produk, pengguna, transaksi, stok, promo, antrean, session, dan cache database.');

        if (! $this->confirmDestructiveReset()) {
            $this->components->info('Reset dibatalkan. Tidak ada data yang diubah.');

            return self::FAILURE;
        }

        $migrationExitCode = $this->call('migrate:fresh', ['--force' => true]);
        if ($migrationExitCode !== self::SUCCESS) {
            $this->error('Migration gagal. Reset tidak dapat diselesaikan.');

            return self::FAILURE;
        }

        if (! $this->option('keep-uploads') && ! $this->deleteUploads()) {
            $this->error('Database berhasil di-reset, tetapi satu atau lebih direktori unggahan gagal dibersihkan.');

            return self::FAILURE;
        }

        if (is_array($admin)) {
            User::query()->create([
                'name' => $admin['name'],
                'first_name' => $admin['name'],
                'email' => $admin['email'],
                'username' => $this->adminUsername($admin['email']),
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => $admin['password'],
            ]);
        }

        $this->newLine();
        $this->components->info('Reset data production selesai. Database tidak diisi dengan data demo.');
        $this->line(is_array($admin)
            ? "Super admin awal dibuat: {$admin['email']}"
            : 'Tidak ada akun pengguna yang dibuat.');
        $this->line($this->option('keep-uploads')
            ? 'File unggahan lama dipertahankan (--keep-uploads).'
            : 'File unggahan lama telah dibersihkan; backup operasional tetap dipertahankan.');

        return self::SUCCESS;
    }

    /**
     * @return array{name: string, email: string, password: string}|false|null
     */
    private function collectInitialAdmin(): array|false|null
    {
        if ($this->option('without-admin')) {
            return null;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Mode non-interaktif harus memakai --without-admin karena kredensial admin wajib dimasukkan secara tersembunyi.');

            return false;
        }

        if (! $this->confirm('Buat akun super admin awal setelah reset?', true)) {
            return null;
        }

        $name = trim((string) $this->ask('Nama admin', 'Administrator'));
        if ($name === '' || mb_strlen($name) > 255) {
            $this->error('Nama admin wajib diisi dan maksimal 255 karakter.');

            return false;
        }

        $email = trim((string) $this->ask('Email admin'));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 255) {
            $this->error('Email admin tidak valid.');

            return false;
        }

        $password = (string) $this->secret('Password admin (minimal 12 karakter)');
        $passwordConfirmation = (string) $this->secret('Ulangi password admin');

        if (mb_strlen($password) < 12) {
            $this->error('Password admin minimal 12 karakter.');

            return false;
        }

        if (! hash_equals($password, $passwordConfirmation)) {
            $this->error('Konfirmasi password admin tidak cocok.');

            return false;
        }

        return compact('name', 'email', 'password');
    }

    private function confirmDestructiveReset(): bool
    {
        if (! $this->input->isInteractive()) {
            return (bool) $this->option('force');
        }

        return hash_equals('RESET DATA', (string) $this->ask('Ketik RESET DATA untuk melanjutkan'));
    }

    private function deleteUploads(): bool
    {
        $success = true;

        foreach (self::UPLOAD_DIRECTORIES as $disk => $directories) {
            foreach ($directories as $directory) {
                if (Storage::disk($disk)->exists($directory)) {
                    $success = Storage::disk($disk)->deleteDirectory($directory) && $success;
                }
            }
        }

        return $success;
    }

    private function adminUsername(string $email): string
    {
        $username = Str::slug(Str::before($email, '@'), '.');

        return Str::limit($username !== '' ? $username : 'admin', 255, '');
    }
}
