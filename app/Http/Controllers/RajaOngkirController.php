<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Throwable;
use RuntimeException;

class RajaOngkirController extends Controller
{
    public function __construct(private readonly RajaOngkirService $rajaOngkir) {}

    public function provinces()
    {
        try {
            return response()->json([
                'data' => $this->rajaOngkir->provinces(),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }

    public function cities(Request $request)
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer'],
        ]);

        try {
            return response()->json([
                'data' => $this->rajaOngkir->cities((int) $validated['province_id']),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }

    public function districts(Request $request)
    {
        $validated = $request->validate([
            'city_id' => ['required', 'integer'],
        ]);

        try {
            return response()->json([
                'data' => $this->rajaOngkir->districts((int) $validated['city_id']),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }

    public function subdistricts(Request $request)
    {
        $validated = $request->validate([
            'district_id' => ['required', 'integer'],
        ]);

        try {
            return response()->json([
                'data' => $this->rajaOngkir->subdistricts((int) $validated['district_id']),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }

    public function shippingOptions(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => ['required', 'integer'],
            'weight' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $storeLocation = StoreLocation::query()
                ->where('is_active', true)
                ->latest('id')
                ->first();
            $originId = (int) ($storeLocation?->city_id ?? 0);
            $couriers = (string) env('RAJAONGKIR_COURIERS', 'jne:sicepat:jnt');
            if ($originId <= 0) {
                throw new RuntimeException('Store location belum dikonfigurasi di admin.');
            }

            $data = $this->rajaOngkir->calculateDomesticCost(
                $originId,
                (int) $validated['destination_id'],
                (int) $validated['weight'],
                $couriers
            );

            $data = collect($data)
                ->filter(function ($item) {
                    $haystack = strtolower(trim(
                        implode(' ', [
                            (string) ($item['name'] ?? ''),
                            (string) ($item['code'] ?? ''),
                            (string) ($item['service'] ?? ''),
                            (string) ($item['description'] ?? ''),
                        ])
                    ));

                    if ($haystack === '') {
                        return true;
                    }

                    return !str_contains($haystack, 'truck')
                        && !str_contains($haystack, 'trucking')
                        && !str_contains($haystack, 'cargo');
                })
                ->values()
                ->all();

            return response()->json([
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }
}
