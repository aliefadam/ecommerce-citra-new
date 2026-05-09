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
        ]);

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim((string) $validated['code']))])
            ->first();

        if (!$coupon || !$coupon->isUsableFor((int) $validated['subtotal'])) {
            return response()->json(['message' => 'Voucher tidak valid, belum aktif, sudah habis, atau minimal belanja belum terpenuhi.'], 422);
        }

        $discount = $coupon->discountFor((int) $validated['subtotal']);
        session([
            'checkout_coupon' => [
                'code' => $coupon->code,
                'discount_amount' => $discount,
            ],
        ]);

        return response()->json([
            'ok' => true,
            'code' => $coupon->code,
            'discount_amount' => $discount,
            'message' => 'Voucher berhasil digunakan.',
        ]);
    }

    public function remove()
    {
        session()->forget('checkout_coupon');

        return response()->json(['ok' => true]);
    }
}
