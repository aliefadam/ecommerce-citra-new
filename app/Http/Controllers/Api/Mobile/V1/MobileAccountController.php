<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerOrderController;
use App\Models\Address;
use App\Models\Transaction;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\ShipmentTrackingService;
use App\Services\TaxInvoiceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MobileAccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => [
            'profile' => $this->profileData($user),
            'addresses' => $user->addresses()->orderByDesc('is_primary')->latest()->get()->map($this->addressData(...)),
            'tax_profiles' => $user->taxProfiles()->orderByDesc('is_default')->latest()->get()->map(fn ($profile) => [
                'id' => (int) $profile->id,
                'name' => (string) $profile->taxpayer_name,
                'number' => (string) $profile->masked_taxpayer_number,
                'email' => (string) $profile->taxpayer_email,
                'is_default' => (bool) $profile->is_default,
            ]),
        ]]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if (User::query()
                        ->whereKeyNot($user->id)
                        ->whereRaw('LOWER(email) = ?', [strtolower(trim((string) $value))])
                        ->exists()) {
                        $fail('Email sudah digunakan akun lain.');
                    }
                },
            ],
            'phone_country_code' => ['nullable', 'string', 'max:8'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);
        $validated['email'] = strtolower(trim((string) $validated['email']));
        $validated['name'] = trim($validated['first_name'].' '.($validated['last_name'] ?? ''));
        $user->update($validated);

        return response()->json(['data' => $this->profileData($user->fresh())]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Password saat ini tidak valid.']);
        }
        $request->user()->update(['password' => $validated['password']]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }

    public function updateAddress(Request $request, int $address): JsonResponse
    {
        $owned = Address::query()->where('user_id', $request->user()->id)->findOrFail($address);
        $validated = $this->validateAddress($request);
        DB::transaction(function () use ($request, $owned, $validated) {
            if (! empty($validated['is_primary'])) {
                $request->user()->addresses()->whereKeyNot($owned->id)->update(['is_primary' => false]);
            }
            $owned->update($validated);
            if (! $request->user()->addresses()->where('is_primary', true)->exists()) {
                $owned->update(['is_primary' => true]);
            }
        });

        return response()->json(['data' => $this->addressData($owned->fresh())]);
    }

    public function deleteAddress(Request $request, int $address): JsonResponse
    {
        $owned = Address::query()->where('user_id', $request->user()->id)->findOrFail($address);
        DB::transaction(function () use ($request, $owned) {
            $wasPrimary = (bool) $owned->is_primary;
            $owned->delete();
            if ($wasPrimary) {
                $request->user()->addresses()->latest()->first()?->update(['is_primary' => true]);
            }
        });

        return response()->json(null, 204);
    }

    public function orders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'process', 'kirim', 'selesai', 'dibatalkan'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $orders = Transaction::query()
            ->with(['company', 'details', 'taxInvoice'])
            ->where('user_id', $request->user()->id)
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->whereIn('status', match ($status) {
                'pending' => ['pending', 'menunggu_pembayaran', 'menunggu_verifikasi'],
                'process' => ['paid', 'settlement', 'capture', 'process'],
                'kirim' => ['kirim', 'shipping', 'shipped'],
                'selesai' => ['selesai', 'completed'],
                'dibatalkan' => ['dibatalkan', 'cancel', 'cancelled', 'expire', 'deny', 'failure'],
            }))
            ->latest()
            ->paginate(10);

        return response()->json(['data' => $orders->getCollection()->map($this->orderSummary(...)), 'meta' => [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total(),
        ]]);
    }

    public function order(Request $request, string $orderId, ShipmentTrackingService $tracking): JsonResponse
    {
        $transaction = $this->ownedOrder($request, $orderId)->load([
            'company', 'details', 'statusHistories' => fn ($query) => $query->latest(), 'taxInvoice',
        ]);

        return response()->json(['data' => $this->orderDetail($transaction, $tracking->forTransaction($transaction))]);
    }

    public function complete(Request $request, string $orderId): JsonResponse
    {
        $transaction = $this->ownedOrder($request, $orderId);
        if (! in_array(strtolower((string) $transaction->status), ['kirim', 'shipping', 'shipped'], true)) {
            throw ValidationException::withMessages(['order' => 'Pesanan belum bisa ditandai selesai.']);
        }
        app()->call([app(CustomerOrderController::class), 'complete'], [
            'request' => $request,
            'transaction' => $transaction,
        ]);

        return response()->json(['data' => $this->orderDetail($transaction->fresh()->load(['company', 'details', 'statusHistories', 'taxInvoice']), null)]);
    }

    public function requestTaxInvoice(Request $request, string $orderId, TaxInvoiceRequestService $service): JsonResponse
    {
        $transaction = $this->ownedOrder($request, $orderId);
        if (in_array(strtolower((string) $transaction->status), ['dibatalkan', 'cancel', 'cancelled', 'expire', 'deny', 'failure'], true)) {
            throw ValidationException::withMessages(['order' => 'Faktur pajak tidak dapat diminta untuk pesanan yang dibatalkan atau gagal.']);
        }
        $validated = $request->validate([
            'profile_id' => ['nullable', 'integer'],
            'taxpayer_name' => ['nullable', 'string', 'max:255'],
            'taxpayer_number' => ['nullable', 'string', 'max:32'],
            'taxpayer_address' => ['nullable', 'string', 'max:2000'],
            'taxpayer_email' => ['nullable', 'email', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'save_profile' => ['nullable', 'boolean'],
            'set_default_profile' => ['nullable', 'boolean'],
        ]);
        $invoice = $service->requestForTransaction($transaction, $request->user(), ['requested' => true, ...$validated]);

        return response()->json(['data' => $this->taxInvoiceData($invoice)], 201);
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->getCollection()->map(fn (UserNotification $notification) => [
                'id' => (int) $notification->id,
                'type' => (string) $notification->type,
                'title' => (string) $notification->title,
                'body' => (string) $notification->body,
                'read' => $notification->isRead(),
                'created_at' => $notification->created_at?->toIso8601String(),
                'action_url' => '/app/orders',
            ]),
            'meta' => [
                'unread' => UserNotification::query()->where('user_id', $request->user()->id)->whereNull('read_at')->count(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    public function readNotification(Request $request, int $notification): JsonResponse
    {
        UserNotification::query()->where('user_id', $request->user()->id)->findOrFail($notification)->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function readAllNotifications(Request $request): JsonResponse
    {
        UserNotification::query()->where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    private function ownedOrder(Request $request, string $orderId): Transaction
    {
        return Transaction::query()->where('user_id', $request->user()->id)->where('order_id', $orderId)->firstOrFail();
    }

    private function orderSummary(Transaction $transaction): array
    {
        return [
            'order_id' => (string) $transaction->order_id,
            'invoice_no' => (string) $transaction->invoice_no,
            'company' => (string) ($transaction->company?->name ?? ''),
            'status' => (string) $transaction->status,
            'payment_type' => (string) $transaction->payment_type,
            'grand_total' => (int) $transaction->grand_total,
            'item_count' => (int) $transaction->details->sum('quantity'),
            'item_preview' => (string) ($transaction->details->first()?->product_name ?? ''),
            'created_at' => $transaction->created_at?->toIso8601String(),
            'actions' => $this->actions($transaction),
        ];
    }

    private function orderDetail(Transaction $transaction, ?array $tracking): array
    {
        return [
            ...$this->orderSummary($transaction),
            'payment_method' => (string) $transaction->payment_method,
            'subtotal' => (int) $transaction->subtotal_amount,
            'discount' => (int) $transaction->discount_amount,
            'shipping_cost' => (int) $transaction->shipping_cost,
            'tax_name' => (string) $transaction->tax_name,
            'tax_amount' => (int) $transaction->tax_amount,
            'shipping' => [
                'recipient_name' => (string) $transaction->shipping_recipient_name,
                'phone' => $this->maskPhone((string) $transaction->shipping_phone),
                'address' => (string) $transaction->shipping_address_line,
                'district' => (string) $transaction->shipping_district,
                'city' => (string) $transaction->shipping_city,
                'province' => (string) $transaction->shipping_province,
                'postal_code' => (string) $transaction->shipping_postal_code,
                'courier' => (string) ($transaction->shipping_courier_name ?: $transaction->shipping_label),
                'service' => (string) $transaction->shipping_service,
                'tracking_number' => (string) $transaction->tracking_number,
            ],
            'items' => $transaction->details->map(fn ($detail) => [
                'id' => (int) $detail->id,
                'name' => (string) $detail->product_name,
                'variant' => (string) $detail->variant_name,
                'sku' => (string) $detail->sku,
                'image' => (string) $detail->image,
                'price' => (int) $detail->price,
                'quantity' => (int) $detail->quantity,
                'subtotal' => (int) $detail->subtotal,
            ]),
            'timeline' => $transaction->statusHistories->map(fn ($history) => [
                'status' => (string) $history->to_status,
                'note' => (string) $history->note,
                'created_at' => $history->created_at?->toIso8601String(),
            ]),
            'tracking' => $tracking,
            'invoice_url' => route('invoice.show', ['transaction' => $transaction->id]),
            'tax_invoice' => $this->taxInvoiceData($transaction->taxInvoice),
        ];
    }

    private function actions(Transaction $transaction): array
    {
        $status = strtolower((string) $transaction->status);
        $taxInvoice = $transaction->relationLoaded('taxInvoice') ? $transaction->taxInvoice : null;

        return [
            'can_pay' => in_array($status, ['pending', 'menunggu_pembayaran'], true),
            'can_cancel' => in_array($status, ['pending', 'menunggu_pembayaran', 'menunggu_verifikasi'], true),
            'can_complete' => in_array($status, ['kirim', 'shipping', 'shipped'], true),
            'can_request_tax_invoice' => ! $taxInvoice || in_array($taxInvoice->status, [TransactionTaxInvoice::STATUS_REJECTED, TransactionTaxInvoice::STATUS_CANCELLED], true),
        ];
    }

    private function taxInvoiceData(?TransactionTaxInvoice $invoice): ?array
    {
        if (! $invoice) {
            return null;
        }

        return [
            'status' => (string) $invoice->status,
            'taxpayer_name' => (string) $invoice->taxpayer_name,
            'taxpayer_number' => (string) $invoice->masked_taxpayer_number,
            'number' => (string) $invoice->tax_invoice_number,
            'date' => $invoice->tax_invoice_date?->toDateString(),
            'download_url' => $invoice->tax_invoice_file_path
                ? route('frontend.profil.orders.tax-invoice.download', ['transaction' => $invoice->transaction_id])
                : null,
        ];
    }

    private function profileData($user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'first_name' => (string) ($user->first_name ?: $user->name),
            'last_name' => (string) $user->last_name,
            'username' => (string) $user->username,
            'email' => (string) $user->email,
            'gender' => (string) $user->gender,
            'phone_country_code' => (string) $user->phone_country_code,
            'phone_number' => (string) $user->phone_number,
            'birth_date' => $user->birth_date?->toDateString(),
            'bio' => (string) $user->bio,
            'point_balance' => (int) $user->point_balance,
        ];
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', Rule::in(['Rumah', 'Kantor', 'Lainnya'])],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone_country_code' => ['required', 'string', 'max:8'],
            'phone_number' => ['required', 'string', 'max:30'],
            'province_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'subdistrict_id' => ['nullable', 'integer'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'subdistrict' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'destination_id' => ['required', 'integer', 'min:1'],
            'address_line' => ['required', 'string', 'max:1000'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }

    private function addressData(Address $address): array
    {
        return [
            'id' => (int) $address->id,
            'label' => (string) $address->label,
            'recipient_name' => (string) $address->recipient_name,
            'phone_country_code' => (string) $address->phone_country_code,
            'phone_number' => (string) $address->phone_number,
            'province_id' => $address->province_id ? (int) $address->province_id : null,
            'city_id' => $address->city_id ? (int) $address->city_id : null,
            'district_id' => $address->district_id ? (int) $address->district_id : null,
            'subdistrict_id' => $address->subdistrict_id ? (int) $address->subdistrict_id : null,
            'province' => (string) $address->province,
            'city' => (string) $address->city,
            'district' => (string) $address->district,
            'subdistrict' => (string) $address->subdistrict,
            'postal_code' => (string) $address->postal_code,
            'destination_id' => (int) $address->destination_id,
            'address_line' => (string) $address->address_line,
            'is_primary' => (bool) $address->is_primary,
        ];
    }

    private function maskPhone(string $phone): string
    {
        $length = strlen($phone);

        return $length <= 6 ? str_repeat('*', $length) : substr($phone, 0, 4).str_repeat('*', $length - 7).substr($phone, -3);
    }
}
