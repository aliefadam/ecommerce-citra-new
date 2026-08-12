<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirService
{
    private string $baseUrl;

    private string $apiKey;

    private int $connectTimeout;

    private int $timeout;

    private int $retryTimes;

    private int $retrySleep;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.rajaongkir.base_url'), '/');
        $this->apiKey = (string) config('services.rajaongkir.api_key', '');
        $this->connectTimeout = max(1, (int) config('services.rajaongkir.connect_timeout', 5));
        $this->timeout = max($this->connectTimeout, (int) config('services.rajaongkir.timeout', 20));
        $this->retryTimes = max(0, (int) config('services.rajaongkir.retry_times', 2));
        $this->retrySleep = max(0, (int) config('services.rajaongkir.retry_sleep', 250));
    }

    public function configured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    public function mode(): string
    {
        return strtolower((string) config('services.rajaongkir.mode', 'sandbox'));
    }

    public function provinces(): array
    {
        return $this->get('/destination/province');
    }

    public function cities(int $provinceId): array
    {
        return $this->get('/destination/city/'.$provinceId);
    }

    public function districts(int $cityId): array
    {
        return $this->get('/destination/district/'.$cityId);
    }

    public function subdistricts(int $districtId): array
    {
        return $this->get('/destination/sub-district/'.$districtId);
    }

    public function domesticDestination(string $query): array
    {
        return $this->get('/destination/domestic-destination', [
            'search' => $query,
            'limit' => 10,
        ]);
    }

    public function calculateDomesticCost(int $originId, int $destinationId, int $weightGrams, string $couriers): array
    {
        return $this->post('/calculate/domestic-cost', [
            'origin' => $originId,
            'destination' => $destinationId,
            'weight' => max(1, $weightGrams),
            'courier' => $couriers,
        ]);
    }

    public function trackWaybill(string $awb, string $courier): array
    {
        $query = http_build_query([
            'awb' => trim($awb),
            'courier' => strtolower(trim($courier)),
        ]);

        $response = $this->client()
            ->post($this->baseUrl.'/track/waybill?'.$query);

        return $this->parse($response->status(), $response->json());
    }

    private function get(string $path, array $query = []): array
    {
        $response = $this->client()
            ->get($this->baseUrl.$path, $query);

        return $this->parse($response->status(), $response->json());
    }

    private function post(string $path, array $payload): array
    {
        $response = $this->client()
            ->asForm()
            ->post($this->baseUrl.$path, $payload);

        return $this->parse($response->status(), $response->json());
    }

    private function headers(): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('RAJAONGKIR API key belum dikonfigurasi.');
        }

        return [
            'key' => $this->apiKey,
        ];
    }

    private function client(): PendingRequest
    {
        $request = Http::connectTimeout($this->connectTimeout)
            ->timeout($this->timeout)
            ->withHeaders($this->headers())
            ->acceptJson();

        return $this->retryTimes > 0
            ? $request->retry($this->retryTimes + 1, $this->retrySleep)
            : $request;
    }

    private function parse(int $status, mixed $json): array
    {
        if (! is_array($json)) {
            throw new RuntimeException('Respon RajaOngkir tidak valid.');
        }

        $meta = $json['meta'] ?? [];
        $ok = $status >= 200 && $status < 300 && (($meta['status'] ?? '') === 'success' || ($meta['code'] ?? 0) === 200);
        if (! $ok) {
            $message = $this->safeProviderMessage($meta['message'] ?? null, 'Gagal memproses RajaOngkir');
            throw new RuntimeException($message);
        }

        $data = $json['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    private function safeProviderMessage(mixed $message, string $fallback): string
    {
        $message = is_string($message) ? trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $message) ?? '') : '';

        return $message !== '' ? mb_substr($message, 0, 300) : $fallback;
    }
}
