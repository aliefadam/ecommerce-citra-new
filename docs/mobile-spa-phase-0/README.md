# Fase 0 — Discovery dan Contract Inventory Mobile SPA/PWA

Status: **Selesai dan gate disetujui; Fase 1 belum dimulai**  
Tanggal audit: 9 November 2026  
Tanggal persetujuan gate: 9 November 2026  
PRD induk: [`../prd-mobile-storefront-spa-pwa.md`](../prd-mobile-storefront-spa-pwa.md)

## Ringkasan Eksekutif

Fase 0 mengonfirmasi bahwa mobile SPA dapat dibangun pada `/app` tanpa merombak storefront desktop.
Laravel sudah memiliki domain service dan perlindungan transaksi yang dapat digunakan kembali, tetapi
belum memiliki kontrak API mobile yang lengkap.

Kondisi saat ini:

- Storefront desktop/mobile menggunakan Blade, Alpine, Tailwind, dan Vite.
- Terdapat 297 route aplikasi; 88 route berada di luar namespace admin.
- API saat ini hanya terdiri dari empat endpoint katalog publik read-only dan satu webhook Midtrans.
- Cart member memiliki beberapa response JSON, tetapi checkout masih bergantung pada session dan
  payload halaman Blade.
- Guest hanya memiliki alur `buy now`; belum ada guest cart persisten.
- Vue, Vue Router, Pinia, dan Laravel Sanctum belum terpasang.
- PWA existing sudah memiliki manifest berscope `/` dan service worker berscope `/`.
- CORS `/api/*` saat ini wildcard, tanpa credential, serta hanya mengizinkan GET/HEAD/OPTIONS untuk
  kebutuhan Open Company Catalog API.
- Harga, stok, company ownership, ongkir, pajak, reservasi, dan status payment sudah memiliki
  server-authoritative service yang wajib dipertahankan.

Kesimpulan: **GO ke Fase 1**. Seluruh syarat gate pada bagian akhir dokumen telah disetujui owner
pada 9 November 2026. Persetujuan ini menutup Fase 0, tetapi bukan instruksi untuk memulai Fase 1.

## Artefak Fase 0

1. [Inventory route, fitur, dan API gap](route-api-inventory.md)
2. [Arsitektur target, trust boundary, dan threat model](architecture-security.md)
3. [Prototype konseptual shell dan pemetaan layar](shell-prototype.md)

Artefak tersebut adalah rancangan dokumentasi. Tidak ada route `/app`, dependency Vue, API mobile,
manifest mobile, atau service worker baru yang dibuat pada Fase 0.

## Baseline Teknis

### Stack existing

| Area | Existing | Implikasi mobile SPA |
| --- | --- | --- |
| Backend | Laravel 13 / PHP 8.3 | Dipertahankan sebagai sumber kebenaran |
| Desktop rendering | Blade | Tidak diubah |
| UI existing | Tailwind 4, Alpine.js | Token visual dapat dipakai ulang; komponen Blade tidak dapat dipakai langsung |
| Build | Vite 8 | Tambahkan entry SPA terpisah pada Fase 1 |
| Auth | Guard `web`, session database | Dapat dipakai same-origin; Sanctum belum tersedia |
| Public API | `/api/v1/companies/{slug}` | Read-only per perusahaan; bukan backend SPA transaksi |
| PWA | Manifest `/app.webmanifest`, SW scope `/` | Perlu manifest dan SW khusus scope `/app/` |
| E2E | Playwright Chrome, default 1440×1000 | Perlu project/device matrix mobile |

### Build baseline 9 November 2026

`npm run build` lulus dengan hasil utama:

| Asset | Raw | Gzip | Catatan |
| --- | ---: | ---: | --- |
| CSS customer | 223,17 KB | 35,69 KB | Fondasi visual existing |
| JS customer | 14,90 KB | 5,35 KB | Alpine/shell/search existing |
| JS PWA | 0,92 KB | 0,46 KB | Registrasi SW existing |
| JS admin | 505,42 KB | 124,01 KB | Tidak masuk budget mobile SPA |

Build memberi warning chunk admin di atas 500 KB. Warning tersebut bukan blocker mobile SPA karena
admin di luar scope, tetapi entry SPA wajib dipisahkan agar tidak menarik dependency admin.

### Baseline performa terakhir yang tersedia

Audit 27 September 2026 mencatat:

| Halaman | Query | LCP lab | CLS lab |
| --- | ---: | ---: | ---: |
| Homepage | 17 | 2.684 ms | 0,093881 |
| Kategori | 13 | 2.200 ms | 0,000319 |
| Detail produk | 47 | 2.804 ms | 0,000372 |

Data tersebut merupakan satu observasi Chrome lokal tanpa throttling, bukan p75 produksi. Homepage
dan detail melampaui target LCP 2,5 detik pada sampel itu. Mobile SPA harus membuat baseline baru
dengan throttling dan perangkat representatif saat prototype executable tersedia.

### Budget awal Fase 1

| Metrik | Budget awal |
| --- | ---: |
| Initial SPA JavaScript, seluruh initial chunks | ≤ 150 KB gzip |
| Satu lazy route chunk | ≤ 80 KB gzip |
| Initial SPA CSS | ≤ 60 KB gzip |
| LCP lab halaman publik utama | ≤ 2,5 detik |
| CLS lab | ≤ 0,1 |
| Feedback route transition | ≤ 100 ms |

Font, gambar produk, dan third-party chat dilaporkan terpisah. Budget diperiksa dari production build,
bukan dev server.

## Viewport, Device, dan Browser Matrix

| Prioritas | Viewport/perangkat | Browser |
| --- | --- | --- |
| P0 minimum | 360×800 Android compact | Chrome Android current dan current-1 |
| P0 standard | 390×844 smartphone modern | Chrome Android dan Safari iOS |
| P1 large phone | 430×932 | Safari iOS dan Chrome Android |
| P1 tablet boundary | 767×1024 | Safari/Chrome |
| Isolation check | 768×1024 dan 1440×1000 | Chrome/Edge desktop |

Browser support awal:

- Chrome/Edge current dan current-1;
- Safari iOS 16.4+;
- Samsung Internet current dan current-1;
- Firefox Android latest untuk functional smoke;
- standalone PWA dan browser tab pada Android/iOS.

Keyboard virtual, safe area, orientation change, reduced motion, 200% zoom/reflow, offline, jaringan
lambat, dan update service worker harus masuk matrix. Playwright tetap menjadi regression utama,
ditambah smoke test pada perangkat nyata sebelum pilot.

## Design dan Product Decisions yang Dipertahankan

- Identitas industrial-modern existing tetap dipakai.
- Search menjadi aksi utama.
- Minimum touch target 44×44 px dan input mobile 16 px.
- Seller, SKU, spesifikasi, satuan, harga, serta status stok tetap prioritas.
- Bottom navigation memiliki lima tab.
- Desktop tidak redirect paksa ke `/app`.
- `/app` pada layar lebar tetap berupa shell maksimum 430 px yang dipusatkan.
- Astro hanya referensi pola interaksi, bukan aset atau layout untuk disalin.

## Test Inventory yang Dapat Dipakai Ulang

Coverage existing yang relevan:

- katalog, autocomplete, shell, dan responsive/reflow;
- product variant → cart dan recovery error;
- guest/member/multi-company checkout;
- shipping quote tampering;
- server-authoritative pricing dan stock reservation;
- coupon, flash-sale, point, dan tax invoice;
- Midtrans signature, nominal, state ordering, serta idempotensi webhook;
- order ownership, guest tracking verification, dan payment proof;
- account lifecycle, wishlist, fulfillment, return, serta tracking.

Gap testing untuk SPA:

- direct load, refresh, Back/Forward, scroll restoration, dan route guard;
- JSON contract test `/api/mobile/v1`;
- CSRF/session expiry dan return URL;
- idempotency key order creation;
- service-worker cache inspection;
- PWA install/update/standalone;
- desktop screenshot isolation;
- multi-browser mobile matrix.

## Deliverable dan Exit Criteria Fase 0

| Deliverable | Status |
| --- | --- |
| Route/page inventory | Selesai |
| API inventory dan gap | Selesai |
| Desktop ↔ mobile URL map | Selesai |
| Arsitektur SPA/API | Selesai sebagai rancangan |
| Trust boundary/threat model | Selesai sebagai rancangan |
| Conceptual shell prototype | Selesai; non-executable |
| Viewport/browser/performance budget | Selesai sebagai baseline awal |
| Feature flag/fallback/rollback plan | Selesai sebagai rancangan |
| Owner approval | Disetujui 9 November 2026 |
| Technical/QA approval | Menjadi quality gate pelaksanaan Fase 1 |

## Gate Review

### Keputusan yang Disetujui

**GO ke Fase 1** dengan keputusan berikut:

1. Same-origin session SPA menggunakan Sanctum; bearer token tidak disimpan di `localStorage`.
2. Manifest memakai `/app/manifest.webmanifest` dan service worker khusus scope `/app/`.
3. Kebijakan CORS mobile dipisahkan dan tidak menggunakan credentialed wildcard.
4. Guest cart penuh ditunda; Fase 4 minimum tetap mendukung guest `buy now` existing.
5. Initial bundle budget pada dokumen ini berlaku sebagai gate awal.
6. Bottom navigation awal: `Home`, `Katalog`, `Keranjang`, `Transaksi`, `Profil`.
7. `/app` tetap opt-in selama pilot dan tidak menggunakan redirect user-agent.
8. Idempotency key wajib tersedia sebelum order creation API dirilis.

### Keputusan yang belum menahan Fase 1 foundation

- Kapan `/app` menjadi default mobile setelah pilot.
- Berapa periode stabil sebelum mobile Blade dinyatakan legacy.
- Provider error monitoring produksi dan masa retensi datanya.
- Apakah wishlist menggantikan salah satu tab setelah tersedia data usage.

## Batas Perubahan Berikutnya

Setelah gate disetujui, Fase 1 hanya boleh membangun foundation: dependency, entry Vite terpisah,
shell, router, store, API client, session bootstrap, manifest/SW scope `/app`, feature flag, dan
observability dasar. Cart, checkout, payment, serta mutation produksi belum masuk Fase 1.
