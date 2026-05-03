<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $this->apiKey = (string) (env('RAJAONGKIR_API_KEY') ?: env('API_KEY_RAJAONGKIR', ''));
    }

    public function provinces(): array
    {
        return $this->get('/destination/province');
    }

    public function cities(int $provinceId): array
    {
        return $this->get('/destination/city/' . $provinceId);
    }

    public function districts(int $cityId): array
    {
        return $this->get('/destination/district/' . $cityId);
    }

    public function subdistricts(int $districtId): array
    {
        return $this->get('/destination/sub-district/' . $districtId);
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

    private function get(string $path, array $query = []): array
    {
        $response = Http::timeout(20)
            ->withHeaders($this->headers())
            ->acceptJson()
            ->get($this->baseUrl . $path, $query);

        return $this->parse($response->status(), $response->json());
    }

    private function post(string $path, array $payload): array
    {
        $response = Http::timeout(20)
            ->withHeaders($this->headers())
            ->asForm()
            ->post($this->baseUrl . $path, $payload);

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

    private function parse(int $status, mixed $json): array
    {
        if (!is_array($json)) {
            throw new RuntimeException('Respon RajaOngkir tidak valid.');
        }

        $meta = $json['meta'] ?? [];
        $ok = $status >= 200 && $status < 300 && (($meta['status'] ?? '') === 'success' || ($meta['code'] ?? 0) === 200);
        if (!$ok) {
            $message = (string) ($meta['message'] ?? 'Gagal memproses RajaOngkir');
            throw new RuntimeException($message);
        }

        $data = $json['data'] ?? [];
        return is_array($data) ? $data : [];
    }
}

