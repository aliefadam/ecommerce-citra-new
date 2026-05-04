<?php

namespace App\Http\Controllers;

use App\Models\StoreLocation;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Throwable;

class StoreLocationController extends Controller
{
    public function __construct(private readonly RajaOngkirService $rajaOngkir) {}

    public function edit()
    {
        $location = StoreLocation::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        return view('backend.store-locations.edit', compact('location'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'province_id' => ['required', 'integer', 'min:1'],
            'province_name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', 'min:1'],
            'city_name' => ['required', 'string', 'max:255'],
        ]);

        StoreLocation::query()->update(['is_active' => false]);

        StoreLocation::query()->create([
            'label' => $validated['label'] ?: 'Lokasi Toko Utama',
            'province_id' => (int) $validated['province_id'],
            'city_id' => (int) $validated['city_id'],
            'city_name' => (string) $validated['city_name'],
            'province_name' => (string) ($validated['province_name'] ?? ''),
            'is_active' => true,
        ]);

        return redirect()->route('store-locations.edit')->with('success', 'Store location berhasil diperbarui.');
    }

    public function provinces()
    {
        try {
            return response()->json(['data' => $this->rajaOngkir->provinces()]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }

    public function cities(Request $request)
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $items = $this->rajaOngkir->cities((int) $validated['province_id']);

            $data = collect($items)
                ->map(function ($item) {
                    $cityId = (int) ($item['city_id'] ?? $item['id'] ?? 0);
                    $cityName = (string) ($item['city_name'] ?? $item['name'] ?? '');
                    $provinceName = (string) ($item['province_name'] ?? $item['province'] ?? '');

                    return [
                        'origin_id' => 0,
                        'city_id' => $cityId,
                        'city_name' => $cityName,
                        'province_name' => $provinceName,
                        'label' => $cityName,
                    ];
                })
                ->filter(fn ($item) => $item['city_id'] > 0 && $item['city_name'] !== '')
                ->values()
                ->all();

            return response()->json(['data' => $data]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);
        }
    }
}
