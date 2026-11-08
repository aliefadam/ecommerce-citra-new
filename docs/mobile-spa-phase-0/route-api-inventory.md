# Inventory Route, Fitur, dan API Gap

Tanggal inventory: 9 November 2026  
Sumber otoritatif: `routes/web.php`, `routes/api.php`, `php artisan route:list --json`

## Ringkasan Route

Route terdaftar: **297**. Route di luar namespace admin: **88**.

| Kelompok non-admin | Jumlah |
| --- | ---: |
| Public/web/supporting | 31 |
| API, termasuk webhook | 5 |
| Authentication | 11 |
| Commerce/cart/checkout/payment | 22 |
| Account/order/wishlist/notification | 19 |

Jumlah adalah inventory, bukan jumlah layar SPA. Beberapa URI adalah mutation atau endpoint pendukung.

## Pemetaan Layar Desktop ke Mobile SPA

| Existing Blade | Mobile SPA target | Akses | Catatan |
| --- | --- | --- | --- |
| `/` | `/app` | Public | Home/search/category highlights |
| `/pencarian?q=` | `/app/search?q=` | Public | Query/filter harus berada di URL |
| `/kategori?parent=&category=` | `/app/categories`, `/app/c/{slug}` | Public | Normalisasi main/detail category diperlukan |
| `/detail-produk/{slug}` | `/app/products/{slug}` | Public | Deep link/share |
| `/flash-sale` | `/app/promotions/flash-sale` | Public | Lazy route |
| `/promo/{slug}` | `/app/promotions/{slug}` | Public | Empty/expired state |
| `/redeem-point` | `/app/rewards` | Mixed | Login untuk redemption |
| `/cart` | `/app/cart` | Member pada parity awal | Guest buy-now tetap tersedia |
| `/checkout` | `/app/checkout` | Checkout session | Route guard + server preview |
| `/checkout/waiting/{orderId}` | `/app/orders/{orderId}/payment` | Owner/session | Payment recovery |
| `/checkout/orders` | `/app/orders` | Member | Filter/order list |
| `/profil?tab=biodata` | `/app/account` | Member | Profile summary |
| `/profil?tab=alamat` | `/app/account/addresses` | Member | CRUD JSON diperlukan |
| `/profil?tab=pesanan` | `/app/orders` | Member | Tidak lagi bergantung multi-tab Blade |
| `/wishlist` | `/app/wishlist` | Member | Existing JSON sebagian dapat dipakai ulang |
| `/notifications` | `/app/notifications` | Member | Contract JSON baru |
| `/lacak-pesanan` | `/app/track-order` | Public verified | Email + order ID verification |
| `/invoice/{transaction}` | `/app/orders/{id}/invoice` | Owner | File/navigation contract |
| `/blog`, `/blog/{slug}` | Desktop canonical | Public | Tidak wajib Fase 1–5 SPA |
| `/pages/{slug}` | Desktop canonical | Public | Legal links boleh membuka Blade |
| `/login`, `/register` | `/app/auth/login`, `/app/auth/register` | Guest | JSON auth + safe return URL |
| `/forgot-password`, `/reset-password/{token}` | `/app/auth/*` | Guest | Email/token flow tetap server-side |

## Inventory Endpoint Existing

### Public read

| Endpoint | Bentuk saat ini | Reuse |
| --- | --- | --- |
| `GET /` | HTML + payload homepage | Query logic direfaktor ke service/resource |
| `GET /pencarian` | HTML, seluruh hasil tanpa pagination | Tidak dipakai langsung; perlu API paginated |
| `GET /pencarian/saran` | JSON maksimal 8 suggestion | Dapat menjadi dasar autocomplete API |
| `GET /kategori` | HTML + category tree | Perlu resource JSON |
| `GET /detail-produk/{slug}` | HTML + detail/variant/review | Perlu resource JSON khusus storefront |
| `GET /api/v1/companies/{slug}/products*` | JSON publik per perusahaan | Hanya referensi serialization/filter |
| `GET /api/v1/companies/{slug}/categories*` | JSON publik per perusahaan | Tidak cukup untuk katalog gabungan storefront |

### Authentication/session

| Endpoint | Bentuk saat ini | Gap SPA |
| --- | --- | --- |
| `POST /login` | Redirect HTML, session regenerate | JSON result/current-user contract |
| `POST /register` | Redirect HTML, dapat claim guest order | JSON + guest claim result |
| `POST /logout` | Redirect HTML, session invalidate | JSON 204 + CSRF refresh behavior |
| Google OAuth | Full-page redirect/callback | Return target `/app` yang tervalidasi |
| Password reset | Redirect/form HTML | JSON request; reset dapat memakai controlled full page awal |

Laravel Sanctum belum ada pada `composer.json`. Guard existing hanya `web` berbasis session.

### Cart dan checkout

| Endpoint | Existing | Gap/keputusan |
| --- | --- | --- |
| Cart CRUD `/cart*` | Auth-only; mutation/count/items JSON | Contract belum versioned/resource-based |
| `POST /checkout/buy-now` | Public; menyimpan checkout snapshot di session; redirect | Perlu JSON response dan opaque checkout context |
| `POST /cart/checkout` | Menyimpan selected cart IDs di session | Perlu checkout session/preview contract |
| `GET /checkout` | HTML, membangun seluruh checkout | Perlu bootstrap/preview JSON |
| Coupon apply/remove | JSON/session per company | Perlu contract versioned dan preview refresh |
| RajaOngkir endpoints | JSON di bawah `checkout.access` | Dapat dibungkus API mobile, throttle per risiko |
| Shipping options | Menghasilkan signed encrypted quote | Wajib dipertahankan |
| Manual checkout | JSON, membuat transaksi/reservasi | Tambahkan idempotency key |
| Midtrans charge/status/cancel | JSON + session/ownership | Pisahkan create/status/cancel resources |
| Payment proof upload | Form/file response | JSON multipart + ownership existing |

Cart guest penuh belum tersedia. Database `carts` mengikat cart ke `user_id`, sedangkan guest checkout
existing menggunakan session `checkout.source = buy_now`.

### Account dan after-sales

| Area | Existing | Gap SPA |
| --- | --- | --- |
| Profile | HTML + POST redirect | Current user/profile JSON |
| Address | POST/PUT/DELETE redirect | List + JSON CRUD resources |
| Wishlist | JSON list/toggle/status/count | Versioning dan response normalization |
| Notification | Controller scoped user | List/read JSON resource contract |
| Orders | Data dirakit untuk Blade profile | Paginated order list/detail resources |
| Tracking | Session verification + redirect | JSON verify/detail dengan rate limit sama |
| Tax invoice | Mutation/download dengan ownership | JSON state/action + secure download |
| Review/return | Form mutation | JSON validation/action resources |
| Invoice/payment proof | Ownership/session checks | File metadata + secure navigation/download |

## API Mobile Minimum dan Prioritas

Prefix target: `/api/mobile/v1`.

### Foundation — Fase 1

| Method/path usulan | Tujuan | Auth |
| --- | --- | --- |
| `GET /bootstrap` | Locale, feature flags aman, user summary, badges, app version | Optional session |
| `GET /session` | Current user/session state | Optional session |
| `POST /auth/login` | Login session first-party | Guest + throttle + CSRF |
| `POST /auth/logout` | Invalidate session | Auth + CSRF |

Fase 1 belum mengaktifkan transaksi melalui API.

### Catalog — Fase 2

| Method/path usulan | Sumber domain |
| --- | --- |
| `GET /home` | Banner, kategori, promo, curated product sections |
| `GET /search/suggestions` | `StorefrontSearchSuggestionController` logic |
| `GET /products` | `Product::storefrontVisible`, pagination/filter/sort |
| `GET /products/{slug}` | Detail, variants, specifications, reviews summary |
| `GET /categories` | `StorefrontNavigationService`/category tree |
| `GET /promotions*` | Flash sale/promo active state |

### Auth, cart, wishlist — Fase 3

| Method/path usulan | Catatan |
| --- | --- |
| `POST /auth/register` | Claim guest orders harus dipertahankan |
| `GET/PATCH /account` | Profile resource |
| `GET/POST/PUT/DELETE /addresses*` | Ownership server-side |
| `GET/POST/PATCH/DELETE /cart*` | Member cart parity awal |
| `POST /cart/checkout-session` | Selected IDs divalidasi terhadap owner |
| `GET/POST/DELETE /wishlist*` | Hindari ambiguous toggle untuk retry |

`PUT/DELETE wishlist/{product}` lebih aman untuk retry daripada satu endpoint toggle.

### Checkout/payment — Fase 4

| Method/path usulan | Guardrail |
| --- | --- |
| `POST /checkout/buy-now-session` | Variant/qty server validation |
| `GET /checkout/preview` | Server-calculated items/totals/state |
| `POST /checkout/shipping-options` | Signed quote bound ke fingerprint |
| `PUT/DELETE /checkout/coupon` | Per-company server validation |
| `POST /checkout/orders` | **Idempotency-Key wajib** |
| `POST /orders/{order}/payments` | Owner/session + payment method allowlist |
| `GET /orders/{order}/payment-status` | Owner/session; polling bounded |
| `POST /orders/{order}/cancel` | State transition server-side |
| `POST /orders/{order}/payment-proof` | Multipart, type/size/content validation |

### Orders/account — Fase 5

| Method/path usulan | Guardrail |
| --- | --- |
| `GET /orders` | Paginated, current user only |
| `GET /orders/{order}` | Owner/session verified |
| `POST /track-order/verify` | Generic error + 3-failure throttle |
| `GET /track-order/{order}` | Verified session only |
| `GET/PATCH /notifications*` | Current user only |
| `POST /orders/{order}/tax-invoice` | Owner + state validation |
| `POST /orders/{order}/complete` | Owner + idempotent transition |
| `POST /orders/{order}/reviews` | Detail/order ownership |
| `POST /orders/{order}/returns` | Return window/quantity validation |

## Reusable Domain Boundary

Wajib dipakai ulang, bukan disalin ke controller API:

- `CheckoutPricingService` untuk product snapshot dan subtotal server-side;
- `CheckoutTaxCalculator` untuk PPN;
- `ShippingQuoteService` untuk signed quote dan context binding;
- `CommerceReservationService` untuk stok, flash-sale, kupon, commit/release;
- `LoyaltyPointService` untuk reserve/commit point;
- `TaxInvoiceRequestService` untuk data faktur;
- `ShipmentTrackingService` dan `RajaOngkirService` untuk pengiriman/tracking;
- `MidtransApiService`/webhook state transition untuk provider payment;
- `DocumentNumberGenerator` untuk nomor transaksi.

Controller Blade dan API sebaiknya mengorkestrasi application service yang sama. Fase implementasi
tidak boleh membuat kalkulasi kedua di Vue atau controller API.

## Contract Rules

- Response resource whitelist; jangan serialize model mentah.
- Error envelope: `code`, `message`, `errors`, `request_id` bila tersedia.
- Pagination konsisten dan page size dibatasi.
- Tanggal ISO-8601; uang integer Rupiah; rate pajak memiliki tipe yang terdokumentasi.
- Optimistic concurrency/state mismatch mengembalikan `409` atau `422` yang dapat dipulihkan.
- `401` untuk session tidak ada/kedaluwarsa; `403` untuk ownership/authorization; hindari object
  enumeration dengan response yang sesuai threat model.
- Mutation checkout/order wajib menerima idempotency key dan menyimpan hasil per actor/context.
- Open Company Catalog API tetap backward-compatible dan terpisah.

## Gap Prioritas

| Prioritas | Gap | Dampak |
| --- | --- | --- |
| P0 | Sanctum/stateful API belum ada | Auth SPA belum dapat dimulai aman |
| P0 | Tidak ada API mobile versioned | SPA akan tergantung kontrak web ad-hoc |
| P0 | Order creation belum punya request idempotency key | Risiko duplicate order saat retry/double submit |
| P0 | CORS `/api/*` wildcard GET-only | Tidak boleh sekadar dibuka untuk credentialed mutation |
| P0 | Manifest/SW existing scope `/` | Perlu scope `/app/` terpisah agar desktop terisolasi |
| P1 | Checkout state tersimpan sebagai array session | Perlu opaque context + preview contract |
| P1 | Homepage/search/detail logic berada di controller besar | Perlu extraction ke service/resource |
| P1 | Orders/profile/address banyak memakai redirect/Blade | Perlu JSON resource/action |
| P1 | Playwright hanya Chrome default | Mobile/multi-browser gate belum ada |
| P2 | Guest cart penuh tidak tersedia | Bukan blocker jika parity awal tetap buy-now |
| P2 | Analytics provider belum ditetapkan | Pilot funnel tidak dapat dinilai tanpa keputusan |
