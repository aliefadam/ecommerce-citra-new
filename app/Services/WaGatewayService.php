<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WaGatewayService
{
    private string $baseUrl;

    private string $token;

    private int $timeout;

    private int $retryTimes;

    private int $retrySleep;

    public function __construct()
    {
        $config = config('services.wa_gateway', []);

        $this->baseUrl = rtrim((string) ($config['url'] ?? ''), '/');
        if ($this->baseUrl === 'https://wa.dokterkoding.my.id') {
            $this->baseUrl = 'https://wa-gateway.dokterkoding.my.id';
        }
        $this->token = (string) ($config['token'] ?? '');
        $this->timeout = max(1, (int) ($config['timeout'] ?? 10));
        $this->retryTimes = max(0, (int) ($config['retry_times'] ?? 1));
        $this->retrySleep = max(0, (int) ($config['retry_sleep'] ?? 200));
    }

    public function configured(): bool
    {
        return $this->baseUrl !== '' && $this->token !== '';
    }

    public function mode(): string
    {
        return strtolower((string) config('services.wa_gateway.mode', 'sandbox'));
    }

    public function prepareStore(string $name, string $storeId, array $limits): array
    {
        return $this->request('post', '/api/stores', [
            'name' => $name,
            'storeId' => $storeId,
            'limits' => [
                'perMinute' => (int) ($limits['perMinute'] ?? 10),
                'perDay' => (int) ($limits['perDay'] ?? 200),
                'perMonth' => (int) ($limits['perMonth'] ?? 3000),
            ],
        ], allowConflict: true);
    }

    public function connect(string $storeId, bool $reset = false): array
    {
        $path = "/api/stores/{$this->encodeStoreId($storeId)}/whatsapp/connect";
        if ($reset) {
            $path .= '?reset=true';
        }

        return $this->request('post', $path);
    }

    public function disconnect(string $storeId): array
    {
        return $this->request('post', "/api/stores/{$this->encodeStoreId($storeId)}/whatsapp/disconnect");
    }

    public function status(string $storeId): array
    {
        return $this->request('get', "/api/stores/{$this->encodeStoreId($storeId)}/whatsapp/status");
    }

    public function qr(string $storeId): array
    {
        return $this->request('get', "/api/stores/{$this->encodeStoreId($storeId)}/whatsapp/qr");
    }

    public function qrRaw(string $storeId): Response
    {
        $response = $this->client(retryable: true)
            ->accept('*/*')
            ->get($this->url("/api/stores/{$this->encodeStoreId($storeId)}/whatsapp/qr/raw"));

        if (! $response->successful()) {
            throw new RuntimeException($this->messageFromResponse($response), $response->status());
        }

        return $response;
    }

    public function usage(string $storeId): array
    {
        return $this->request('get', "/api/stores/{$this->encodeStoreId($storeId)}/usage");
    }

    private function request(string $method, string $path, array $payload = [], bool $allowConflict = false): array
    {
        $response = $this->client(retryable: strtolower($method) === 'get')->{$method}($this->url($path), $payload);

        if ($allowConflict && $response->status() === 409) {
            return [
                'success' => true,
                'message' => 'Toko sudah tersedia di WA Gateway.',
            ];
        }

        if (! $response->successful()) {
            throw new RuntimeException($this->messageFromResponse($response), $response->status());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Respon WA Gateway tidak valid.');
        }

        return $json;
    }

    private function client(bool $retryable = false): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('WA Gateway belum dikonfigurasi. Isi WA_GATEWAY_URL dan WA_GATEWAY_TOKEN di ENV.');
        }

        $request = Http::connectTimeout(min(5, $this->timeout))
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Internal-Token' => $this->token,
            ]);

        return $retryable && $this->retryTimes > 0
            ? $request->retry($this->retryTimes + 1, $this->retrySleep)
            : $request;
    }

    private function url(string $path): string
    {
        return $this->baseUrl.'/'.ltrim($path, '/');
    }

    private function encodeStoreId(string $storeId): string
    {
        return rawurlencode($storeId);
    }

    private function messageFromResponse(Response $response): string
    {
        $json = $response->json();
        if (is_array($json)) {
            $message = $json['message'] ?? $json['error'] ?? null;
            if (is_string($message) && $message !== '') {
                $message = trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $message) ?? '');

                return mb_substr($message, 0, 300);
            }
        }

        return 'WA Gateway gagal memproses permintaan. Kode: '.$response->status();
    }
}
