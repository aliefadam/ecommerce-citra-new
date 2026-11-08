# Fase 1 — Foundation Mobile SPA/PWA

Status: **Selesai; Fase 2 belum dimulai**  
Tanggal penyelesaian: 9 November 2026  
PRD induk: [`../prd-mobile-storefront-spa-pwa.md`](../prd-mobile-storefront-spa-pwa.md)

## Hasil

Foundation mobile SPA tersedia secara opt-in pada `/app` dan tetap tersembunyi ketika
`MOBILE_SPA_ENABLED=false`. Storefront desktop Blade tidak dialihkan atau diubah tampilannya.

Deliverable Fase 1:

- Vue 3, Vue Router, Pinia, dan entry Vite mobile yang terpisah;
- shell maksimum 430 px dengan lima bottom navigation, safe area, focus management, serta state
  loading/error/retry/offline/update;
- direct route `/app/{path?}`, feature flag, dan fallback desktop yang tetap tersedia;
- API session ter-versi di `/api/mobile/v1` untuk bootstrap, login, session, dan logout;
- autentikasi Sanctum same-origin berbasis cookie dan CSRF tanpa bearer token di penyimpanan browser;
- manifest `/app/manifest.webmanifest` dan service worker terpisah dengan scope `/app/`;
- service worker tidak meng-cache request `/api/*` atau `/sanctum/*`;
- observability client dasar untuk route transition dan error event;
- pemetaan `AuthenticationException` dan `AuthorizationException` API menjadi 401 dan 403.

Cart, wishlist, katalog nyata, checkout, order creation, payment, dan API transaksi tidak termasuk
Fase 1 dan belum diimplementasikan.

## Gate Fase 1

| Kriteria | Bukti | Status |
| --- | --- | --- |
| Direct load, refresh, Back/Forward | Playwright memuat `/app/categories`, reload, dan kembali ke Home | Lulus |
| Route guard dan session | Guest diarahkan ke login; login kembali ke route tujuan; logout menghapus akses | Lulus |
| Isolation desktop | `/` tetap memakai shell Blade; `/app` memakai bundle terpisah dan maksimum 430 px | Lulus |
| Feature flag/rollback | `/app` dan `/api/mobile/v1` menghasilkan 404 ketika flag mati | Lulus |
| Asset build/budget | Initial JS mobile 43,77 KB gzip; CSS 2,96 KB gzip | Lulus |
| Accessibility shell | Landmark/nav label, skip link, route-heading focus, touch target, reduced motion, safe area | Lulus |
| PWA isolation/update | Scope `/app/`, cache versioning, update notice, offline fallback, API/auth network-only | Lulus |
| Security dependency JS | `npm audit --audit-level=high` | Lulus, 0 vulnerability |

Pemeriksaan install/standalone dan update service worker pada perangkat fisik tetap menjadi smoke test
sebelum pilot; hal ini tidak memperluas scope Fase 1 ke rollout.

## Verifikasi

- `php artisan test tests/Feature/MobileSpaFoundationTest.php`: 6 test, 47 assertion lulus.
- Related backend regression: 16 test, 122 assertion lulus.
- `npm run test:e2e -- tests/Browser/mobile-spa-foundation.spec.cjs`: 1 test lulus.
- `npm run test:e2e -- tests/Browser/storefront-shell.spec.cjs`: 1 test lulus.
- `npm run build`: lulus.
- `vendor/bin/pint --dirty --test`: lulus.
- `git diff --check`: lulus.

## Konfigurasi Operasional

- Default dan contoh environment: `MOBILE_SPA_ENABLED=false`.
- Pilot diaktifkan secara eksplisit dengan `MOBILE_SPA_ENABLED=true`.
- Rollback dilakukan dengan mematikan flag; desktop Blade tetap menjadi storefront default.
- Playwright mengaktifkan flag hanya pada environment E2E.

## Remaining Issue

`composer audit` mendeteksi 20 advisory pada dependency existing yang terkunci lama. Pembaruan terarah
telah dicoba dua kali, tetapi Packagist tidak dapat dijangkau (`curl error 28`, timeout port 443),
sehingga remediation Composer belum dapat diselesaikan. Sanctum yang ditambahkan untuk Fase 1 tetap
terpasang, test backend lulus, dan isu dependency existing ini harus ditutup sebelum exposure pilot
diperluas.

## Keputusan

Gate teknis Fase 1 dinyatakan **lulus**. Program berhenti pada foundation; Fase 2 hanya boleh dimulai
setelah instruksi dan persetujuan scope berikutnya.
