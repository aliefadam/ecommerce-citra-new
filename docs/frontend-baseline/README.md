# Sprint 0 Report: Frontend Customer Baseline dan Inventory

Tanggal: 13 September 2026  
Status: Selesai  
PRD: [Modernisasi Frontend Customer](../prd-modernisasi-frontend-customer.md)  
Lingkungan ukur: Laravel `e2e`, SQLite terisolasi, Chrome headless, production Vite build

## Ringkasan Eksekutif

Sprint 0 menghasilkan baseline yang dapat diulang untuk membandingkan perubahan pada Sprint 1-7.
Tidak ada route, controller, model, migration, atau business behavior customer yang diubah.

Temuan utama:

1. Styling kritis frontend masih bergantung pada Tailwind Browser CDN.
2. Audit tanpa akses layanan eksternal menghasilkan 733 failed request dan 20 error
   `tailwind is not defined`.
3. Detail produk menjalankan 45 query, termasuk 9 pola query duplikat.
4. HTML profil mencapai sekitar 266 KB; homepage dan checkout sekitar 208 KB.
5. JavaScript global hasil build kosong karena logic masih dominan inline di Blade.
6. Terdapat 15 blok style inline, 16 blok script inline, 398 penggunaan kelas teks sangat kecil,
   dan 50 deklarasi z-index pada area customer/auth.
7. Route `/promo` mengembalikan 404 saat tidak ada promo aktif/slug; empty state ini perlu diperbaiki.
8. Guest yang membuka `/cart` dialihkan ke login; ini sesuai middleware existing, tetapi copy dan
   recovery path perlu dievaluasi pada sprint terkait.

## Artefak Baseline

- [Raw capture report](capture-report.json)
- [Screenshot viewport 360](screenshots/360/)
- [Screenshot viewport 390](screenshots/390/)
- [Screenshot viewport 768](screenshots/768/)
- [Screenshot viewport 1024](screenshots/1024/)
- [Screenshot viewport 1440](screenshots/1440/)
- Query probe: `tests/Feature/FrontendCustomerBaselineTest.php`
- Screenshot runner: `scripts/capture_frontend_baseline.cjs`

Total screenshot: **95** (19 state/page x 5 viewport), sekitar **6,8 MB**.

### Menjalankan ulang baseline

```powershell
npm.cmd run build
node scripts/prepare_manual_e2e.cjs

$env:APP_ENV='e2e'
$env:APP_URL='http://127.0.0.1:8878'
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE=(Resolve-Path 'storage/framework/testing/browser-e2e.sqlite').Path
$env:SESSION_DRIVER='database'
$env:CACHE_STORE='array'
$env:MAIL_MAILER='array'
php artisan serve --host=127.0.0.1 --port=8878
```

Pada terminal kedua:

```powershell
$env:FRONTEND_BASELINE_URL='http://127.0.0.1:8878'
node scripts/capture_frontend_baseline.cjs
php artisan test tests/Feature/FrontendCustomerBaselineTest.php
```

Database yang dipakai berada di `storage/framework/testing/browser-e2e.sqlite`; runner tidak memakai
database development/production dari `.env`.

### Catatan metode screenshot

Layout baseline belum menghubungkan CSS Vite ke frontend. Agar struktur visual tetap dapat direkam,
runner menginjeksi CSS hasil production build setelah halaman dimuat. Ini hanya dilakukan pada runner
audit dan bukan perubahan aplikasi.

Font, icon, banner, dan foto produk dari layanan eksternal tidak diinjeksi atau dipalsukan. Beberapa
gambar/icon pada screenshot terlihat kosong; kondisi ini sengaja dipertahankan sebagai bukti risiko
dependency eksternal. Screenshot bukan golden image final dan akan diperbarui setelah Sprint 1.

## Viewport Matrix

| Label | Ukuran | Sasaran |
| --- | --- | --- |
| Mobile compact | 360x800 | Android compact / minimum supported width |
| Mobile standard | 390x844 | Smartphone modern |
| Tablet portrait | 768x1024 | Tablet dan breakpoint `md` |
| Tablet landscape | 1024x768 | Breakpoint `lg` dan layar laptop kecil |
| Desktop | 1440x1000 | Desktop operasional/customer |

Page/state yang direkam pada setiap viewport:

- Homepage.
- Kategori.
- Hasil pencarian `baut`.
- Flash sale.
- Promo tanpa slug/data.
- Blog.
- Lacak pesanan.
- Cart sebagai guest.
- Login, register, dan forgot password.
- Detail produk seeded.
- Profil customer.
- Riwayat pesanan.
- Wishlist kosong.
- Redeem point.
- Notifikasi kosong.
- Cart member.
- Checkout dengan satu produk dan ongkir yang dimock.

Halaman state-dependent seperti checkout orders, checkout waiting, invoice, faktur pajak download,
dan payment proof sudah tercakup oleh browser/feature test bisnis existing. Golden screenshot untuk
state tersebut dibuat pada Sprint 5-6 ketika struktur halamannya disentuh.

## Baseline Query dan HTML Response

Angka berikut diukur menggunakan SQLite in-memory pada test environment. Duration bukan SLA
production; query count, duplicate count, dan response bytes menjadi pembanding utama.

| Halaman | Status | Query | Duplikat | Duration lokal | HTML response |
| --- | ---: | ---: | ---: | ---: | ---: |
| Homepage | 200 | 21 | 4 | 243,86 ms | 208.846 B |
| Kategori | 200 | 19 | 5 | 307,65 ms | 190.410 B |
| Detail produk | 200 | 45 | 9 | 849,08 ms | 189.518 B |
| Cart member | 200 | 17 | 1 | 235,58 ms | 129.418 B |
| Checkout | 200 | 19 | 1 | 429,10 ms | 207.837 B |
| Profil | 200 | 17 | 1 | 690,24 ms | 266.026 B |

Implikasi Sprint 1:

- Pisahkan query kategori/search dari view navbar.
- Hapus serialisasi 60 produk dan relasi varian dari setiap halaman.
- Audit eager loading/detail produk untuk menurunkan query dan duplikasi.
- Pertahankan query probe sebagai pembanding sebelum/sesudah.

## Baseline Asset

Hasil `npm run build`:

| Asset | Raw | Gzip | Catatan |
| --- | ---: | ---: | --- |
| CSS aplikasi | 176.805 B | 25.510 B | Sudah berisi class hasil scan Blade, tetapi belum dipakai layout customer |
| JavaScript aplikasi | 0 B | 20 B | Logic customer masih berada pada script inline |
| Vite manifest | 330 B | 160 B | Build/versioning tersedia |

CSS masih di bawah performance budget PRD sebesar 120 KB gzip. JavaScript global yang kosong bukan
indikasi bahwa halaman bebas JavaScript; logic justru tersebar di 16 blok inline besar sehingga tidak
terukur sebagai bundle bersama.

## Dependency Eksternal

Failed request selama capture terisolasi:

| Host | Jumlah | Fungsi |
| --- | ---: | --- |
| `images.unsplash.com` | 328 | Foto produk seed/demo |
| `cdn.jsdelivr.net` | 210 | Tailwind Browser dan Flaticon |
| `fonts.googleapis.com` | 100 | Plus Jakarta Sans |
| `embed.tawk.to` | 70 | Chat widget |
| `cdn.tailwindcss.com` | 25 | Dependency runtime Tailwind tambahan |

Total berdasarkan resource type:

- Stylesheet: 225.
- Script: 180.
- Image: 328.

Keputusan Sprint 1:

- CSS kritis wajib berasal dari Vite build.
- Font dan icon transaksi kritis harus lokal/self-hosted.
- Chat dimuat deferred dan gagal secara graceful.
- Remote image memiliki local placeholder dan dimension/aspect ratio.

## Inventory Blade, CSS, dan JavaScript

Scope inventory: 22 file Blade customer/auth/partial, total sekitar 801 KB source.

| Temuan | Jumlah |
| --- | ---: |
| Inline `<style>` | 15 |
| Inline `<script>` | 16 |
| Tiny text (`9px`, `10px`, `11px`, `text-xs`) | 398 |
| Deklarasi z-index | 50 |
| Query/model reference langsung di Blade | 4 |

File terbesar:

| File | Baris | Ukuran |
| --- | ---: | ---: |
| `frontend/profil.blade.php` | 2.932 | 178.842 B |
| `frontend/checkout.blade.php` | 1.891 | 105.977 B |
| `frontend/detail-produk.blade.php` | 1.970 | 94.688 B |
| `frontend/index.blade.php` | 1.599 | 80.507 B |
| `frontend/kategori.blade.php` | 1.293 | 63.559 B |
| `partials/navbar-user.blade.php` | 1.046 | 54.095 B |
| `partials/footer-user.blade.php` | 573 | 41.170 B |

Pola komponen yang terduplikasi:

- Product card pada homepage, kategori, search, flash sale, redeem, wishlist, dan rekomendasi.
- Price, discount badge, stock badge, seller label, dan rating.
- Toast dan alert.
- Search dropdown.
- Modal/drawer dan backdrop.
- Quantity selector.
- Section heading dengan accent bar.
- Form validation dan loading button.
- Empty state.

Komponen tersebut menjadi candidate Blade component/design-system Sprint 2-4.

## Inventory Layer dan Z-Index

Nilai existing yang ditemukan:

- `z-10`: 7 penggunaan.
- `z-50`: 15 penggunaan.
- `z-[60]`, `z-[70]`.
- `z-[900]`, `z-[901]`, `z-[999]`.
- `z-[9999]`: 8 penggunaan.
- `z-[99999]`: 7 penggunaan.

Rentang yang tidak terstruktur menjelaskan risiko tumpang tindih navbar, mobile bottom navigation,
sticky buy action, toast, drawer, modal, dan chat. Sprint 2 harus menggantinya dengan layer token.

## Inventory Route dan Page Customer

### Public page

| Route name | URI | Kondisi |
| --- | --- | --- |
| `frontend.index` | `/` | Public |
| `frontend.kategori` | `/kategori` | Public |
| `frontend.search` | `/pencarian` | Public |
| `frontend.detail-produk` | `/detail-produk/{slug?}` | Public, data-dependent |
| `frontend.flash-sale` | `/flash-sale` | Public |
| `frontend.promo` | `/promo/{slug?}` | Public, saat kosong baseline 404 |
| `frontend.redeem-point` | `/redeem-point` | Public |
| `frontend.blog.index` | `/blog` | Public |
| `frontend.blog.show` | `/blog/{slug}` | Public, data-dependent |
| `frontend.pages.show` | `/pages/{slug}` | Public, data-dependent |
| `frontend.order-tracking.index` | `/lacak-pesanan` | Public |
| `login` | `/login` | Guest only |
| `register` | `/register` | Guest only |
| `password.request` | `/forgot-password` | Guest only |
| `password.reset` | `/reset-password/{token}` | Token-dependent |
| `frontend.newsletter.unsubscribe` | `/newsletter/unsubscribe/{token}` | Token-dependent |

### Authenticated customer page

| Route name | URI | Kondisi |
| --- | --- | --- |
| `frontend.cart` | `/cart` | Auth; guest baseline redirect login |
| `frontend.profil` | `/profil` | Auth, multi-tab |
| `frontend.wishlist.index` | `/wishlist` | Auth |
| `frontend.notifications.index` | `/notifications` | Auth |
| `frontend.checkout.orders` | `/checkout/orders` | Auth, order IDs diperlukan |
| `invoice.show` | `/invoice/{transaction}` | Auth, ownership diperlukan |
| `frontend.profil.orders.tax-invoice.download` | `/profil/orders/{transaction}/tax-invoice/download` | Auth, file/status-dependent |

### Checkout/state-dependent page

| Route name | URI | Kondisi |
| --- | --- | --- |
| `frontend.checkout` | `/checkout` | Checkout session/access required |
| `frontend.checkout.waiting` | `/checkout/waiting/{orderId}` | Ownership/session/status required |
| `payment-proof.show` | `/payment-proof/{transaction}` | Ownership/session required |

Mutation/API pendukung yang wajib dipertahankan pada redesign mencakup cart CRUD, prepare checkout,
buy now, apply/remove coupon, RajaOngkir location/shipping, Midtrans charge/status/cancel, manual
payment, wishlist, notification read, profile/address, review, return, order completion, faktur pajak,
newsletter, tracking verification, dan payment proof upload.

## Baseline Visual dan UX

### Global shell

- Announcement gradient memiliki perhatian tinggi tetapi pesan generik.
- Desktop search sudah dominan; pada mobile search tersembunyi di balik kontrol.
- Ketika icon CDN gagal, beberapa mobile navigation action hampir tidak terlihat karena label hanya
  ditampilkan pada item aktif.
- Header menggunakan beberapa gaya gradient/radius sekaligus.
- Footer terlalu besar dan banyak legal content hidup sebagai modal.

### Homepage dan katalog

- Hero menggunakan banner tanpa text fallback/value proposition.
- Broken/remote image membuat hero dan kartu kehilangan konteks.
- `Browse` dan `Popular Categories` bercampur dengan Bahasa Indonesia.
- Produk terbaru muncul sebelum customer mendapat bantuan memilih spesifikasi.
- Product card menonjolkan badge dan harga, belum SKU/material/satuan.
- Homepage desktop menempatkan filter katalog lengkap sehingga terasa seperti halaman listing.

### Detail produk

- Sticky action mobile sudah berguna dan dipertahankan.
- Purchase information terpisah dari area gambar pada mobile sehingga context mudah terpotong.
- Generic trust copy belum memberi bukti seperti asal pengiriman, metode bayar, dan retur.
- Product variant dengan kombinasi besar membutuhkan progressive selector.

### Cart dan checkout

- Group per perusahaan dan transparansi ongkir sudah menjadi fondasi yang baik.
- Ringkasan/CTA belum optimal pada mobile.
- Checkout menampilkan banyak section panjang dalam satu flow.
- Sticky purchase/navigation/chat perlu satu koordinasi safe-area.

### Profile dan supporting page

- Profil adalah file dan HTML response terbesar.
- Banyak fungsi berada pada satu Blade dan satu script block.
- Wishlist/notifikasi kosong membutuhkan empty state yang lebih kuat.
- Promo tanpa data sebaiknya tidak menjadi 404 generik.

## Keputusan Brand Baseline

Status: disetujui sebagai arah kerja Sprint 1-2 melalui persetujuan pelaksanaan Sprint 0. Nilai dapat
disesuaikan pada Sprint 2 jika bertentangan dengan logo final, tetapi karakter tidak berubah.

### Tone

**Industrial-modern / precision catalog**: profesional, kokoh, presisi, informatif, dan tidak ramai.

### Palette awal

| Token | Nilai awal | Penggunaan |
| --- | --- | --- |
| `ink-950` | `#151B23` | Navbar, heading, primary dark surface |
| `steel-600` | `#5F6B78` | Secondary text |
| `steel-200` | `#D8DEE5` | Border/divider |
| `warm-50` | `#F7F5F0` | Page background |
| `surface` | `#FFFFFF` | Card/form surface |
| `cobalt-600` | `#155EEF` | Link, focus, informational action |
| `safety-500` | `#F97316` | Primary commerce CTA dan promo penting |
| `success-600` | `#16803C` | Available/success |
| `warning-600` | `#B54708` | Low stock/warning |
| `danger-600` | `#D92D20` | Error/destructive |

### Typography

- Heading/display: `Barlow Condensed`, self-hosted, weight 600-700.
- Body/UI: `Plus Jakarta Sans`, self-hosted, weight 400-700.
- Body minimum 14px; form mobile 16px; tiny metadata minimum 12px kecuali bukti readability.
- Harga dan SKU memakai angka dengan alignment/weight konsisten.

### Density dan shape

- Controlled density: informasi teknis padat dengan grouping yang jelas.
- Radius default 10-14px; pill hanya untuk status/filter chip.
- Shadow hanya untuk floating layer.
- Border dan whitespace menjadi pemisah utama.
- Satu CTA commerce utama per viewport.

## Analytics Measurement Plan

Provider analytics belum terpasang pada baseline. Sprint 0 menetapkan kontrak event agar funnel dapat
diinstrumentasikan tanpa mengubah business rule.

| Event | Trigger | Payload aman minimum | Bukti backend pembanding |
| --- | --- | --- | --- |
| `search_submitted` | Submit search | query length, result count | Request `/pencarian` |
| `search_suggestion_selected` | Pilih autocomplete | product ID, position | Belum ada endpoint async |
| `category_opened` | Buka kategori/filter | category ID/slug | Request kategori |
| `product_viewed` | Detail tampil | product ID, company ID | Detail request/log |
| `variant_selected` | Varian berubah | product/variant ID | Client only |
| `add_to_cart` | Cart mutation sukses | product/variant ID, qty | Cart row |
| `buy_now_started` | Buy-now sukses | product/variant ID, qty | Checkout session |
| `cart_checkout_started` | Prepare checkout sukses | item/company count | Checkout session |
| `checkout_step_completed` | Tahap valid | step name, company count | Client only |
| `payment_started` | Charge/manual submit | provider, company count | Transaction/payment attempt |
| `payment_completed` | Payment terminal success | provider, transaction ID | Transaction/status history |
| `payment_failed` | Failure terminal/recoverable | provider, safe reason code | Log/status history |

Larangan payload: nama, email, telepon, alamat, NPWP, password, token, payment credential, raw provider
payload, atau free-form customer note.

## Sprint 0 Acceptance Evidence

- [x] Baseline screenshot tersedia untuk viewport 360, 390, 768, 1024, dan 1440.
- [x] Baseline performance, request, asset size, dan query count tercatat.
- [x] Daftar route/page customer lengkap tersedia.
- [x] Warna, typography, density, dan tone industrial ditetapkan.
- [x] Analytics funnel minimum dipetakan dan dapat diinstrumentasikan.
- [x] Tidak ada perubahan business behavior.

## Handoff ke Sprint 1

Urutan yang disarankan:

1. Pasang Vite asset pada layout customer dan hapus Tailwind Browser CDN.
2. Self-host font serta icon kritis; siapkan local image placeholder.
3. Ekstrak navbar query dari Blade dan cache category tree.
4. Buat endpoint autocomplete async.
5. Defer Tawk/chat.
6. Jalankan ulang query probe, capture matrix, build, backend test, dan browser test.

Target bukti Sprint 1 adalah perbandingan before/after terhadap report ini, bukan hanya screenshot baru.
