# Audit produk dinamis dan kualitas customer

Tanggal: 27 September 2026. Status: audit sebagian selesai; release/sign-off belum lulus.

## Bukti pengujian

- `php artisan test --compact tests/Feature/DynamicProductSpecificationTest.php tests/Feature/FrontendCustomerBaselineTest.php tests/Unit/PlaywrightHarnessPolicyTest.php`: 13 test, 84 assertion lulus.
- Baseline dengan rincian query berulang dijalankan ulang: 1 test, 8 assertion lulus.
- E2E admin-product-create, catalog-api, customer-quality-audit, product-to-cart: 6 lulus, 1 gagal. API gagal pada assertion rate-limit 429; assertion kontrak dan privasi sebelumnya berhasil.
- Audit tambahan setelah perekaman kontras: `npm run test:e2e -- tests/Browser/customer-quality-audit.spec.cjs`: 4/4 lulus.
- Kegagalan API: 121 request berurutan tidak memperoleh 429; skenario memakan lebih dari satu menit, sedangkan konfigurasi limiter 120/menit. Pergantian window merupakan hipotesis, belum diagnosis final. Jangan melonggarkan limiter atau menyebut seluruh API E2E lulus.

## Query dan performance

| Halaman | Query | Bentuk berulang | LCP lab ms | CLS lab |
| --- | ---: | ---: | ---: | ---: |
| Homepage | 17 | 1 | 2684 | 0.093881 |
| Kategori | 13 | 0 | 2200 | 0.000319 |
| Detail produk | 47 | 11 | 2804 | 0.000372 |

Lihat [query-report.json](query-report.json). Detail mengulang batch eager-load company/category/variant di beberapa blok; duplikasi tidak otomatis berarti N+1. Uji pertumbuhan jumlah produk/varian masih diperlukan sebelum checklist bebas N+1 ditutup.

Asset build yang tersedia: CSS 34.664 byte gzip (budget 120.000), JS aplikasi 5.211 byte gzip (budget 220.000). Build tidak dijalankan ulang karena audit tidak mengubah asset aplikasi.

LCP/CLS di atas adalah satu observasi Chrome lokal sampai screenshot diambil, tanpa throttling dan tanpa data lapangan. Homepage/detail melampaui target LCP 2.500 ms pada sampel ini. INP dan p75 produksi belum diukur; hasil ini tidak membuktikan Web Vitals produksi lulus.

## Accessibility dan screenshot

- Homepage, kategori, detail: tidak overflow horizontal pada 360x800 dan 360x400.
- Reflow 360x400 mewakili layout CSS layar 720x800 pada zoom 200%; bukan pengujian tombol zoom browser atau keyboard virtual perangkat.
- Menu kategori dapat dicapai dengan Tab, dibuka Enter, ditutup Escape, dan fokus kembali ke trigger.
- Preferensi reduced motion aktif; sampel durasi CSS tercatat di `audit.json`. Seluruh animasi JavaScript belum diaudit.
- Sampling kontras hanya teks leaf dengan warna solid; background image, transparansi kompleks, pseudo-element, clipping, serta overlap dapat memerlukan review manual. Kandidat bukan vonis WCAG.
- Kandidat kontras: homepage 8/98 sampel, kategori 15/77, detail 21/75. Contoh counter produk memiliki rasio 2,04 terhadap ambang teks normal 4,5. Audit penuh contrast/focus/keyboard seluruh halaman tetap terbuka.

Artefak terbaru:

- [Homepage 360px](customer-quality-audit-quality-audit-360px-and-reflow-home/home-360.png) dan [hasil lab](customer-quality-audit-quality-audit-360px-and-reflow-home/audit.json).
- [Kategori 360px](customer-quality-audit-qua-20f5f-it-360px-and-reflow-catalog/catalog-360.png) dan [hasil lab](customer-quality-audit-qua-20f5f-it-360px-and-reflow-catalog/audit.json).
- [Detail 360px](customer-quality-audit-quality-audit-360px-and-reflow-detail/detail-360.png) dan [hasil lab](customer-quality-audit-quality-audit-360px-and-reflow-detail/audit.json).
- Screenshot reflow 200% disimpan bersama masing-masing artefak.

## Review kategori ambigu

Inventory aktual belum dapat dibaca: koneksi MySQL lokal 127.0.0.1:3306 ditolak. Skrip read-only `php scripts/audit-category-mapping.php` tersedia untuk dijalankan setelah database aktif. Tidak ada mapping aktual yang diubah atau disetujui.

Nama klem/clamp/bracket saja tidak cukup untuk memilih Bolt/Pipe/Valve/Flange/Nut. Rekomendasi review: periksa contoh produk dan atribut wajib, kemudian pilih template sesuai fungsi produk atau template khusus. Periksa fallback Main Category agar kategori ambigu tidak diam-diam memperoleh template yang salah. Owner/admin perlu menyetujui mapping berdasarkan inventory aktual.

## Gate tersisa

- Inventory/mapping kategori aktual setelah MySQL tersedia.
- Uji pertumbuhan query untuk katalog/detail.
- Diagnosis rate-limit E2E, review kandidat kontras, seluruh focus/keyboard, zoom browser dan keyboard perangkat.
- Data Web Vitals produksi dan validasi ulang LCP.
- Manual book lengkap DOCX belum diregenerasi; [addendum customer](../../manual-book/customer-quality-addendum.md) tersedia.
- Sign-off owner: **menunggu**, tidak diwakili oleh hasil test atau audit ini.
