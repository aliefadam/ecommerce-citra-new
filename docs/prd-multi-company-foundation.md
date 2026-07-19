# PRD: Fondasi Multi-Perusahaan (Multi-Company Foundation)

> Status: Siap dieksekusi — Fase 1 & Fase 2 sudah lengkap dengan keputusan bisnis & teknis (v2, 2026-07-19). Sisa Open Questions non-blocking (WA gateway, onboarding data perusahaan baru).
> Prasyarat untuk: `prd-quotation-to-invoice-b2b.md` (perlu revisi company-aware setelah PRD ini disetujui)

## Ringkasan

Sistem saat ini melayani satu perusahaan (BOQ). Pemilik bisnis memiliki beberapa perusahaan (PT) dan ingin semuanya dikelola lewat **satu sistem dan satu admin panel** — ini **bukan** multi-tenant SaaS; semua perusahaan milik satu grup pemilik yang sama.

Keputusan utama:

- **Satu database, satu codebase.** Setiap data operasional diberi dimensi `company_id`. Tidak ada pemisahan database/subdomain per perusahaan.
- **Katalog & stok per perusahaan.** Satu produk dimiliki tepat satu perusahaan. Tidak ada produk bersama lintas perusahaan; jika dua PT menjual barang serupa, masing-masing punya record produk & stok sendiri.
- **Storefront tunggal bergaya marketplace.** Satu situs B2C menampilkan katalog gabungan semua perusahaan. Customer cukup satu akun untuk belanja di semua perusahaan.
- **Checkout per perusahaan.** Keranjang dikelompokkan per perusahaan; satu `Transaction` selalu milik tepat satu perusahaan. Ongkir dihitung dari origin milik perusahaan terkait.
- **RBAC dua dimensi: role (apa yang boleh) × company scope (di perusahaan mana).** Struktur permission `module.action` yang sudah ada dipertahankan; yang baru adalah penugasan role per perusahaan. Satu admin bisa mengelola PT A+B tapi tidak PT C, atau mengelola produk PT A tanpa bisa melihat report.
- **Report per perusahaan + konsolidasi.** Semua report existing mendapat filter perusahaan; report konsolidasi lintas perusahaan menjadi permission terpisah (khusus owner/direksi).
- **Data existing dimigrasikan sebagai perusahaan pertama (BOQ)** — backfill `company_id` lalu dikunci NOT NULL. Tidak ada perubahan perilaku bagi user selama hanya ada satu perusahaan aktif.

## Tujuan

- Owner bisa mendaftarkan perusahaan baru (nama, legal, logo, alamat, kontak, NPWP) dan menonaktifkannya tanpa menghapus data historis.
- Setiap produk, varian, stok, lokasi gudang/origin, transaksi, kupon, dan flash sale terikat ke tepat satu perusahaan.
- Customer dengan satu akun bisa browsing katalog gabungan, menambahkan produk lintas perusahaan ke keranjang, dan checkout terpisah per perusahaan.
- Admin panel punya **company switcher**: semua halaman modul beroperasi dalam konteks perusahaan aktif yang dipilih.
- Super admin bisa menugaskan admin ke satu/beberapa/semua perusahaan, dengan role berbeda per perusahaan bila perlu.
- Report bisa dilihat per perusahaan (sesuai scope admin) dan konsolidasi (permission khusus).
- Penomoran dokumen (invoice, dan nantinya dokumen B2B) unik **per perusahaan**, dengan prefix per perusahaan.
- Pengaturan operasional (rekening pembayaran, origin pengiriman, identitas di invoice/faktur) menjadi per perusahaan.

## Non-Tujuan

- Bukan multi-tenant SaaS: tidak ada registrasi perusahaan mandiri, tidak ada isolasi database, tidak ada billing antar tenant.
- Tidak membangun storefront/branding terpisah per perusahaan (domain/subdomain) pada fase ini.
- Tidak membangun transfer stok antar perusahaan (antar-PT dianggap jual-beli terpisah; bila dibutuhkan, jadi fitur lanjutan).
- Tidak mengubah alur dokumen B2B — itu domain `prd-quotation-to-invoice-b2b.md` yang akan direvisi menyusul.
- Tidak menggabungkan pembayaran dua perusahaan dalam satu tagihan/settlement.

## Definisi

### Company (Perusahaan)

Entitas PT milik grup pemilik. Menjadi pemilik katalog, stok, transaksi, promo, pengaturan operasional, dan (nantinya) dokumen B2B. Contoh: BOQ (perusahaan pertama, hasil migrasi data existing).

### Company Scope (Penugasan Admin)

Relasi user-admin ↔ perusahaan ↔ role. Menentukan *di perusahaan mana* seorang admin aktif dan *dengan role apa*. Scope `semua perusahaan` tersedia untuk owner/direksi.

### Company Switcher

Dropdown di admin panel untuk memilih konteks perusahaan aktif. Semua query modul otomatis ter-scope ke perusahaan aktif. Pilihan "Semua Perusahaan" hanya muncul untuk report/dashboard bagi yang berwenang.

## User Stories

### Owner / Super Admin

- Sebagai owner, saya bisa menambah/mengubah/menonaktifkan perusahaan.
- Sebagai owner, saya bisa menugaskan seorang admin ke PT A dan PT B dengan role berbeda, tanpa akses ke PT C.
- Sebagai owner, saya bisa melihat report konsolidasi semua perusahaan maupun drill-down per perusahaan.
- Sebagai owner, saya bisa memastikan admin tertentu tidak bisa melihat report sama sekali (role tanpa permission `reports.*`).

### Admin Perusahaan

- Sebagai admin yang ditugaskan ke PT A dan PT B, saya bisa berpindah konteks lewat company switcher dan hanya melihat data perusahaan yang saya pegang.
- Sebagai admin, saya tidak bisa melihat, mencari, atau mengubah data perusahaan di luar scope saya — termasuk lewat manipulasi URL/ID.
- Sebagai admin katalog PT A, saya mengelola produk & stok PT A tanpa melihat penjualan/report.

### Customer (B2C)

- Sebagai customer, saya browsing satu situs berisi produk semua perusahaan, dengan identitas perusahaan/toko terlihat di halaman produk.
- Sebagai customer, keranjang saya otomatis terkelompok per perusahaan, dan saya checkout per kelompok (satu pembayaran per perusahaan).
- Sebagai customer, riwayat pesanan saya menampilkan pesanan dari semua perusahaan dalam satu daftar, dengan label perusahaan.

## Scope Functional

### 1. Master Perusahaan

- CRUD perusahaan: `name`, `slug`, `legal_name`, `logo`, `address`, `phone`, `email`, `npwp` (opsional), `invoice_prefix`, `is_active`, `sort_order`.
- Nonaktif = disembunyikan dari storefront & tidak bisa transaksi baru; data historis tetap utuh.
- Permission baru: `companies.index/create/edit/delete` (default hanya super admin).

### 2. Scoping Data per Perusahaan

Tabel yang mendapat `company_id` (NOT NULL setelah backfill):

- Katalog: `products` (varian, atribut, stok, `stock_movements` mengikuti lewat relasi), `flash_sales`, `coupons`.
- Operasional: `store_locations`, `transactions` (detail mengikuti), `return_requests` (mengikuti transaksi).
- Pengaturan: `store_settings` → dipecah menjadi **pengaturan global situs** dan **pengaturan per perusahaan**, dengan daftar key eksplisit (hasil audit key existing di `BackendController::updateSettings`):
  - **Per perusahaan** → `company_settings`: `manual_payment_bank_name`, `manual_payment_account_number`, `manual_payment_account_name`, `manual_payment_instruction`, `tax_enabled`, `tax_name`, `tax_rate`. `store_logo_path` dipindah jadi kolom `companies.logo_path`.
  - **Tetap global**: `social_*` (9 link medsos — satu identitas marketplace), `store_name` (nama situs marketplace, beda dari `companies.name`/`legal_name` per PT), `wa_gateway_*` (satu nomor WA gateway untuk semua PT — **perlu konfirmasi owner** bila ternyata tiap PT ingin nomor WA sendiri).
  - **Kredensial payment gateway (secret, bukan setting biasa)** → tabel terpisah `company_payment_credentials`, lihat §Data Requirements & §Payment Gateway di bawah.

Tetap **global** (bukan per perusahaan) — **diputuskan**: `users` (customer & admin), `addresses`, kategori/taksonomi (`main_categories`, `categories`, `category_details`) sebagai navigasi marketplace bersama (konsisten untuk pengalaman satu marketplace), wishlist & review (tetap per produk, tidak butuh dimensi company terpisah), `newsletter_*`, `content_pages`, `banners` (dengan kolom opsional `company_id` nullable untuk banner promosi satu perusahaan).

**Poin & member tier — diputuskan: tetap global** (1 saldo poin/tier lintas semua PT, bukan per perusahaan). Konsekuensi: poin yang didapat dari belanja di PT A bisa di-redeem untuk produk PT B — PT penerima redeem menanggung nilai diskon yang "dihasilkan" transaksi PT lain. Ini harus didokumentasikan sebagai **kebijakan cost-sharing internal antar-PT** (mis. dicatat sebagai beban promosi lintas entitas saat rekonsiliasi bulanan), bukan sekadar detail teknis — perlu sign-off bagian keuangan sebelum go-live Fase 2.

### 3. RBAC Dua Dimensi

- `admin_roles` existing **dipertahankan apa adanya** (flat `module.action` JSON) — role tetap mendefinisikan *kemampuan*.
- Tabel baru `admin_company_assignments`: `user_id`, `company_id` (nullable = semua perusahaan), `admin_role_id`, unique per (user, company). Menggantikan `users.admin_role_id` tunggal (kolom lama di-backfill menjadi satu assignment `company_id = NULL`, lalu deprecated).
- `User::hasAdminPermission(string $permission, ?int $companyId = null)`: super admin (`role = 'admin'`) lolos semua; selainnya dicek terhadap assignment yang cocok (assignment `company_id NULL` berlaku untuk semua perusahaan). Saat `$companyId` tidak diberikan, default ke perusahaan aktif di session — ini menjaga backward-compatibility untuk ≥6 call site existing (`AdminPermission` middleware, `SalesReportController`, `AdminTaxInvoiceController`, beberapa view) yang belum di-update untuk pass `$companyId` secara eksplisit; migrasi call site dilakukan bertahap per modul di Fase 2, bukan sekaligus di Fase 1.
- Middleware/global scope memastikan seluruh query admin ter-scope ke perusahaan aktif dan perusahaan aktif ∈ scope user (guard terhadap manipulasi ID/URL — validasi kepemilikan `company_id` di setiap route model binding).
- Permission report baru: `reports.consolidated` untuk tampilan lintas perusahaan.

### 4. Storefront Marketplace & Checkout

- Katalog gabungan: listing, pencarian, dan filter menampilkan produk semua perusahaan aktif; kartu & halaman produk menampilkan nama perusahaan/toko.
- Keranjang: item disimpan seperti sekarang, ditampilkan terkelompok per perusahaan (`company_id` diturunkan dari produk).
- Checkout per perusahaan: satu proses checkout mengeksekusi **satu perusahaan**; kupon hanya berlaku untuk perusahaan penerbitnya; ongkir dihitung dari `store_locations` perusahaan terkait; PPN/faktur pajak memakai identitas perusahaan terkait.
- Riwayat pesanan customer menampilkan label perusahaan; notifikasi & email transaksi memakai identitas perusahaan pengirim.
- **Email pengirim — diputuskan**: 1 domain/mailer teknis bersama (SMTP config tidak berubah per PT), hanya *display name* pengirim yang mengikuti `companies.name` per transaksi (mis. `"PT A" <noreply@domain-marketplace.com>`). Menghindari dependency setup domain+DNS (SPF/DKIM) per PT baru yang bisa memblokir go-live, mirip masalah approval Midtrans di §4b.

#### 4a. Kondisi Kode Saat Ini (baseline, hasil audit teknis)

Penting sebagai konteks estimasi: **belum ada konsep company sama sekali di codebase** — bukan cuma belum dipakai, field/model-nya memang belum ada (`grep -rniI "company|tenant_id" app database` nihil). Fase 2 adalah penambahan model data + refactor lintas modul yang menyentuh banyak titik, bukan sekadar tambah filter `where` di query yang sudah ada. Temuan kunci:

- **Cart** adalah tabel DB (`Cart` model, `database/migrations/2026_05_03_150000_create_carts_table.php`), unik per (`user_id`, `product_variant_id`) — bukan session. Derive `company_id` per item cukup lewat `productVariant.product.company_id`, tidak perlu ubah struktur `carts`.
- **Checkout saat ini SELALU 1 request → 1 Transaction**, di kedua jalur pembayaran: Midtrans (`MidtransController::createCharge`, 1 `orderId` per call) dan manual transfer (`ManualPaymentController::checkout`, sama). Payload endpoint (`items[]`, `shipping_cost`, `address_id`) sudah cukup generik untuk dipanggil N kali (sekali per grup perusahaan) tanpa mengubah signature endpoint — tapi session checkout (`CartController.php:141-146`, key `checkout`/`checkout_coupon`/`checkout_waiting.{orderId}`) saat ini single-bundle flat, harus direstruktur jadi keyed-by-company.
- **Ongkir**: `RajaOngkirController::shippingOptions()` (`RajaOngkirController.php:79-83`) hardcode ambil **1** `store_locations` yang paling baru & aktif secara global (`->latest('id')->first()`), bukan per company. Perlu: `company_id` di `store_locations`, method menerima parameter company, query diubah dari `latest()->first()` global menjadi `where('company_id', $id)`.
- **Kupon**: tabel & logic (`Coupon.php:29-66`) sepenuhnya global, dihitung dari `$subtotal` flat gabungan seluruh cart — dipakai di 3 titik (`CheckoutCouponController::apply`, `MidtransController::createCharge`, `ManualPaymentController::checkout`). Perlu `company_id` nullable di `coupons`, dan ketiga titik ini dihitung ulang **per grup company** (bukan subtotal total), session `checkout_coupon` jadi keyed-by-company.
- **Listing/search produk** (`FrontendController::index/kategori/search`) sudah pakai Eloquent query builder standar tanpa cache layer — **paling siap**, tinggal tambah `where`/`whereIn('company_id', ...)`.
- **Order history customer** (`FrontendController::profil`, query `Transaction::where('user_id', ...)`) generik, tinggal eager-load relasi `company` — **low risk**.
- **Pajak & payment info**: `StoreSetting` saat ini satu baris key-value global (`tax_rate`, rekening manual payment, dst — lihat §2). Ini sudah diputuskan pindah ke `company_settings` per perusahaan; `CheckoutTaxCalculator::calculate()` dan kedua controller pembayaran perlu menerima `company_id` untuk resolve setting yang benar.

**Ringkasan effort per area:** skema data (sedang), cart/session grouping (sedang), checkout→Transaction N-kali (sedang, area risiko tertinggi karena menyentuh 2 payment controller + halaman waiting), ongkir per company (kecil-sedang), kupon per company (sedang), listing/search (kecil), order history (kecil), tax/store settings (sedang).

#### 4b. Alur Checkout Multi-Perusahaan — diputuskan: pola marketplace

Klik "Checkout" pada keranjang berisi produk dari N perusahaan **langsung membuat N `Transaction`** (satu per perusahaan), masing-masing berstatus "menunggu pembayaran" — pola yang sama seperti Shopee/Tokopedia. Customer diarahkan ke daftar pesanan dan bisa membayar tiap pesanan kapan pun (langsung semua sekaligus, atau satu dulu, sisanya nanti). Kegagalan/kadaluarsa pembayaran salah satu perusahaan **tidak memengaruhi** pesanan perusahaan lain.

Implikasi teknis (berdasarkan baseline di §4a):
- Endpoint checkout (Midtrans charge & manual transfer) dipanggil **berurutan N kali** dari sisi backend saat tombol "Checkout" ditekan (bukan di-loop dari frontend) — server membuat N `Transaction` sekaligus dalam satu request, masing-masing dengan `orderId`, kupon, ongkir, dan pajak yang dihitung khusus untuk grup perusahaan itu.
- Halaman `checkout-waiting` (`MidtransController.php:283-356`, saat ini didesain untuk 1 `orderId`) diganti/diperluas jadi **halaman daftar pesanan** yang menampilkan status per perusahaan, masing-masing dengan tombol bayar/instruksi transfer sendiri.
- Snap popup Midtrans dipicu per pesanan saat customer memilih "Bayar" pada salah satu kartu pesanan — bukan otomatis berantai.
- Tidak perlu mekanisme rollback lintas-company bila satu gagal, karena masing-masing `Transaction` independen sejak awal (selaras dengan Non-Tujuan: tidak menggabungkan pembayaran dua perusahaan).

### 4b. Payment Gateway per Perusahaan

Masalah nyata yang harus diantisipasi: situs bisa live untuk perusahaan baru sebelum merchant Midtrans PT tersebut disetujui (proses approval Midtrans per legal entity bisa makan waktu). Keputusan:

- **Manual transfer aktif per-company sejak hari pertama**, tanpa syarat approval apa pun — ini sudah ada sebagai metode pembayaran existing (`manual_transfer` di checkout, lihat `resources/views/frontend/checkout.blade.php:1628`), tinggal dibuat company-aware lewat `company_settings` (`manual_payment_bank_name/account_number/account_name/instruction` per perusahaan).
- **Midtrans bersifat opt-in per perusahaan**: kredensial (`server_key`, `client_key`) disimpan di tabel terpisah `company_payment_credentials` (bukan `company_settings` — lihat alasan keamanan di §Data Requirements). Selama kredensial suatu perusahaan belum diisi/lengkap, opsi Midtrans **disembunyikan** dari checkout perusahaan itu (bukan memblokir seluruh checkout) — customer tetap bisa checkout via manual transfer.
- **Tidak ada shared/fallback merchant account lintas perusahaan** secara default: dana milik PT B tidak pernah masuk ke rekening merchant Midtrans PT A. Ini konsisten dengan Non-Tujuan ("tidak menggabungkan pembayaran dua perusahaan").
- Aturan minimal: setiap perusahaan aktif harus punya **minimal satu** metode pembayaran terkonfigurasi (manual transfer sudah otomatis tersedia begitu `manual_payment_*` diisi saat setup perusahaan) sebelum katalognya ditampilkan di storefront.
- Keamanan kredensial: kolom secret di `company_payment_credentials` pakai Eloquent `encrypted` cast; permission terpisah `companies.credentials.manage` (bukan `companies.edit`); UI hanya menampilkan versi masked dan form "ganti key" (write-only, tidak pernah render key asli).

### 5. Report

- Semua report existing mendapat parameter `company_id`, default = perusahaan aktif di switcher.
- Report konsolidasi (semua perusahaan, breakdown per perusahaan) di balik `reports.consolidated`.
- Export (Excel) menyertakan kolom/label perusahaan.

### 6. Penomoran Dokumen

- Nomor invoice/transaksi unik per perusahaan memakai `invoice_prefix` perusahaan (mis. `BOQ-INV-2026-0001`).
- Service penomoran dibuat terpusat dan company-aware — sekaligus menjadi fondasi penomoran dokumen B2B (selaras dengan Rekomendasi MVP di PRD B2B).
- **Faktur pajak — diputuskan: seri nomor per NPWP**, bukan satu seri global. `companies.npwp` wajib diisi untuk perusahaan yang berstatus PKP; `transaction_tax_invoices` mendapat kolom `company_id` dan nomor seri di-generate per `company_id` (bukan per aplikasi), memakai service penomoran yang sama di atas. Ini kebutuhan hukum (nomor faktur pajak terikat ke NPWP penerbit di PPN), bukan sekadar preferensi.

## Data Requirements

### Tabel `companies`

`id`, `name`, `slug` (unique), `legal_name`, `logo_path`, `address`, `phone`, `email`, `npwp` (nullable), `invoice_prefix` (unique), `is_active`, `sort_order`, timestamps.

### Tabel `admin_company_assignments`

`id`, `user_id` (FK users), `company_id` (FK companies, nullable = semua), `admin_role_id` (FK admin_roles), timestamps, unique (`user_id`, `company_id`).

### Tabel `company_settings`

`id`, `company_id` (FK), `key`, `value` (json), unique (`company_id`, `key`) — menampung pengaturan non-secret per perusahaan hasil pemecahan `store_settings`. Key yang dipindah ke sini: `manual_payment_bank_name`, `manual_payment_account_number`, `manual_payment_account_name`, `manual_payment_instruction`, `tax_enabled`, `tax_name`, `tax_rate`. Key yang **tetap global** di `store_settings`: `social_*`, `store_name`, `wa_gateway_*`.

### Tabel `company_payment_credentials`

`id`, `company_id` (FK, unique per provider), `provider` (mis. `midtrans`), `server_key` (encrypted), `client_key` (encrypted), `is_production`, `is_active`, timestamps. Terpisah dari `company_settings` karena berisi secret — lihat §4b Payment Gateway. Permission akses: `companies.credentials.manage`.

### Kolom `company_id` (FK companies, NOT NULL setelah backfill)

`products`, `flash_sales`, `coupons`, `store_locations`, `transactions`. `banners.company_id` nullable. Unique constraint slug produk berubah dari `unique(slug)` menjadi `unique(company_id, slug)` — lihat §Edge Cases untuk keputusan URL.

## Migrasi Data

1. Buat `companies`, seed perusahaan pertama **BOQ** dari data `store_settings` existing.
2. Tambah `company_id` nullable ke tabel target → backfill semua record ke BOQ → ubah NOT NULL + index.
3. Backfill `users.admin_role_id` → `admin_company_assignments` (`company_id = NULL`).
4. Pecah `store_settings` → global vs `company_settings` BOQ.
5. Verifikasi: seluruh test existing hijau; smoke test checkout, report, dan permission dengan 1 perusahaan (perilaku harus identik dengan sebelum migrasi).

## Acceptance Criteria

- [ ] Owner bisa membuat perusahaan kedua dan menugaskan admin dengan role berbeda per perusahaan.
- [ ] Admin ber-scope PT A tidak bisa mengakses data PT B lewat UI maupun manipulasi URL/ID (403/404).
- [ ] Produk & stok hanya muncul dan bisa dikelola dalam konteks perusahaan pemiliknya.
- [ ] Customer bisa checkout produk dari dua perusahaan sebagai dua transaksi terpisah, masing-masing dengan ongkir & pembayaran sendiri.
- [ ] Klik "Checkout" pada keranjang berisi 2+ perusahaan langsung membuat N `Transaction` berstatus menunggu pembayaran (pola marketplace, §4b); customer bisa membayar tiap pesanan independen, kapan pun, tanpa harus menuntaskan semua dalam satu sesi.
- [ ] Pembayaran salah satu perusahaan gagal/kadaluarsa tidak memengaruhi status/pembayaran pesanan perusahaan lain dalam checkout yang sama.
- [ ] Perusahaan yang belum mengisi kredensial Midtrans (`company_payment_credentials`) tetap bisa menerima pesanan via manual transfer; opsi Midtrans otomatis tersembunyi untuk perusahaan itu.
- [ ] Kupon perusahaan A tidak bisa dipakai untuk item perusahaan B.
- [ ] Report per perusahaan sesuai scope; `reports.consolidated` menampilkan gabungan + breakdown.
- [ ] Nomor invoice unik per perusahaan dengan prefix masing-masing; nomor seri faktur pajak unik per NPWP.
- [ ] Setelah migrasi dengan hanya BOQ aktif, seluruh alur existing berjalan tanpa perubahan perilaku.

## Edge Cases

- Perusahaan dinonaktifkan saat masih ada transaksi berjalan/keranjang berisi produknya — transaksi berjalan tetap diproses sampai selesai; item keranjang ditandai tidak tersedia saat checkout.
- Admin kehilangan scope suatu perusahaan saat sedang aktif di konteks itu — sesi berikutnya dialihkan ke perusahaan valid pertama; jika tak ada, akses admin ditolak.
- Dua perusahaan punya produk dengan nama/slug sama — **diputuskan**: pola URL storefront **tidak berubah** (`/p/{slug}`, sama seperti sekarang) supaya SEO/link produk BOQ existing tidak rusak. Constraint unique berubah dari `unique(slug)` global menjadi `unique(company_id, slug)`; kalau slug baru bentrok dengan produk milik perusahaan lain, sistem auto-suffix (`-2`, dst.) saat create, sama seperti pola slug-generation yang sudah ada.
- Flash sale/kupon dibuat admin multi-scope — form wajib eksplisit memilih perusahaan (ikut konteks switcher), tidak boleh ambigu.
- Redeem poin (jika poin global) atas produk perusahaan tertentu — beban promo lintas PT; lihat Open Questions.
- Return request atas transaksi perusahaan X hanya boleh diproses admin ber-scope X.
- Perusahaan baru live di katalog tapi merchant Midtrans belum disetujui — lihat §4b: manual transfer tetap tersedia sejak hari pertama, opsi Midtrans otomatis disembunyikan sampai `company_payment_credentials` diisi lengkap.

## Keputusan Terkonfirmasi (2026-07-19)

Poin-poin berikut sudah diputuskan bersama owner dan tidak lagi jadi Open Question — detail teknis masing-masing sudah tertaut di bagian terkait:

- **Poin & member tier**: global (1 saldo lintas PT) — lihat §2, perlu sign-off keuangan untuk kebijakan cost-sharing.
- **Kategori**: global/bersama — lihat §2.
- **Wishlist & review**: global per produk — lihat §2.
- **Email pengirim**: 1 domain/mailer bersama, display name per PT — lihat §4.
- **Faktur pajak**: seri nomor per NPWP per perusahaan — lihat §6.

## Open Questions

Sisa pertanyaan berikut tidak menghalangi mulainya Fase 2 (bisa dijawab sambil jalan / sebelum perusahaan kedua benar-benar transaksi):

- **WA gateway**: satu nomor gateway untuk semua PT (default asumsi saat ini, lihat §2) atau tiap PT butuh nomor sendiri?
- Apakah perusahaan baru butuh onboarding data (copy master produk dari PT lain sebagai template) atau selalu input dari nol?

## Rekomendasi Urutan Implementasi

1. **Fase 1 — Fondasi** (tanpa perubahan perilaku): tabel `companies` + seed BOQ, `company_id` + backfill, `admin_company_assignments`, refactor `hasAdminPermission(company-aware)`, company switcher, scoping middleware. Rilis dengan 1 perusahaan.
2. **Fase 2 — Marketplace & checkout**, urutan sub-langkah berdasarkan effort/risiko (lihat §4a):
   1. Skema: `company_id` di `products` (atau `product_variants`), `store_locations`, `coupons`; `company_id` di `transactions`; backfill seluruh data existing ke company BOQ.
   2. Listing/search storefront (`FrontendController`) + order history customer — effort kecil, tambah `where`/`whereIn('company_id', ...)` dan eager-load relasi `company`. Bisa dikerjakan & di-deploy paling awal karena low-risk dan tidak menyentuh alur uang.
   3. Ongkir per company: `store_locations.company_id` + `RajaOngkirController::shippingOptions()` menerima parameter company, hilangkan hardcode `latest()->first()` global.
   4. Kupon per company: `coupons.company_id` nullable, scoping di `CheckoutCouponController`/`MidtransController`/`ManualPaymentController`, session `checkout_coupon` jadi keyed-by-company.
   5. Pajak/payment info per company: `CheckoutTaxCalculator` + kedua payment controller resolve `company_settings` berdasarkan `company_id` grup (menyatukan dengan §4b Payment Gateway).
   6. **Checkout multi-perusahaan (area risiko tertinggi)**: restrukturisasi session cart/checkout jadi grouped-by-company, endpoint checkout membuat N `Transaction` sekaligus (pola marketplace, §4b), redesign halaman checkout & `checkout-waiting` jadi daftar pesanan per perusahaan.
   7. Report per perusahaan + konsolidasi (`reports.consolidated`).
   
   Baru setelah seluruh sub-langkah di atas selesai dan diuji dengan 1 perusahaan (perilaku harus identik), perusahaan kedua diaktifkan untuk transaksi riil.
3. **Fase 3 — B2B**: revisi `prd-quotation-to-invoice-b2b.md` agar company-aware (semua tabel B2B ber-`company_id`, penomoran per perusahaan, role Staff Gudang via `admin_company_assignments`, piutang per perusahaan), lalu implementasi.
