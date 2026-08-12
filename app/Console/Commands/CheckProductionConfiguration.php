<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProductionConfiguration extends Command
{
    protected $signature = 'ops:production-check';

    protected $description = 'Memblokir release ketika konfigurasi keamanan production belum aman';

    public function handle(): int
    {
        $checks = [
            ['APP_ENV', app()->environment('production'), 'harus production'],
            ['APP_DEBUG', ! config('app.debug'), 'harus false'],
            ['APP_URL', str_starts_with((string) config('app.url'), 'https://'), 'harus HTTPS'],
            ['SESSION_SECURE_COOKIE', config('session.secure') === true, 'harus true'],
            ['OPS_READINESS_TOKEN', strlen((string) config('operations.readiness_token')) >= 24, 'minimal 24 karakter'],
            ['OPS_BACKUP_PASSWORD', strlen((string) config('operations.backup_password')) >= 16, 'minimal 16 karakter'],
            ['OPS_BACKUP_DISK', (string) config('operations.backup_disk') !== 'local', 'harus off-server'],
            ['QUEUE_CONNECTION', ! in_array((string) config('queue.default'), ['sync', 'null'], true), 'harus async'],
            ['MAIL_MAILER', ! in_array((string) config('mail.default'), ['log', 'array'], true), 'harus delivery provider'],
            ['MIDTRANS_MODE', (string) config('services.midtrans.mode') === 'production', 'harus production'],
            ['RAJAONGKIR_MODE', (string) config('services.rajaongkir.mode') === 'production', 'harus production'],
            ['WA_GATEWAY_MODE', in_array((string) config('services.wa_gateway.mode'), ['production', 'fake'], true), 'production atau fake bila bukan release scope'],
            ['RELEASE_FEATURE_FREEZE', (bool) config('release.feature_freeze'), 'harus true'],
        ];

        $this->table(['Check', 'Status', 'Requirement'], array_map(
            fn (array $check) => [$check[0], $check[1] ? 'PASS' : 'FAIL', $check[2]],
            $checks
        ));

        return collect($checks)->contains(fn (array $check) => ! $check[1])
            ? self::FAILURE
            : self::SUCCESS;
    }
}
