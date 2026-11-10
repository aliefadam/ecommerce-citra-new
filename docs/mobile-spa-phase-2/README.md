# Fase 2 — Discovery dan Catalog Mobile

Status: **Selesai; Fase 3 belum dimulai**  
Tanggal penyelesaian: 9 November 2026  
PRD induk: [`../prd-mobile-storefront-spa-pwa.md`](../prd-mobile-storefront-spa-pwa.md)

## Hasil

Mobile SPA kini menyediakan discovery dan katalog dengan data Laravel nyata, tanpa membuka cart,
wishlist, checkout, order, atau payment.

Deliverable Fase 2:

- Home mobile berisi hero, banner internal aman, kategori, dan produk terbaru;
- pencarian nama, deskripsi, seller, kategori, SKU, varian, dan atribut teknis;
- autocomplete minimal dua karakter dengan debounce, cancellation, keyboard, dan status screen reader;
- kategori bertingkat dengan jumlah produk storefront-visible;
- listing dengan filter kategori, seller, stok, rentang harga, sorting, dan pagination berbasis URL;
- product card yang menampilkan gambar, kategori, nama, varian/SKU, harga, seller, dan status stok;
- detail produk dengan galeri, seller, varian, harga, deskripsi, spesifikasi, serta share/copy link;
- deep link `/app/search`, `/app/c/{slug}`, dan `/app/products/{slug}`;
- loading, empty, error/retry, offline global, focus management, dan scroll restoration;
- API katalog baru di `/api/mobile/v1` tanpa mengubah Open Company Catalog API existing.

## Kontrak API

| Endpoint | Kontrak utama |
| --- | --- |
| `GET /api/mobile/v1/home` | Banner, kategori ringkas, produk terbaru |
| `GET /api/mobile/v1/categories` | Tree kategori storefront-visible dan counts |
| `GET /api/mobile/v1/products` | Search/filter/sort/pagination, maksimal 24 item per halaman |
| `GET /api/mobile/v1/products/{slug}` | Detail, varian, atribut, dan spesifikasi |
| `GET /api/mobile/v1/search/suggestions` | Maksimal 8 saran, query 2–100 karakter |

Seluruh query memakai `Product::storefrontVisible()`, mensyaratkan varian produk, dan hanya memuat
perusahaan aktif. Resource menggunakan whitelist; angka stok, `company_id`, konfigurasi admin,
redeem point, dan field model internal tidak diekspos. Stok hanya dikirim sebagai boolean
`in_stock`. Input sort, harga, pagination, seller, serta kategori divalidasi dan page size dibatasi.

## Gate Fase 2

| Kriteria | Bukti | Status |
| --- | --- | --- |
| Search → detail | Autocomplete membuka detail dan direct reload berhasil | Lulus |
| Deep link dan URL state | Search, kategori, sort, stok, harga, dan page tersimpan di URL | Lulus |
| Back/scroll restoration | Posisi listing dipulihkan setelah kembali dari detail | Lulus |
| Loading/empty/error/retry | Skeleton, hasil kosong, API error, dan retry tersedia | Lulus |
| Visibility/security | Produk/perusahaan nonaktif 404; field sensitif tidak terserialisasi | Lulus |
| Pagination bounds | `per_page` dibatasi 1–24 dan sort memakai allowlist | Lulus |
| Viewport matrix | Chrome 360×800, 390×844, 430×932, dan 767×1024 tanpa overflow | Lulus |
| Desktop isolation | Regression storefront Blade existing tetap lulus | Lulus |
| Bundle budget | Initial JS 44,62 KB gzip; CSS 4,75 KB gzip; lazy route ≤2,02 KB gzip | Lulus |

Safari iOS, Chrome Android, standalone PWA, keyboard virtual, dan share sheet perangkat fisik tetap
memerlukan smoke test sebelum pilot. Automated gate menggunakan Chromium representatif.

## Verifikasi

- `php artisan test tests/Feature/MobileCatalogApiTest.php tests/Feature/MobileSpaFoundationTest.php tests/Feature/CompanyCatalogDynamicSpecificationTest.php`: 12 test, 128 assertion lulus.
- `npm run test:e2e -- tests/Browser/mobile-spa-catalog.spec.cjs`: 2 test lulus.
- `npm run test:e2e -- tests/Browser/mobile-spa-foundation.spec.cjs`: 1 test lulus.
- `npm run test:e2e -- tests/Browser/storefront-shell.spec.cjs`: 1 test lulus.
- `npm run build`: lulus.
- `npm audit --audit-level=high`: 0 vulnerability.
- `vendor/bin/pint --dirty --test`: lulus.
- `git diff --check`: lulus.

## Rollback dan Batas Scope

- Default `MOBILE_SPA_ENABLED=false` tetap dipertahankan.
- Versi aplikasi dinaikkan menjadi `catalog-2`; cache service worker menjadi
  `ecommerce-citra-mobile-catalog-v2`.
- Mematikan feature flag menutup shell dan API mobile tanpa mengubah storefront desktop.
- Tombol cart pada detail sengaja nonaktif dan menyatakan bahwa cart tersedia pada Fase 3.

## Remaining Issue

Remediation 20 advisory Composer dependency existing masih tertunda karena dua percobaan pembaruan
gagal menjangkau Packagist (`curl error 28`). Isu ini dan smoke test perangkat fisik harus ditutup
sebelum exposure pilot diperluas.

## Hardening Setelah Gate

Sebelum Fase 3, gate Fase 2 diperluas dengan autocomplete keyboard, API failure/retry, offline state,
broken-image inspection, regression Open Catalog API, storefront catalog, dan Lighthouse. Dua defect
mobile ditemukan dan ditutup: fallback gambar eksternal yang gagal serta kontras teks harga. Hasil
akhir Lighthouse Chromium untuk `/app`: accessibility 100, best practices 100, SEO 100, tanpa console
error. Dua assertion copy pada `StorefrontCatalogExperienceTest` tetap gagal karena teks desktop lama
sudah tidak ada; view desktop tidak diubah karena berada di luar scope mobile.

## Keputusan

Gate teknis Fase 2 dinyatakan **lulus**. Fase 3 (auth lifecycle lanjutan, cart, reconciliation, dan
wishlist) belum dimulai dan membutuhkan persetujuan scope berikutnya.
