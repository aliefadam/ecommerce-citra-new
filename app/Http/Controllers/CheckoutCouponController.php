<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CheckoutCouponController extends Controller
{
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $companyId = (int) $validated['company_id'];
        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim((string) $validated['code']))])
            ->first();

        if (!$coupon || (int) $coupon->company_id !== $companyId || !$coupon->isUsableFor((int) $validated['subtotal'])) {
            return response()->json(['message' => 'Voucher tidak valid, belum aktif, sudah habis, atau minimal belanja belum terpenuhi.'], 422);
        }

        $discount = $coupon->discountFor((int) $validated['subtotal']);
        $this->setCouponForCompany($companyId, [
            'code' => $coupon->code,
            'discount_amount' => $discount,
        ]);

        return response()->json([
            'ok' => true,
            'code' => $coupon->code,
            'discount_amount' => $discount,
            'message' => 'Voucher berhasil digunakan.',
        ]);
    }

    public function remove(Request $request)
    {
        $companyId = (int) $request->input('company_id', 0);

        if ($companyId > 0) {
            $this->setCouponForCompany($companyId, null);
        } else {
            session()->forget('checkout_coupon');
        }

        return response()->json(['ok' => true]);
    }

    /**
     * checkout_coupon disimpan sebagai map company_id => {code, discount_amount} -- checkout
     * marketplace membuat N transaksi sekaligus (satu per perusahaan), jadi kupon tiap perusahaan
     * harus tersimpan independen, bukan satu value flat seperti sebelum checkout multi-perusahaan
     * ada. Lihat docs/prd-multi-company-foundation.md §4b.
     */
    private function setCouponForCompany(int $companyId, ?array $coupon): void
    {
        $couponsByCompany = (array) session('checkout_coupon', []);

        if ($coupon === null) {
            unset($couponsByCompany[$companyId]);
        } else {
            $couponsByCompany[$companyId] = $coupon;
        }

        session(['checkout_coupon' => $couponsByCompany]);
    }
}
