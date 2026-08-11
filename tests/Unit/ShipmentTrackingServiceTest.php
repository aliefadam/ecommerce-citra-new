<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Services\RajaOngkirService;
use App\Services\ShipmentTrackingService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class ShipmentTrackingServiceTest extends TestCase
{
    public function test_it_resolves_courier_and_normalizes_tracking_timeline(): void
    {
        Cache::flush();
        $rajaOngkir = Mockery::mock(RajaOngkirService::class);
        $rajaOngkir->shouldReceive('trackWaybill')
            ->once()
            ->with('JNE123456', 'jne')
            ->andReturn([
                'delivered' => true,
                'summary' => [
                    'courier_code' => 'jne',
                    'courier_name' => 'Jalur Nugraha Ekakurir',
                    'waybill_number' => 'JNE123456',
                    'service_code' => 'REG',
                    'origin' => 'Surabaya',
                    'destination' => 'Jakarta',
                    'status' => 'DELIVERED',
                ],
                'delivery_status' => [
                    'status' => 'DELIVERED',
                    'pod_receiver' => 'Budi',
                    'pod_date' => '2026-08-11',
                    'pod_time' => '14:00',
                ],
                'manifest' => [
                    ['manifest_code' => '1', 'manifest_description' => 'Paket diterima agen', 'manifest_date' => '2026-08-10', 'manifest_time' => '09:00', 'city_name' => 'Surabaya'],
                    ['manifest_code' => '2', 'manifest_description' => 'Paket diterima tujuan', 'manifest_date' => '2026-08-11', 'manifest_time' => '14:00', 'city_name' => 'Jakarta'],
                ],
            ]);

        $transaction = new Transaction([
            'tracking_number' => 'JNE123456',
            'shipping_label' => 'JNE REG',
        ]);
        $service = new ShipmentTrackingService($rajaOngkir);

        $tracking = $service->forTransaction($transaction);
        $cachedTracking = $service->forTransaction($transaction);

        $this->assertTrue($tracking['available']);
        $this->assertTrue($tracking['delivered']);
        $this->assertSame('jne', $tracking['courier_code']);
        $this->assertSame('Paket diterima tujuan', $tracking['events'][0]['description']);
        $this->assertSame('Budi', $tracking['pod_receiver']);
        $this->assertSame($tracking, $cachedTracking);
    }

    public function test_it_falls_back_without_calling_api_for_unsupported_courier(): void
    {
        $rajaOngkir = Mockery::mock(RajaOngkirService::class);
        $rajaOngkir->shouldNotReceive('trackWaybill');
        $service = new ShipmentTrackingService($rajaOngkir);

        $tracking = $service->forTransaction(new Transaction([
            'tracking_number' => 'CUSTOM123',
            'shipping_label' => 'Kurir Toko',
        ]));

        $this->assertFalse($tracking['available']);
        $this->assertStringContainsString('belum tersedia', $tracking['message']);
    }
}
