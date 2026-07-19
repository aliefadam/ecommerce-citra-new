# PRD: API Katalog per Perusahaan (Company Catalog API)

> Status: **Implemented v2** (2026-07-19) — model **Open API** (publik, tanpa key). Endpoint produk & kategori sudah dibangun, diuji end-to-end, dan berjalan. Lihat §Status Implementasi dan `docs/api-catalog-usage.md`.
> Prasyarat: `prd-multi-company-foundation.md` (Fase 1 & skema `company_id` pada `products`). API ini mengonsumsi dimensi `company_id` yang diperkenalkan di sana.
> Scope rilis pertama: **read-only (GET), publik**, hanya **kategori** dan **produk**.

## Status Implementasi (2026-07-19)

Sudah diimplementasikan & diverifikasi (server `php artisan serve`, curl end-to-end):

- Routing: `routes/api.php` didaftarkan di `bootstrap/app.php` (`apiPrefix: 'api'`). Rate limiter `api` (120/menit per-IP) di `AppServiceProvider`.
- Controllers: `App\Http\Controllers\Api\V1\{ApiController, ProductController, CategoryController}`.
- Resources (whitelist field): `App\Http\Resources\Api\V1\{ProductListResource, ProductDetailResource, ProductVariantResource, CategoryResource}`.
- Helper gambar URL absolut: `App\Support\CatalogImageUrl`.
- CORS: `config/cors.php` (paths `api/*`, GET publik). Cache: middleware `cache.headers` (ETag + `max-age=300`) + cache server-side (`Cache::remember`, TTL 300s) di `ApiController`.
- Error API selalu JSON bersih (tanpa stack trace/nama model) via `withExceptions` di `bootstrap/app.php`.
- Taksonomi kategori yang diekspos = **MainCategory → CategoryDetail** (jalur `Category` self-referential kosong di data, tidak dipakai).

Terverifikasi: listing+paginasi, detail+varian (tanpa angka stok), isolasi antar perusahaan (produk PT lain → 404), filter `category_slug`/`main_category_id`/`category_detail_id`/`search`/`in_stock`, sort harga/nama, kategori flat + `parent_id` + `with_counts`, header `Cache-Control`/`ETag`/304/`X-RateLimit-*`/CORS, dan tidak ada kebocoran field `stock`/`is_redeem_product`/`redeem_points`/`company_id`.

## Ringkasan

Setiap perusahaan (PT) membutuhkan **feed katalog publik** agar produk & kategorinya bisa ditampilkan di **beberapa website eksternal** (mis. website katalog produk milik PT tersebut). Ini API **read-only (GET)** untuk keperluan display — bukan integrasi tulis, bukan data rahasia.

Keputusan bentuk: **Open API tanpa autentikasi/key.** Data katalog memang untuk tampil publik (sama seperti yang dilihat pengunjung storefront), sehingga tidak perlu API key. Sebagai gantinya, proteksi difokuskan pada **anti-abuse & beban server** (rate limit per-IP + caching), bukan kerahasiaan. Karena sistem sudah multi-perusahaan (dimensi `company_id` — lihat `prd-multi-company-foundation.md`):

- **Ter-scope per perusahaan lewat URL.** Perusahaan diidentifikasi lewat slug di path: `/api/v1/companies/{slug}/...`. Setiap response hanya berisi data perusahaan tersebut.
- **Read-only, publik.** Hanya endpoint baca kategori & produk. Tidak ada create/update/delete, tidak ada data customer/transaksi.
- **Tidak membocorkan data sensitif.** Stok (angka pasti), poin/redeem, HPP/margin, customer, transaksi — semua **tidak** diekspos.
- **Berdampingan dengan storefront existing.** Lapisan terpisah (`/api/v1/...`) di codebase yang sama; tidak mengubah alur web B2C existing.

## Tujuan

- Website eksternal bisa menampilkan katalog satu perusahaan tanpa perlu kredensial (cukup tahu slug perusahaan).
- Mengambil daftar **kategori** yang relevan untuk sebuah perusahaan (hanya kategori yang punya produk aktif milik perusahaan itu).
- Mengambil daftar & detail **produk aktif** milik satu perusahaan: nama, kategori, harga, gambar, atribut, dan status ketersediaan (`in_stock`).
- Data otomatis ter-scope ke perusahaan pada URL — mustahil "bocor" ke produk PT lain.
- Aman dari penyalahgunaan/beban: rate limit per-IP + caching, tanpa mengganggu konsumen wajar.
- API stabil & ter-versi (`/api/v1`) agar perubahan tidak merusak integrasi existing.

## Non-Tujuan

- **Bukan** API tulis: tidak ada pembuatan/ubah produk, order, apa pun lewat API pada fase ini.
- **Bukan** API lintas perusahaan: satu URL = satu perusahaan. Tidak ada endpoint katalog gabungan semua PT (itu domain storefront marketplace).
- **Tidak** mengekspos: stok (angka), poin/redeem, customer, transaksi, kupon, kredensial payment, stok movement, HPP/margin.
- Tidak ada autentikasi/API key/developer portal/OAuth pada model ini (sengaja open).
- Tidak membangun webhook/push; murni pull (request–response).
- Tidak menyediakan endpoint checkout/keranjang lewat API.

## Definisi

### Open API (Publik)

API tanpa autentikasi. Siapa pun dengan URL dapat membaca katalog. Proteksi = rate limit + caching (bukan key), karena datanya memang untuk display publik.

### Scope via Slug Perusahaan

Perusahaan ditentukan dari segmen `{slug}` pada URL (`companies.slug`). Semua query difilter `where('company_id', <company by slug>)`. Slug perusahaan nonaktif (`is_active = false`) → `404`.

## User Stories

### Owner / Tim Website

- Sebagai pemilik website katalog PT A, saya cukup memanggil `/api/v1/companies/pt-a/products` untuk menarik & menampilkan produk PT A — tanpa mengurus API key.
- Sebagai frontend developer, saya bisa memanggil API langsung dari browser (CORS aktif) untuk membangun halaman katalog statis/SPA.

### Konsumen API

- Sebagai konsumen, saya bisa mengambil semua kategori dan produk aktif milik satu perusahaan.
- Sebagai konsumen, saya bisa memfilter produk per kategori, mencari, dan melakukan paginasi.
- Sebagai konsumen, saya bisa mengambil detail satu produk (varian, harga, gambar, atribut, `in_stock`).
- Sebagai konsumen, saya mendapat error konsisten: `404` bila slug/produk tidak ada atau nonaktif, `429` bila melewati rate limit.

## Scope Functional

### 1. Model Akses: Open + Anti-Abuse

Tidak ada autentikasi. Sebagai gantinya:

- **Rate limit per-IP** yang longgar (usulan awal: **120 request/menit/IP**, throttle Laravel `throttle:...` keyed by IP). Melewati → `429` + header `Retry-After` & `X-RateLimit-*`. Tujuan: menahan scraping massal/DoS, bukan menghalangi website wajar.
- **Caching agresif** (proteksi beban paling efektif karena katalog jarang berubah):
  - HTTP: header `Cache-Control` (mis. `public, max-age=300`) + `ETag`/`Last-Modified` agar CDN/browser bisa cache.
  - Server-side: cache hasil query (mis. 1–5 menit) sehingga mayoritas request tidak menyentuh DB. Di-invalidate saat produk/kategori perusahaan berubah (atau cukup TTL pendek).
- **CORS** diaktifkan untuk `/api/v1/*` (mengizinkan pemanggilan dari browser website konsumen).
- Wajib HTTPS di produksi.

### 2. Versioning & Konvensi

- Prefix: `/api/v1`. Perusahaan lewat path: `/api/v1/companies/{slug}/...`. Perubahan breaking → `/api/v2`.
- Format JSON, `Content-Type: application/json`, timestamp ISO-8601 (UTC).
- Paginasi seragam (Laravel paginator): `?page=`, `?per_page=` (default 20, maks 100); amplop `data[]` + `meta` (`current_page`, `per_page`, `total`, `last_page`) + `links`.
- Amplop error konsisten: `{ "message": "...", "errors": { ... } }` dengan HTTP status sesuai (400/404/422/429/500).

### 3. Endpoint Kategori

Kategori bersifat **global** di sistem (lihat foundation PRD §2), namun API hanya mengembalikan node kategori yang **memiliki minimal satu produk aktif milik perusahaan pada URL** — agar konsumen tidak melihat cabang taksonomi kosong.

- `GET /api/v1/companies/{slug}/categories`
  - **Bentuk: flat + `parent_id`** (keputusan) — daftar kategori datar; tiap item membawa `parent_id`/`main_category_id`; konsumen merakit tree sendiri bila perlu.
  - Sesuai model existing (`MainCategory`, `CategoryDetail`, `Category`).
  - Param opsional: `?with_counts=true` untuk `products_count` (produk aktif PT ini per kategori).
  - Field per kategori: `id`, `name`, `slug`, `parent_id`/`main_category_id`, `image` (bila ada, URL absolut).
- `GET /api/v1/companies/{slug}/categories/{id_atau_slug}`
  - Detail satu kategori + daftar anak (bila ada).
  - `404` bila kategori tidak punya produk aktif milik perusahaan tersebut.

> Catatan: taksonomi memiliki 3 jalur (`main_category_id`, `category_detail_id`, `category_id`) pada `products`; rilis pertama mengeksposnya apa adanya. Penyatuan taksonomi di luar scope PRD ini.

### 4. Endpoint Produk

Hanya produk `status` aktif milik perusahaan pada URL.

- `GET /api/v1/companies/{slug}/products`
  - Paginasi. Param opsional:
    - `?category_id=` / `?category_slug=`, `?main_category_id=`, `?category_detail_id=` — filter taksonomi.
    - `?search=` — cari pada `name` (opsional SKU varian).
    - `?sort=` — `newest` (default), `name_asc`, `name_desc`, `price_asc`, `price_desc`.
    - `?in_stock=true` — hanya produk yang tersedia.
  - Field ringkas per produk: `id`, `name`, `slug`, `category` (ringkas), `image` (gambar utama via `firstAvailableImagePath()`, URL absolut), `price_min`/`price_max` (dari varian), `in_stock` (boolean agregat), `created_at`.
- `GET /api/v1/companies/{slug}/products/{id_atau_slug}`
  - Detail: `id`, `name`, `slug`, `description`, `category`/`main_category`/`category_detail`, daftar **varian**.
  - Per varian (`ProductVariant`): `id`, `sku`, `price` (**harga normal varian**, tanpa flash sale), `image` (URL absolut), `weight_grams`, dimensi (`length_cm`, `width_cm`, `height_cm`), atribut (via `attributeValues` → `attributeSummary()` / pasangan `code:value`), dan **`in_stock` (boolean)**.
  - **`stock` (angka pasti) TIDAK diekspos** — hanya `in_stock` boolean (keputusan). Mencegah kebocoran kecepatan penjualan/inventori ke kompetitor.
  - Field poin **tidak diekspos**: `is_redeem_product` & `redeem_points` disembunyikan. Produk redeem tetap muncul sebagai produk biasa.
  - `404` bila produk tidak ada, nonaktif, atau milik perusahaan lain — tidak membedakan "tidak ada" vs "milik PT lain".

## Data Requirements

### Tidak ada tabel/kolom baru

Model Open API **tidak** memerlukan tabel kredensial. API membaca `products`, `product_variants`, `product_variant_attributes`, `categories`, `main_categories`, `category_details`, `companies` apa adanya. Prasyarat: `products.company_id` & `companies.slug` sudah ada (dari foundation PRD).

### API Resource classes (whitelist field)

- `CategoryResource`, `ProductResource` (ringkas), `ProductDetailResource`, `ProductVariantResource` — mengontrol **secara eksplisit** field yang keluar. Penting untuk model open: pastikan `stock`, `is_redeem_product`, `redeem_points`, `company_id` mentah, dan kolom internal lain **tidak** ikut. Jangan `toArray()` model mentah.
- Turunan: `in_stock` = `stock > 0` (dihitung di resource, `stock` sendiri tidak dirender).

## Keamanan

Karena open, fokus keamanan bergeser dari "siapa yang boleh akses" ke "membatasi dampak abuse & kebocoran data":

- **Rate limit per-IP + caching** (lihat §1) sebagai pertahanan utama terhadap scraping massal/DoS.
- **Whitelist field via API Resource** — jangan bocorkan stok angka, poin, atau kolom internal.
- **Scope by slug** — validasi kepemilikan `company_id` di query; produk PT lain → `404` (tanpa enumeration).
- Hanya **produk aktif & perusahaan aktif** yang tampil; nonaktif → `404`.
- Wajib HTTPS.
- Gambar dikembalikan sebagai **URL absolut** (prefix base URL storage) agar bisa langsung dipakai website eksternal.
- Kesadaran bisnis: harga & katalog jadi mudah di-scrape kompetitor secara terstruktur. Ini **konsekuensi diterima** dari keputusan open API (data toh sudah tampil di storefront); dicatat agar tidak mengejutkan.

## Acceptance Criteria

- [ ] `GET /api/v1/companies/{slug}/products` mengembalikan hanya produk **aktif** milik perusahaan slug tsb, dengan paginasi, filter kategori, search, dan sort berfungsi.
- [ ] `{slug}` perusahaan nonaktif atau tidak ada → `404`.
- [ ] `GET /api/v1/companies/{slug}/categories` hanya mengembalikan kategori yang punya produk aktif milik perusahaan tsb, dalam bentuk flat + `parent_id`.
- [ ] Detail produk menampilkan varian dengan harga, gambar, atribut, dan `in_stock` — **tanpa** angka `stock`.
- [ ] Response **tidak** memuat: `stock` angka, `is_redeem_product`, `redeem_points`, `company_id` mentah, atau field sensitif lain.
- [ ] Melewati rate limit per-IP → `429` dengan `Retry-After`.
- [ ] Response menyertakan header cache (`Cache-Control`/`ETag`) dan dapat dilayani dari cache tanpa menyentuh DB pada hit.
- [ ] CORS aktif: endpoint bisa dipanggil dari browser website konsumen.
- [ ] Produk milik PT lain lewat slug berbeda tidak pernah muncul (uji dua perusahaan, tidak tumpang tindih).
- [ ] Kontrak `/api/v1` terdokumentasi (OpenAPI/Postman) dan tidak berubah breaking tanpa naik versi.

## Edge Cases

- Produk aktif tapi semua varian `stock = 0` → tetap muncul dengan `in_stock = false` (kecuali dipfilter `?in_stock=true`).
- Produk tanpa varian → `price_min/max` null, `in_stock = false`; tetap dikembalikan.
- Perusahaan dinonaktifkan → slug-nya `404` untuk semua endpoint (katalog hilang dari API seketika).
- Slug produk sama antar perusahaan (foundation: `unique(company_id, slug)`) → karena API sudah ter-scope by company slug, lookup by product slug tetap unik dalam konteks itu.
- Kategori global tanpa produk aktif untuk PT ini → tidak muncul di `/categories` dan `404` di detail.
- Gambar path relatif → dikembalikan sebagai URL absolut.
- Traffic lonjakan/scraping → ditahan rate limit per-IP + cache; storefront & admin tidak ikut terbebani.

## Keputusan Terkonfirmasi (2026-07-19)

- **Bentuk API**: **Open API publik, tanpa autentikasi/key.** Proteksi = rate limit per-IP + caching, bukan key.
- **Identifikasi perusahaan**: lewat **slug di URL** (`/api/v1/companies/{slug}/...`).
- **Rate limit**: **tetap ada** (per-IP, longgar) — bukan soal auth, tapi anti-abuse/beban server.
- **Stok**: **angka `stock` tidak ditampilkan**; hanya `in_stock` boolean.
- **Harga**: selalu **harga normal varian**, tanpa flash sale pada rilis pertama.
- **Poin/redeem**: `is_redeem_product` & `redeem_points` **disembunyikan**.
- **Struktur kategori**: **flat + `parent_id`**.

## Open Questions

Non-blocking (bisa diputuskan sambil implementasi):

- Angka rate limit per-IP final (120/menit hanya usulan awal).
- TTL cache final (usulan 5 menit) dan apakah perlu invalidasi aktif saat produk berubah, atau cukup TTL pendek.
- Perlukah `?in_stock=true` jadi default (produk habis disembunyikan otomatis) atau opsional seperti sekarang.

## Rekomendasi Urutan Implementasi

1. **Skeleton API**: route group `/api/v1/companies/{slug}/...` + resolusi company by slug (404 bila nonaktif/tidak ada), amplop respons & error handler seragam, CORS.
2. **Endpoint produk** (listing + detail) dengan API Resource whitelist (pastikan `stock`/poin tidak keluar, `in_stock` diturunkan), filter/search/sort/paginasi, URL gambar absolut.
3. **Endpoint kategori** (relevansi per company, flat + `parent_id`, optional counts).
4. **Anti-abuse**: rate limit per-IP + caching (HTTP header + server-side) + invalidasi/TTL.
5. **Dokumentasi kontrak** (OpenAPI/Postman) + test scope-isolation dua perusahaan & test bahwa field sensitif tidak bocor.
6. (Fase lanjut) flash-sale-aware pricing, dan — bila kelak butuh akses privat/tulis — baru pertimbangkan lapisan autentikasi (mis. token per perusahaan) di atas model ini.
```