# Fase 3 — Auth, Cart, dan Wishlist Mobile

Status: **Selesai; Fase 4 belum dimulai**  
Tanggal penyelesaian: 9 November 2026  
PRD induk: [`../prd-mobile-storefront-spa-pwa.md`](../prd-mobile-storefront-spa-pwa.md)

## Hasil

Member mobile dapat mendaftar, login, mempertahankan return route, menambah dan mengelola cart, serta
menyimpan wishlist. Guest cart penuh tetap ditunda sesuai keputusan program; guest yang memilih cart
atau wishlist diarahkan ke login, sedangkan guest buy-now Blade existing tetap dipertahankan.

Deliverable Fase 3:

- registrasi same-origin dengan normalisasi email, password confirmation, auto-login, dan rate limit;
- deteksi session expiry global yang menghapus state client dan kembali ke login dengan return URL;
- cart member: read, add, update, delete, clear, quantity summary, subtotal, dan issue reconciliation;
- stok, status produk/perusahaan, flash-sale price, dan subtotal dihitung ulang server-side;
- mutasi cart memakai transaction dan row lock untuk mengurangi lost update;
- cart ID selalu di-scope ke user; ID customer lain menghasilkan 404;
- wishlist memakai `PUT`/`DELETE` idempotent dan hanya menampilkan produk storefront-visible;
- halaman cart, wishlist, register, serta aksi cart/wishlist pada detail produk;
- checkout tetap nonaktif dan diberi label Fase 4.

## Kontrak API

Seluruh endpoint berikut berada di bawah `auth:sanctum`, kecuali registrasi:

| Method/path | Fungsi |
| --- | --- |
| `POST /api/mobile/v1/auth/register` | Registrasi session first-party |
| `GET /api/mobile/v1/cart` | Cart terkini sekaligus reconciliation |
| `POST /api/mobile/v1/cart/items` | Tambah varian dan kuantitas |
| `PATCH /api/mobile/v1/cart/items/{id}` | Ubah kuantitas milik current user |
| `DELETE /api/mobile/v1/cart/items/{id}` | Hapus item milik current user |
| `DELETE /api/mobile/v1/cart` | Kosongkan cart current user |
| `GET /api/mobile/v1/wishlist` | Produk tersimpan yang masih visible |
| `PUT /api/mobile/v1/wishlist/{slug}` | Simpan secara idempotent |
| `DELETE /api/mobile/v1/wishlist/{slug}` | Hapus secara idempotent |

Client tidak mengirim atau menentukan harga, subtotal, seller/company, status produk, maupun stok.
Payload harga palsu diabaikan. Service worker tetap tidak menyimpan response `/api/*` dan
`/sanctum/*`.

## Security Gate

| Skenario | Status |
| --- | --- |
| Guest mengakses cart/wishlist | 401, lulus |
| CSRF/session cookie same-origin | Lulus melalui flow browser Sanctum |
| Kuantitas melebihi stok | 422, lulus |
| Produk/perusahaan nonaktif | Ditolak/tersembunyi, lulus |
| Cart ID milik customer lain | 404 tanpa mutation, lulus |
| Harga palsu dari client | Diabaikan, harga server dipakai |
| Stock berubah setelah cart dibuat | Ditandai issue saat read/reconcile |
| PUT/DELETE wishlist diulang | Idempotent, lulus |
| Session berakhir saat route privat | State dihapus dan redirect login, lulus |
| Checkout/payment/order creation | Tidak tersedia pada Fase 3 |

## Verifikasi

- `php artisan test tests/Feature/MobileCommerceApiTest.php`: 6 test, 44 assertion lulus.
- Combined mobile commerce/catalog/foundation dan guest checkout regression: 29 test, 237 assertion lulus.
- `npm run test:e2e -- tests/Browser/mobile-spa-commerce.spec.cjs`: 2 test lulus.
- `npm run test:e2e -- tests/Browser/mobile-spa-catalog.spec.cjs`: 3 test lulus.
- `npm run test:e2e -- tests/Browser/mobile-spa-foundation.spec.cjs`: 1 test lulus.
- `npm run test:e2e -- tests/Browser/product-to-cart.spec.cjs`: 1 test lulus setelah locator login diselaraskan dengan locale aktual.
- `npm run build`: lulus; initial JS 46,08 KB gzip dan CSS 5,20 KB gzip.
- Lighthouse `/app`: accessibility 100, best practices 100, SEO 100; console error nol.
- Pint dan `git diff --check`: lulus; `npm audit --audit-level=high`: 0 vulnerability.

## Rollback dan Batas Scope

- Versi aplikasi dinaikkan menjadi `commerce-3`; cache service worker menjadi
  `ecommerce-citra-mobile-commerce-v3`.
- `MOBILE_SPA_ENABLED=false` menutup shell dan seluruh API mobile.
- Guest cart persistence, checkout, coupon, shipping, tax, points, order creation, payment, dan
  idempotency key order belum diimplementasikan.

## Remaining Issue

- Remediation advisory Composer existing masih tertunda karena timeout Packagist.
- Smoke test Safari iOS/Chrome Android dan standalone PWA tetap diperlukan sebelum pilot.
- Dua assertion copy desktop pada `StorefrontCatalogExperienceTest` gagal terhadap baseline view
  existing dan tidak diubah karena larangan redesign desktop.

## Keputusan

Gate teknis Fase 3 dinyatakan **lulus**. Fase 4 belum dimulai; checkout dan payment memerlukan gate
security/idempotency tersendiri.
