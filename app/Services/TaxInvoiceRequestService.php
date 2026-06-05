<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use App\Models\UserTaxProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TaxInvoiceRequestService
{
    public function payloadRequestsTaxInvoice(array $payload): bool
    {
        return filter_var(Arr::get($payload, 'requested', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function requestForTransaction(Transaction $transaction, User $user, array $payload): ?TransactionTaxInvoice
    {
        if (! $this->payloadRequestsTaxInvoice($payload)) {
            return null;
        }

        return DB::transaction(function () use ($transaction, $user, $payload) {
            $freshTransaction = Transaction::query()
                ->with('taxInvoice')
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            $existing = $freshTransaction->taxInvoice;
            if ($existing && ! in_array($existing->status, [
                TransactionTaxInvoice::STATUS_REJECTED,
                TransactionTaxInvoice::STATUS_CANCELLED,
            ], true)) {
                throw new RuntimeException('Faktur pajak untuk transaksi ini sudah pernah diminta.');
            }

            $data = $this->validatedTaxpayerData($user, $payload);
            $now = now();

            if (filter_var(Arr::get($payload, 'save_profile', false), FILTER_VALIDATE_BOOLEAN)) {
                $this->saveProfile($user, $data, filter_var(Arr::get($payload, 'set_default_profile', false), FILTER_VALIDATE_BOOLEAN));
            }

            return TransactionTaxInvoice::query()->updateOrCreate(
                ['transaction_id' => $freshTransaction->id],
                [
                    'requested_by_user_id' => $user->id,
                    'status' => TransactionTaxInvoice::STATUS_REQUESTED,
                    'taxpayer_name' => $data['taxpayer_name'],
                    'taxpayer_number' => $data['taxpayer_number'],
                    'taxpayer_address' => $data['taxpayer_address'],
                    'taxpayer_email' => $data['taxpayer_email'],
                    'customer_note' => $data['customer_note'],
                    'admin_note' => null,
                    'requested_at' => $now,
                    'processing_at' => null,
                    'rejected_at' => null,
                    'rejected_reason' => null,
                ]
            );
        });
    }

    public function validatedTaxpayerData(User $user, array $payload): array
    {
        $profileId = (int) Arr::get($payload, 'profile_id', 0);
        $profile = $profileId > 0
            ? UserTaxProfile::query()->where('user_id', $user->id)->whereKey($profileId)->first()
            : null;

        if ($profile) {
            $payload = array_merge([
                'taxpayer_name' => $profile->taxpayer_name,
                'taxpayer_number' => $profile->taxpayer_number,
                'taxpayer_address' => $profile->taxpayer_address,
                'taxpayer_email' => $profile->taxpayer_email,
            ], $payload);
        }

        $validator = Validator::make($payload, [
            'taxpayer_name' => ['required', 'string', 'max:255'],
            'taxpayer_number' => ['required', 'string', 'max:32'],
            'taxpayer_address' => ['required', 'string', 'max:2000'],
            'taxpayer_email' => ['required', 'email', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = $validator->validate();
        $normalizedNumber = TransactionTaxInvoice::normalizeTaxpayerNumber($data['taxpayer_number']);

        if ($normalizedNumber === '') {
            throw ValidationException::withMessages([
                'taxpayer_number' => 'Nomor NPWP wajib diisi.',
            ]);
        }

        $data['taxpayer_number'] = $normalizedNumber;
        $data['customer_note'] = (string) ($data['customer_note'] ?? '');

        return $data;
    }

    private function saveProfile(User $user, array $data, bool $setDefault): UserTaxProfile
    {
        if ($setDefault || ! $user->taxProfiles()->exists()) {
            $user->taxProfiles()->update(['is_default' => false]);
            $setDefault = true;
        }

        return UserTaxProfile::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'taxpayer_number' => $data['taxpayer_number'],
            ],
            [
                'taxpayer_name' => $data['taxpayer_name'],
                'taxpayer_address' => $data['taxpayer_address'],
                'taxpayer_email' => $data['taxpayer_email'],
                'is_default' => $setDefault,
            ]
        );
    }
}
