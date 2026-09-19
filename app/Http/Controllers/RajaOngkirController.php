<?php

namespace App\Http\Controllers;

use App\Models\StoreLocation;
use App\Services\CheckoutPricingService;
use App\Services\RajaOngkirService;
use App\Services\ShippingQuoteService;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class RajaOngkirController extends Controller
{
    public function __construct(
        private readonly RajaOngkirService $rajaOngkir,
        private readonly CheckoutPricingService $checkoutPricing,
        private readonly ShippingQuoteService $shippingQuotes,
    ) {}

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
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'items' => ['required', 'json'],
        ]);

        try {
            $items = json_decode((string) $validated['items'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($items) || $items === []) {
                throw new RuntimeException('Item pengiriman tidak valid.');
            }

            $pricing = $this->checkoutPricing->resolve(
                $items,
                (int) $validated['company_id'],
                session('checkout.source') === 'redeem_point',
            );
            if (app()->environment('e2e')) {
                $data = [[
                    'code' => 'jne',
                    'name' => 'JNE',
                    'service' => 'REG',
                    'etd' => '1-2 hari',
                    'cost' => 12000,
                ]];
            } else {
                $storeLocation = StoreLocation::query()
                    ->where('company_id', (int) $validated['company_id'])
                    ->where('is_active', true)
                    ->latest('id')
                    ->first();
                $originId = (int) ($storeLocation?->city_id ?? 0);
                $couriers = (string) config('services.rajaongkir.couriers', 'jne:sicepat:jnt');
                if ($originId <= 0) {
                    throw new RuntimeException('Store location belum dikonfigurasi di admin.');
                }

                $data = $this->rajaOngkir->calculateDomesticCost(
                    $originId,
                    (int) $validated['destination_id'],
                    $pricing['weight_grams'],
                    $couriers
                );
            }

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

                    return ! str_contains($haystack, 'truck')
                        && ! str_contains($haystack, 'trucking')
                        && ! str_contains($haystack, 'cargo');
                })
                ->map(function (array $item) use ($validated, $pricing) {
                    $label = trim(strtoupper((string) ($item['name'] ?? $item['code'] ?? '')).' '.(string) ($item['service'] ?? ''));
                    $item['quote_token'] = $this->shippingQuotes->issue(
                        (int) $validated['company_id'],
                        (int) $validated['destination_id'],
                        $pricing['fingerprint'],
                        $pricing['weight_grams'],
                        (int) ($item['cost'] ?? 0),
                        $label,
                    );

                    return $item;
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
