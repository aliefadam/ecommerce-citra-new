# Fase 4 — Checkout dan Payment Mobile

Status: **Selesai; Fase 5 belum dimulai**  
Tanggal penyelesaian: 10 November 2026  
PRD induk: [`../prd-mobile-storefront-spa-pwa.md`](../prd-mobile-storefront-spa-pwa.md)

## Hasil

Checkout member tersedia di `/app/checkout` dengan grouping per perusahaan, alamat milik user,
signed shipping quote, voucher per perusahaan, pajak server-side, profil faktur pajak, review,
pembayaran transfer manual atau Midtrans, upload bukti, pembatalan, dan recovery status pembayaran.
Guest buy-now tetap memakai flow Blade existing dan lulus regression.

Poin mengikuti domain existing: saldo ditampilkan oleh API checkout, sedangkan penukaran poin tetap
melalui produk redeem dan flow existing; poin tidak diperlakukan sebagai diskon bebas oleh client.

## Security dan Consistency

- Harga, flash sale, stok, subtotal, voucher, PPN, ongkir, dan grand total dihitung ulang server.
- Quote ongkir terenkripsi, terikat company, destination, item fingerprint, dan kedaluwarsa 15 menit.
- `checkout_version` mendeteksi perubahan harga, isi cart, voucher, atau pajak sebelum order dibuat.
- Setiap create-order wajib memakai UUID `Idempotency-Key`; replay payload sama mengembalikan response
  pertama, payload berbeda ditolak, dan request concurrent mendapat `409 checkout_in_progress`.
- Transaction menyimpan idempotency key unik. Order Midtrans memakai order ID deterministik dari key.
- Reservation stok, kuota flash sale, dan kupon tetap berjalan dalam transaction/row lock existing.
- Cart dan coupon dibersihkan per perusahaan setelah order berhasil; partial multi-company checkout
  tidak menghapus group perusahaan lain.
- Address, order, payment status, cancel, dan proof upload selalu di-scope ke authenticated user.
- Polling berhenti pada terminal state; webhook Midtrans tetap menjadi sumber status pembayaran dan
  mempertahankan signature, amount, serta transition validation existing.
- Service worker tetap network-only untuk seluruh `/api/*`, `/sanctum/*`, checkout, dan payment.

## API Mobile Baru

- `GET /api/mobile/v1/checkout`
- `POST /api/mobile/v1/checkout/shipping-options`
- `POST|DELETE /api/mobile/v1/checkout/coupon`
- `POST /api/mobile/v1/checkout/orders`
- `GET /api/mobile/v1/orders/{orderId}/payment`
- `GET /api/mobile/v1/orders/{orderId}/payment/status`
- `POST /api/mobile/v1/orders/{orderId}/payment-proof`
- `POST /api/mobile/v1/orders/{orderId}/cancel`
- `POST /api/mobile/v1/addresses`
- endpoint lokasi province/city/district/subdistrict untuk form alamat.

Endpoint sensitif berada di `auth:sanctum`, CSRF same-origin, limiter checkout, dan feature flag mobile.

## Gate

- Fase 3 pre-gate: 29 test/237 assertion backend, 7 browser flow, build dan audit lulus.
- Final backend checkout/mobile regression: 54 test/394 assertion lulus.
- Mobile checkout API: 6 test/41 assertion lulus untuk manual/Midtrans replay, ownership, stale
  version, multi-company, dan totals.
- Mobile browser: 6 test lulus untuk cart → checkout → signed shipping → manual order → payment,
  wishlist/session, katalog, dan shell.
- Existing checkout/storefront browser regression: 8 test lulus untuk guest positive/negative,
  multi-company, product-to-cart, dan shell desktop.
- `npm run build`, Pint, `git diff --check`, dan `npm audit --audit-level=high` lulus; audit JavaScript
  0 vulnerability. Initial mobile JS 46,30 KB gzip, CSS 5,62 KB gzip.

## Batas Scope

- Guest checkout mobile SPA penuh tetap tidak dibuat; guest buy-now Blade existing dipertahankan.
- Order history/detail dan tracking masuk Fase 5.
- Provider payment membutuhkan konfigurasi Midtrans environment yang valid pada deployment.
- Smoke test perangkat fisik iOS/Android dan standalone PWA tetap diperlukan sebelum pilot.

## Rollback

- Versi aplikasi `checkout-4`; cache `ecommerce-citra-mobile-checkout-v4`.
- `MOBILE_SPA_ENABLED=false` menutup shell dan API mobile tanpa mengubah storefront desktop.
