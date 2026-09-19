<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ShippingQuoteService
{
    public function issue(
        int $companyId,
        int $destinationId,
        string $itemFingerprint,
        int $weightGrams,
        int $cost,
        string $label,
    ): string {
        return Crypt::encryptString(json_encode([
            'version' => 1,
            'company_id' => $companyId,
            'destination_id' => $destinationId,
            'item_fingerprint' => $itemFingerprint,
            'weight_grams' => $weightGrams,
            'cost' => max(0, $cost),
            'label' => mb_substr(trim($label), 0, 100),
            'expires_at' => now()->addMinutes(15)->timestamp,
        ], JSON_THROW_ON_ERROR));
    }

    /** @return array{cost: int, label: string} */
    public function verify(
        string $token,
        int $companyId,
        int $destinationId,
        string $itemFingerprint,
    ): array {
        try {
            $quote = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            Log::warning('Checkout shipping quote rejected because token could not be decrypted.', [
                'company_id' => $companyId,
                'destination_id' => $destinationId,
            ]);

            throw ValidationException::withMessages([
                'shipping_quote_token' => 'Quote ongkir tidak valid. Silakan pilih ulang layanan pengiriman.',
            ]);
        }

        $valid = is_array($quote)
            && (int) ($quote['version'] ?? 0) === 1
            && (int) ($quote['company_id'] ?? 0) === $companyId
            && (int) ($quote['destination_id'] ?? 0) === $destinationId
            && hash_equals((string) ($quote['item_fingerprint'] ?? ''), $itemFingerprint)
            && (int) ($quote['expires_at'] ?? 0) >= now()->timestamp
            && (int) ($quote['cost'] ?? -1) >= 0
            && trim((string) ($quote['label'] ?? '')) !== '';

        if (! $valid) {
            Log::warning('Checkout shipping quote rejected because its checkout context did not match.', [
                'company_id' => $companyId,
                'destination_id' => $destinationId,
                'quote_company_id' => (int) ($quote['company_id'] ?? 0),
                'quote_destination_id' => (int) ($quote['destination_id'] ?? 0),
                'expired' => (int) ($quote['expires_at'] ?? 0) < now()->timestamp,
            ]);

            throw ValidationException::withMessages([
                'shipping_quote_token' => 'Quote ongkir sudah kedaluwarsa atau tidak cocok dengan pesanan.',
            ]);
        }

        return [
            'cost' => (int) $quote['cost'],
            'label' => (string) $quote['label'],
        ];
    }
}
