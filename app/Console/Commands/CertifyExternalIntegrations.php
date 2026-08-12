<?php

namespace App\Console\Commands;

use App\Mail\IntegrationSmokeMail;
use App\Models\Address;
use App\Models\StoreLocation;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Services\MidtransApiService;
use App\Services\RajaOngkirService;
use App\Services\WaGatewayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CertifyExternalIntegrations extends Command
{
    protected $signature = 'integrations:certify
        {provider=all : all|midtrans|rajaongkir|email|whatsapp}
        {--execute : Jalankan smoke call aman; tanpa opsi ini hanya preflight}
        {--order-id= : Order ID Midtrans sandbox/approved production yang akan direkonsiliasi}
        {--origin= : RajaOngkir origin destination ID}
        {--destination= : RajaOngkir destination ID}
        {--weight=1000 : Berat smoke cost dalam gram}
        {--awb= : AWB valid untuk smoke tracking RajaOngkir}
        {--courier=jne : Kode kurir AWB}
        {--email= : Penerima internal yang ada di allowlist}
        {--wa-store= : Store ID WA Gateway; default dari store settings}';

    protected $description = 'Preflight dan controlled smoke test integrasi eksternal tanpa menampilkan credential';

    public function handle(
        MidtransApiService $midtrans,
        RajaOngkirService $rajaOngkir,
        WaGatewayService $whatsapp,
    ): int {
        $provider = strtolower((string) $this->argument('provider'));
        $providers = $provider === 'all'
            ? ['midtrans', 'rajaongkir', 'email', 'whatsapp']
            : [$provider];

        if (array_diff($providers, ['midtrans', 'rajaongkir', 'email', 'whatsapp'])) {
            $this->error('Provider tidak valid. Gunakan all, midtrans, rajaongkir, email, atau whatsapp.');

            return self::INVALID;
        }

        $certificationId = 'CERT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        $execute = (bool) $this->option('execute');
        $results = [];

        foreach ($providers as $name) {
            try {
                $results[] = $this->certify($name, $execute, $certificationId, $midtrans, $rajaOngkir, $whatsapp);
            } catch (Throwable $exception) {
                $results[] = [
                    'provider' => $name,
                    'mode' => $this->providerMode($name, $midtrans, $rajaOngkir, $whatsapp),
                    'status' => 'FAIL',
                    'detail' => $this->safeMessage($exception),
                ];
            }
        }

        $this->newLine();
        $this->info('Integration certification '.$certificationId.' ('.($execute ? 'controlled smoke' : 'preflight').')');
        $this->table(['Provider', 'Mode', 'Status', 'Detail'], array_map(fn (array $result) => [
            $result['provider'],
            $result['mode'],
            $result['status'],
            $result['detail'],
        ], $results));

        return collect($results)->contains(fn (array $result) => $result['status'] === 'FAIL')
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function certify(
        string $provider,
        bool $execute,
        string $certificationId,
        MidtransApiService $midtrans,
        RajaOngkirService $rajaOngkir,
        WaGatewayService $whatsapp,
    ): array {
        $mode = $this->providerMode($provider, $midtrans, $rajaOngkir, $whatsapp);
        $this->guardMode($provider, $mode, $execute);

        if (! $execute) {
            return $this->preflight($provider, $mode, $midtrans, $rajaOngkir, $whatsapp);
        }

        return match ($provider) {
            'midtrans' => $this->smokeMidtrans($mode, $midtrans),
            'rajaongkir' => $this->smokeRajaOngkir($mode, $rajaOngkir),
            'email' => $this->smokeEmail($mode, $certificationId),
            'whatsapp' => $this->smokeWhatsapp($mode, $whatsapp),
        };
    }

    private function preflight(
        string $provider,
        string $mode,
        MidtransApiService $midtrans,
        RajaOngkirService $rajaOngkir,
        WaGatewayService $whatsapp,
    ): array {
        $configured = match ($provider) {
            'midtrans' => $midtrans->configured(),
            'rajaongkir' => $rajaOngkir->configured(),
            'email' => ! in_array($mode, ['', 'array', 'log'], true),
            'whatsapp' => $whatsapp->configured(),
        };

        return [
            'provider' => $provider,
            'mode' => $mode,
            'status' => $configured ? 'PASS' : 'FAIL',
            'detail' => $configured ? 'Konfigurasi tersedia; credential dimask.' : 'Konfigurasi belum lengkap atau masih fake/log.',
        ];
    }

    private function smokeMidtrans(string $mode, MidtransApiService $midtrans): array
    {
        $orderId = trim((string) $this->option('order-id'));
        if ($orderId === '') {
            $orderId = (string) Transaction::query()
                ->whereNotNull('midtrans_transaction_id')
                ->where('payment_type', '!=', 'manual_transfer')
                ->latest('id')
                ->value('order_id');
        }
        if ($orderId === '') {
            throw new RuntimeException('Tidak ada order Midtrans untuk direkonsiliasi. Buat satu transaksi sandbox lalu ulangi dengan --order-id.');
        }

        $status = $midtrans->status($orderId);
        $providerOrderId = (string) ($status['order_id'] ?? $orderId);
        if (! hash_equals($orderId, $providerOrderId)) {
            throw new RuntimeException('Order ID pada respon Midtrans tidak cocok.');
        }

        return [
            'provider' => 'midtrans',
            'mode' => $mode,
            'status' => 'PASS',
            'detail' => 'Status provider terbaca: '.strtolower((string) $status['transaction_status']).'; order ID cocok.',
        ];
    }

    private function smokeRajaOngkir(string $mode, RajaOngkirService $rajaOngkir): array
    {
        $origin = (int) ($this->option('origin') ?: StoreLocation::query()->where('is_active', true)->latest('id')->value('city_id'));
        $destination = (int) ($this->option('destination') ?: Address::query()->whereNotNull('destination_id')->latest('is_primary')->value('destination_id'));
        $weight = max(1, (int) $this->option('weight'));
        if ($origin <= 0 || $destination <= 0) {
            throw new RuntimeException('Origin/destination smoke RajaOngkir belum tersedia. Isi --origin dan --destination.');
        }

        $costs = $rajaOngkir->calculateDomesticCost(
            $origin,
            $destination,
            $weight,
            (string) config('services.rajaongkir.couriers', 'jne:sicepat:jnt'),
        );
        if ($costs === []) {
            throw new RuntimeException('RajaOngkir mengembalikan opsi ongkir kosong.');
        }

        $detail = count($costs).' opsi ongkir diterima.';
        $certificationStatus = 'PASS';
        $awb = trim((string) $this->option('awb'));
        if ($awb !== '') {
            $tracking = $rajaOngkir->trackWaybill($awb, (string) $this->option('courier'));
            $detail .= ' Tracking AWB valid dengan '.count((array) ($tracking['manifest'] ?? [])).' event.';
        } else {
            $detail .= ' Tracking dilewati; berikan --awb untuk sertifikasi tracking.';
            $certificationStatus = 'PARTIAL';
        }

        return ['provider' => 'rajaongkir', 'mode' => $mode, 'status' => $certificationStatus, 'detail' => $detail];
    }

    private function smokeEmail(string $mode, string $certificationId): array
    {
        $recipient = strtolower(trim((string) $this->option('email')));
        $allowlist = array_map('strtolower', (array) config('services.integration_certification.email_allowlist', []));
        if ($recipient === '' || ! in_array($recipient, $allowlist, true)) {
            throw new RuntimeException('Penerima wajib ada di INTEGRATION_SMOKE_EMAIL_ALLOWLIST.');
        }

        Mail::to($recipient)->send(new IntegrationSmokeMail($certificationId));

        return [
            'provider' => 'email',
            'mode' => $mode,
            'status' => 'PASS',
            'detail' => 'Smoke email dikirim ke '.$this->maskEmail($recipient).'.',
        ];
    }

    private function smokeWhatsapp(string $mode, WaGatewayService $whatsapp): array
    {
        $storeId = trim((string) $this->option('wa-store'));
        if ($storeId === '') {
            $storeId = (string) (StoreSetting::values()['wa_gateway_store_id'] ?? 'boq-ecommerce');
        }

        $lastException = null;
        foreach ($this->whatsappStoreCandidates($storeId) as $candidate) {
            try {
                $status = $whatsapp->status($candidate);
                if ((bool) ($status['connected'] ?? false)) {
                    return [
                        'provider' => 'whatsapp',
                        'mode' => $mode,
                        'status' => 'PARTIAL',
                        'detail' => 'Gateway reachable dan sesi connected. Smoke-send belum tersedia pada kontrak aplikasi saat ini.',
                    ];
                }
            } catch (Throwable $exception) {
                $lastException = $exception;
            }
        }

        throw new RuntimeException(
            $lastException
                ? 'WA Gateway belum dapat mengonfirmasi sesi connected: '.$this->safeMessage($lastException)
                : 'WA Gateway merespons tetapi sesi belum connected.'
        );
    }

    private function providerMode(
        string $provider,
        MidtransApiService $midtrans,
        RajaOngkirService $rajaOngkir,
        WaGatewayService $whatsapp,
    ): string {
        return match ($provider) {
            'midtrans' => $midtrans->mode(),
            'rajaongkir' => $rajaOngkir->mode(),
            'email' => strtolower((string) config('mail.default', 'log')),
            'whatsapp' => $whatsapp->mode(),
        };
    }

    private function guardMode(string $provider, string $mode, bool $execute): void
    {
        if (in_array($provider, ['midtrans', 'rajaongkir', 'whatsapp'], true)
            && ! in_array($mode, ['fake', 'sandbox', 'production'], true)) {
            throw new RuntimeException('Mode integrasi tidak valid. Gunakan fake, sandbox, atau production.');
        }

        if ($execute && $mode === 'fake') {
            throw new RuntimeException('Mode fake tidak boleh melakukan network smoke test.');
        }

        if ($execute && $mode === 'production' && ! (bool) config('services.integration_certification.allow_production', false)) {
            throw new RuntimeException('Production smoke diblokir. Persetujuan eksplisit belum dikonfigurasi.');
        }
    }

    private function safeMessage(Throwable $exception): string
    {
        $message = trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $exception->getMessage()) ?? '');

        $secrets = array_filter([
            config('services.midtrans.server_key'),
            config('services.midtrans.client_key'),
            config('services.rajaongkir.api_key'),
            config('services.wa_gateway.token'),
            config('mail.mailers.smtp.password'),
        ], fn (mixed $value) => is_string($value) && $value !== '');

        foreach ($secrets as $secret) {
            $message = str_replace((string) $secret, '[REDACTED]', $message);
        }

        $message = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[EMAIL_REDACTED]', $message) ?? $message;

        return mb_substr($message !== '' ? $message : 'Integrasi gagal tanpa detail.', 0, 400);
    }

    /** @return list<string> */
    private function whatsappStoreCandidates(string $storeId): array
    {
        $base = preg_replace('/^(?:session-)?store-/i', '', trim($storeId)) ?: trim($storeId);

        return array_values(array_unique(array_filter([
            $storeId,
            'store-'.$base,
            'session-store-'.$base,
        ])));
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 2).'***@'.$domain;
    }
}
