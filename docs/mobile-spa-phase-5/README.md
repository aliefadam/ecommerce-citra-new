# Fase 5 — Account, Order, dan Tracking Mobile

Status: **Selesai; Fase 6 belum dimulai**  
Tanggal penyelesaian: 10 November 2026  
PRD induk: [`../prd-mobile-storefront-spa-pwa.md`](../prd-mobile-storefront-spa-pwa.md)

## Hasil

- Profil member dan perubahan password.
- Daftar alamat, tambah, jadikan utama, dan hapus alamat.
- Riwayat order dengan filter status dan detail item/totals/pengiriman.
- Recovery pembayaran melalui halaman payment Fase 4 dan aksi pembatalan berbasis status.
- Tracking resi melalui `ShipmentTrackingService` existing dengan cache dan fallback aman.
- Invoice existing dan download faktur pajak yang tetap melewati ownership controller server.
- Permintaan faktur pajak baru dengan NPWP termasking pada respons.
- Notifikasi, unread count, mark-read, dan mark-all-read.
- Customer dapat menandai order dikirim sebagai selesai; transition tetap memakai transaction lock,
  history, loyalty points, dan notification domain existing.

## Security Gate

- Semua endpoint berada di `auth:sanctum`, same-origin CSRF, dan feature flag mobile.
- Order, alamat, faktur pajak, dan notifikasi selalu dicari melalui `user_id` authenticated user.
- Cross-user identifier menghasilkan `404` dan tidak membocorkan keberadaan resource.
- Nomor telepon pengiriman dan NPWP dimasking; raw NPWP tidak dikirim di kontrak read API.
- Client hanya menerima capability flags. Completion, cancel, payment, dan tax invoice tetap
  divalidasi ulang server berdasarkan status terbaru.
- Completion memakai row lock existing dan tidak dapat dijalankan dua kali.
- Invoice/faktur pajak memakai route download existing yang mengulang ownership check.
- Tracking hanya dipanggil setelah order ownership lolos dan tidak menerima resi dari client.
- Tidak ada data account/order yang dimasukkan ke service-worker cache karena seluruh `/api/*`
  tetap network-only.

## API Mobile Baru

- `GET /api/mobile/v1/account`
- `PATCH /api/mobile/v1/account/profile`
- `PATCH /api/mobile/v1/account/password`
- `PATCH|DELETE /api/mobile/v1/addresses/{address}`
- `GET /api/mobile/v1/orders`
- `GET /api/mobile/v1/orders/{orderId}`
- `POST /api/mobile/v1/orders/{orderId}/complete`
- `POST /api/mobile/v1/orders/{orderId}/tax-invoice`
- `GET /api/mobile/v1/notifications`
- `POST /api/mobile/v1/notifications/{notification}/read`
- `POST /api/mobile/v1/notifications/read-all`

## Verifikasi

- Focused API account/security: 5 test/47 assertion lulus.
- Regression checkout, tracking, tax invoice, member checkout, dan webhook: 37 test lulus.
- Full backend: 265 test/1742 assertion lulus; empat assertion copy/localization desktop existing
  tetap gagal di `GuestAccountConversionTest` dan `StorefrontCatalogExperienceTest`.
- Final browser regression: 15 test lulus, termasuk checkout → order history → detail/invoice →
  notifications → wishlist, lost-response retry, guest checkout, multi-company, dan desktop shell.
- `npm run build`, Pint dirty, `git diff --check`, dan `npm audit --audit-level=high` lulus;
  audit JavaScript 0 vulnerability. Initial mobile core sekitar 51,75 KB gzip, CSS 5,96 KB gzip;
  view Fase 5 tetap lazy-loaded (masing-masing maksimal 2,27 KB gzip).

## Batas Scope dan Rollback

- Avatar upload mobile tidak ditambahkan; avatar existing tetap dapat dikelola melalui profil desktop.
- Guest tracking tetap menggunakan flow verifikasi Blade existing.
- Versi aplikasi `account-5`; cache `ecommerce-citra-mobile-account-v5`.
- `MOBILE_SPA_ENABLED=false` menutup shell/API mobile tanpa mengubah storefront desktop.
- Fase 6 menangani device matrix, accessibility/performance formal, analytics, pilot, dan rollout.
