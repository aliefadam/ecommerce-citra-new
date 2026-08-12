<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransApiService
{
    public function configured(): bool
    {
        return trim((string) config('services.midtrans.server_key', '')) !== '';
    }

    public function mode(): string
    {
        $mode = strtolower(trim((string) config('services.midtrans.mode', 'sandbox')));

        return in_array($mode, ['fake', 'sandbox', 'production'], true) ? $mode : 'invalid';
    }

    public function status(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            throw new RuntimeException('Order ID Midtrans wajib diisi.');
        }

        $response = $this->client(safeToRetry: true)
            ->get($this->baseUrl().'/v2/'.rawurlencode($orderId).'/status');

        if (! $response->successful()) {
            throw new RuntimeException('Midtrans status check gagal. HTTP '.$response->status().'.');
        }

        $json = $response->json();
        if (! is_array($json) || ! is_string($json['transaction_status'] ?? null)) {
            throw new RuntimeException('Respon status Midtrans tidak valid.');
        }

        return $json;
    }

    private function baseUrl(): string
    {
        return $this->mode() === 'production'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    private function client(bool $safeToRetry = false): PendingRequest
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $connectTimeout = max(1, (int) config('services.midtrans.connect_timeout', 5));
        $timeout = max($connectTimeout, (int) config('services.midtrans.timeout', 30));
        $request = Http::connectTimeout($connectTimeout)
            ->timeout($timeout)
            ->acceptJson()
            ->withBasicAuth($serverKey, '');
        $retryTimes = max(0, (int) config('services.midtrans.retry_times', 2));

        return $safeToRetry && $retryTimes > 0
            ? $request->retry($retryTimes + 1, max(0, (int) config('services.midtrans.retry_sleep', 250)))
            : $request;
    }
}
