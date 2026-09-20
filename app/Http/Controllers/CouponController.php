<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    use ScopesToActiveCompany;

    public function index()
    {
        $coupons = Coupon::where('company_id', $this->activeCompanyId())->latest()->get();

        return view('backend.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['code'] = Coupon::normalizeCode((string) $validated['code']);
        $validated['normalized_code'] = $validated['code'];
        $validated['company_id'] = $this->activeCompanyId();
        $this->ensureCodeIsUnique($validated['normalized_code'], $validated['company_id']);
        Coupon::create($validated);

        return back()->with('success', 'Voucher berhasil dibuat.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->guardCompanyOwnership($coupon->company_id);

        $validated = $this->validated($request, $coupon);
        $validated['code'] = Coupon::normalizeCode((string) $validated['code']);
        $validated['normalized_code'] = $validated['code'];
        $this->ensureCodeIsUnique($validated['normalized_code'], (int) $coupon->company_id, $coupon->id);
        $coupon->update($validated);

        return back()->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon)
    {
        $this->guardCompanyOwnership($coupon->company_id);

        $coupon->delete();

        return back()->with('success', 'Voucher berhasil dihapus.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'min_purchase' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'is_member_only' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['is_member_only'] = (bool) ($validated['is_member_only'] ?? false);
        $validated['min_purchase'] = (int) ($validated['min_purchase'] ?? 0);
        $validated['max_discount'] = ($validated['max_discount'] ?? null) !== null ? (int) $validated['max_discount'] : null;
        $validated['usage_limit'] = ($validated['usage_limit'] ?? null) !== null ? (int) $validated['usage_limit'] : null;

        if ($validated['type'] === 'percent' && (int) $validated['value'] > 100) {
            throw ValidationException::withMessages(['value' => 'Diskon persen maksimal 100%.']);
        }

        return $validated;
    }

    private function ensureCodeIsUnique(string $normalizedCode, int $companyId, ?int $ignoreId = null): void
    {
        $exists = Coupon::query()
            ->where('company_id', $companyId)
            ->where('normalized_code', $normalizedCode)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['code' => 'Kode voucher sudah digunakan pada perusahaan ini.']);
        }
    }
}
