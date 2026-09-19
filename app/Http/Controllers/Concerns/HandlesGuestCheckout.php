<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait HandlesGuestCheckout
{
    protected function guestCheckoutRules(Request $request): array
    {
        $requiredForGuest = Rule::requiredIf($request->user() === null);

        return [
            'guest_name' => [$requiredForGuest, 'nullable', 'string', 'max:255'],
            'guest_email' => [$requiredForGuest, 'nullable', 'email', 'max:255'],
            'guest_phone' => [$requiredForGuest, 'nullable', 'string', 'regex:/^[0-9+().\s-]{8,20}$/'],
            'shipping_address_line' => [$requiredForGuest, 'nullable', 'string', 'max:2000'],
            'shipping_city' => [$requiredForGuest, 'nullable', 'string', 'max:255'],
            'shipping_district' => ['nullable', 'string', 'max:255'],
            'shipping_province' => [$requiredForGuest, 'nullable', 'string', 'max:255'],
            'shipping_postal_code' => [$requiredForGuest, 'nullable', 'string', 'max:20'],
            'shipping_destination_id' => [$requiredForGuest, 'nullable', 'integer', 'min:1'],
        ];
    }

    protected function guestCheckoutData(Request $request, array $validated): ?array
    {
        if ($request->user()) {
            return null;
        }

        if (collect($validated['items'] ?? [])->contains(fn ($item) => (int) ($item['redeemPoints'] ?? 0) > 0)) {
            throw new HttpResponseException(response()->json([
                'message' => 'Redeem poin hanya tersedia untuk member yang sudah login.',
            ], 422));
        }

        $email = strtolower(trim((string) $validated['guest_email']));
        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $message = 'Email ini sudah terdaftar. Silakan login untuk melanjutkan.';
            $loginUrl = route('login', ['redirect' => route('frontend.checkout')]);

            if ($request->expectsJson()) {
                throw new HttpResponseException(response()->json([
                    'message' => $message,
                    'requires_login' => true,
                    'login_url' => $loginUrl,
                ], 409));
            }

            throw new HttpResponseException(redirect()->guest($loginUrl)->with('error', $message));
        }

        return [
            'name' => trim((string) $validated['guest_name']),
            'email' => $email,
            'phone' => trim((string) $validated['guest_phone']),
            'address_snapshot' => [
                'shipping_recipient_name' => trim((string) $validated['guest_name']),
                'shipping_phone' => trim((string) $validated['guest_phone']),
                'shipping_address_line' => trim((string) $validated['shipping_address_line']),
                'shipping_city' => trim((string) $validated['shipping_city']),
                'shipping_district' => trim((string) ($validated['shipping_district'] ?? '')),
                'shipping_province' => trim((string) $validated['shipping_province']),
                'shipping_postal_code' => trim((string) $validated['shipping_postal_code']),
            ],
        ];
    }

    protected function checkoutAddressSnapshot(Request $request, array $validated, ?array $guest): array
    {
        if ($guest) {
            return $guest['address_snapshot'];
        }

        $shippingLabel = strtolower(trim((string) ($validated['shipping_label'] ?? '')));
        if (
            str_contains($shippingLabel, 'ambil sendiri')
            || str_contains($shippingLabel, 'pickup')
            || str_contains($shippingLabel, 'pick up')
        ) {
            return [];
        }

        $addressId = (int) ($validated['address_id'] ?? 0);
        if ($addressId < 1) {
            throw ValidationException::withMessages([
                'address_id' => 'Pilih alamat pengiriman sebelum melanjutkan pembayaran.',
            ]);
        }

        $address = Address::query()
            ->whereKey($addressId)
            ->where('user_id', $request->user()?->id)
            ->first();

        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Alamat pengiriman tidak valid atau bukan milik akun ini.',
            ]);
        }

        return [
            'shipping_recipient_name' => $address->recipient_name,
            'shipping_phone' => trim(($address->phone_country_code ?? '').$address->phone_number),
            'shipping_address_line' => $address->address_line,
            'shipping_city' => $address->city,
            'shipping_district' => $address->district,
            'shipping_province' => $address->province,
            'shipping_postal_code' => $address->postal_code,
        ];
    }

    protected function checkoutShippingDestinationId(Request $request, array $validated, ?array $guest): int
    {
        if ($guest) {
            return (int) ($validated['shipping_destination_id'] ?? 0);
        }

        $address = Address::query()
            ->whereKey((int) ($validated['address_id'] ?? 0))
            ->where('user_id', $request->user()?->id)
            ->first();

        if (! $address || (int) $address->destination_id < 1) {
            throw ValidationException::withMessages([
                'address_id' => 'Alamat pengiriman belum memiliki tujuan ongkir yang valid.',
            ]);
        }

        return (int) $address->destination_id;
    }

    protected function ensureCheckoutItemsAvailable(array $items, int $companyId): void
    {
        $requestedByVariant = collect($items)
            ->filter(fn ($item) => ! empty($item['productVariantId']))
            ->groupBy(fn ($item) => (int) $item['productVariantId'])
            ->map(fn ($rows) => (int) $rows->sum(fn ($item) => max(1, (int) ($item['qty'] ?? 1))));

        foreach ($requestedByVariant as $variantId => $requestedQuantity) {
            $variant = ProductVariant::query()
                ->with('product')
                ->find((int) $variantId);

            if (! $variant || ! $variant->product || $variant->product->status !== 'active' || (int) $variant->product->company_id !== $companyId) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk sudah tidak tersedia.',
                ]);
            }

            $stock = max(0, (int) $variant->stock);
            if ($stock < $requestedQuantity) {
                throw ValidationException::withMessages([
                    'items' => $stock < 1
                        ? 'Stok salah satu produk sudah habis.'
                        : "Jumlah produk melebihi stok yang tersedia ({$stock}).",
                ]);
            }
        }
    }
}
