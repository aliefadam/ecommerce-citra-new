<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShipmentTrackingService
{
    public function __construct(private readonly RajaOngkirService $rajaOngkir) {}

    public function forTransaction(Transaction $transaction): ?array
    {
        $awb = trim((string) $transaction->tracking_number);
        if ($awb === '') {
            return null;
        }

        $courierCode = $this->resolveCourierCode(
            (string) ($transaction->shipping_courier_name ?: $transaction->shipping_label)
        );

        if ($courierCode === null) {
            return [
                'available' => false,
                'awb' => $awb,
                'courier_code' => null,
                'courier_name' => (string) ($transaction->shipping_courier_name ?: $transaction->shipping_label ?: 'Ekspedisi'),
                'message' => 'Tracking otomatis belum tersedia untuk ekspedisi ini.',
                'events' => [],
            ];
        }

        try {
            $minutes = max(1, (int) config('services.rajaongkir.tracking_cache_minutes', 15));
            $cacheKey = 'rajaongkir:waybill:'.hash('sha256', $courierCode.'|'.$awb);
            $data = Cache::remember(
                $cacheKey,
                now()->addMinutes($minutes),
                fn () => $this->rajaOngkir->trackWaybill($awb, $courierCode)
            );

            return $this->normalize($data, $awb, $courierCode);
        } catch (Throwable $exception) {
            Log::warning('RajaOngkir waybill tracking failed.', [
                'courier' => $courierCode,
                'awb_hash' => hash('sha256', $awb),
                'error' => $exception->getMessage(),
            ]);

            return [
                'available' => false,
                'awb' => $awb,
                'courier_code' => $courierCode,
                'courier_name' => strtoupper($courierCode),
                'message' => 'Status dari ekspedisi belum dapat dimuat. Nomor resi tetap bisa digunakan di situs ekspedisi.',
                'events' => [],
            ];
        }
    }

    public function resolveCourierCode(string $label): ?string
    {
        $normalized = strtolower(trim($label));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?: '';

        $couriers = [
            'sicepat' => ['sicepat', 'si cepat'],
            'anteraja' => ['anteraja', 'anter aja'],
            'jnt' => ['j t', 'jnt'],
            'jne' => ['jne'],
            'tiki' => ['tiki'],
            'sap' => ['sap express', 'sap'],
            'ninja' => ['ninja express', 'ninja'],
            'wahana' => ['wahana'],
            'pov' => ['pos indonesia', 'pos'],
            'lion' => ['lion parcel', 'lion'],
            'first' => ['first logistics', 'first'],
        ];

        foreach ($couriers as $code => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/(^| )'.preg_quote($alias, '/').'($| )/', $normalized) === 1) {
                    return $code;
                }
            }
        }

        return null;
    }

    private function normalize(array $data, string $awb, string $courierCode): array
    {
        $summary = is_array($data['summary'] ?? null) ? $data['summary'] : [];
        $delivery = is_array($data['delivery_status'] ?? null) ? $data['delivery_status'] : [];
        $manifest = is_array($data['manifest'] ?? null) ? $data['manifest'] : [];

        $events = collect($manifest)
            ->filter(fn ($event) => is_array($event))
            ->map(fn (array $event) => [
                'code' => (string) ($event['manifest_code'] ?? ''),
                'description' => (string) ($event['manifest_description'] ?? 'Pembaruan pengiriman'),
                'date' => (string) ($event['manifest_date'] ?? ''),
                'time' => (string) ($event['manifest_time'] ?? ''),
                'city' => (string) ($event['city_name'] ?? ''),
            ])
            ->sortByDesc(fn (array $event) => trim($event['date'].' '.$event['time']))
            ->values()
            ->all();

        return [
            'available' => true,
            'awb' => (string) ($summary['waybill_number'] ?? $awb),
            'courier_code' => (string) ($summary['courier_code'] ?? $courierCode),
            'courier_name' => (string) ($summary['courier_name'] ?? strtoupper($courierCode)),
            'service' => (string) ($summary['service_code'] ?? ''),
            'status' => (string) ($delivery['status'] ?? $summary['status'] ?? ''),
            'delivered' => (bool) ($data['delivered'] ?? false),
            'origin' => (string) ($summary['origin'] ?? ''),
            'destination' => (string) ($summary['destination'] ?? ''),
            'receiver_name' => (string) ($summary['receiver_name'] ?? ''),
            'pod_receiver' => (string) ($delivery['pod_receiver'] ?? ''),
            'pod_date' => (string) ($delivery['pod_date'] ?? ''),
            'pod_time' => (string) ($delivery['pod_time'] ?? ''),
            'message' => null,
            'events' => $events,
        ];
    }
}
