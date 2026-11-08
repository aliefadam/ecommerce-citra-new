# Arsitektur Target, Trust Boundary, dan Threat Model

## Arsitektur Target

```mermaid
flowchart LR
    M[Mobile browser / installed PWA] -->|/app/*| S[Vue SPA shell]
    D[Desktop browser] -->|existing public URLs| B[Blade storefront]
    S -->|same-origin JSON + session + CSRF| A[/api/mobile/v1]
    B --> W[Existing web controllers]
    A --> X[Shared application/domain services]
    W --> X
    X --> DB[(MySQL)]
    X --> R[RajaOngkir]
    X --> P[Midtrans]
    P -->|signed webhook| H[/api/midtrans/notification]
    O[External catalog clients] --> C[/api/v1/companies/{slug}]
```

### Boundary rules

- Vue menangani presentasi, route state, loading, dan recovery UI.
- Laravel menentukan identity, ownership, company scope, harga, stok, promo, ongkir, pajak, point,
  status, dan allowed transition.
- API mobile dan Blade memanggil service domain yang sama.
- Open Catalog API tetap public/read-only dan tidak menjadi jalur transaksi.
- Provider callback tidak bergantung pada browser atau status yang dikirim SPA.

## Deployment dan Asset Isolation

Entry Vite target:

```text
resources/js/mobile-app/main.js
resources/css/mobile-app.css
resources/views/mobile-app.blade.php
```

Rancangan saja; file tersebut belum dibuat.

Route `/app/{path?}` mengembalikan satu shell untuk path yang diizinkan. API dan asset bukan bagian
catch-all. Entry desktop `resources/js/app.js` dan `resources/css/app.css` tetap berjalan seperti
sekarang.

### PWA coexistence

Existing:

- manifest `/app.webmanifest`, `start_url=/`, `scope=/`;
- service worker `/service-worker.js`, `scope=/`;
- navigasi network-first dengan fallback `/offline.html`;
- cache hanya offline page dan icon.

Target mobile:

- manifest `/app/manifest.webmanifest`, `start_url=/app`, `scope=/app/`;
- worker `/app-service-worker.js`, didaftarkan dengan scope `/app/`;
- asset hash cache-first;
- public catalog terbatas network-first/stale-while-revalidate;
- semua auth/cart/checkout/order/payment network-only.

Scope `/app/` lebih spesifik dan tidak mengubah route desktop. Root worker existing tidak boleh
diubah dalam Fase 1 kecuali ada migration plan terpisah.

## Autentikasi dan Session

### Existing

- Guard `web` berbasis session database.
- Cookie HTTP-only, SameSite Lax, path `/`; secure mengikuti environment.
- Login/register meregenerasi session; logout invalidate session dan regenerasi CSRF token.
- Sanctum belum menjadi dependency.

### Target

- Tambahkan Sanctum dan stateful API untuk same-origin SPA.
- Cookie session tetap HTTP-only; tidak ada auth token di `localStorage`/IndexedDB.
- API client mengirim credential same-origin dan CSRF header.
- `GET /bootstrap` boleh optional-auth; route private memakai `auth:sanctum`/stateful guard sesuai
  konfigurasi final.
- Session expiry menghasilkan `401` dengan code stabil; SPA membuka re-auth dan mempertahankan
  return route internal yang tervalidasi.
- Google OAuth tetap full-page redirect dengan signed/internal return target.

## CORS Decision

`config/cors.php` existing mengizinkan origin `*`, tanpa credential, untuk GET/HEAD/OPTIONS pada
`api/*`. Kebijakan itu dibuat untuk Open Catalog API.

Mobile API tidak boleh menggunakan wildcard credential. Pilihan implementasi:

1. same-origin only dan tidak membutuhkan CORS untuk produksi; atau
2. allowlist origin first-party eksplisit untuk environment yang benar-benar membutuhkan subdomain.

Rekomendasi: **same-origin only**. Jika development Vite memakai origin berbeda, gunakan proxy Vite
atau allowlist environment lokal yang tidak masuk produksi.

## Data dan State Ownership

| State | Owner otoritatif | Client boleh menyimpan |
| --- | --- | --- |
| Session/user | Laravel session | User summary non-sensitif di memory |
| Catalog/filter | Laravel + URL | Query/filter/scroll |
| Harga/promo/stok | Laravel/database | Display snapshot sementara |
| Cart member | Database | Optimistic copy dengan rollback |
| Checkout context | Server session/opaque ID | Opaque ID, route state |
| Ongkir | Signed server quote | Token sampai expiry |
| Order/payment | Database/provider webhook | Display status terakhir |
| Tax/customer data | Database/private storage | Form draft minimal di memory; bukan SW cache |
| Feature flags | Server bootstrap | Non-sensitive flags |

## Threat Model

| Ancaman | Existing control | Gap SPA | Mitigasi wajib |
| --- | --- | --- | --- |
| CSRF mutation | Web CSRF middleware | API stateful belum ada | Sanctum CSRF, same-origin, SameSite, negative test |
| Session fixation | Regenerate login/register | JSON auth belum ada | Pertahankan regenerate/invalidate behavior |
| Token theft | HTTP-only session | Risiko jika bearer token ditambahkan | Larang token di browser storage |
| XSS/product content | Blade escaping dominan | Vue raw HTML dapat membuka sink | Hindari `v-html`; sanitasi allowlist jika benar-benar perlu |
| Open redirect | Existing locale/auth memiliki intended URL | `/app` return URL baru | Hanya relative `/app/*`, normalisasi server-side |
| Cross-customer object access | Banyak query/abort ownership | Resource API baru dapat lupa scope | Policy/owned query + negative test setiap object |
| Cross-company item mix | Pricing memverifikasi `company_id` | Client dapat mengirim company ID | Turunkan dari variant/order; server verify setiap group |
| Client price/stock tampering | `CheckoutPricingService` | Snapshot SPA dapat basi | Re-resolve preview dan submit; tampilkan diff |
| Shipping-cost tampering | Encrypted quote bound ke company/destination/items | Token reuse/replay | Expiry 15 menit, fingerprint, context check |
| Coupon/flash oversubscription | Row lock + reservation | Retry submit | Transaction + idempotency key |
| Duplicate order | Unique random order ID, DB transaction | Random ID baru tiap retry tidak mencegah duplikat | Persisted idempotency key per actor/context |
| Stock race | Reservation + `lockForUpdate` | Tidak ada gap bila service dipakai | Wajib gunakan service; jangan reimplement |
| Payment spoofing | Signature + gross amount + terminal-state guards | Client polling dapat dianggap final | Webhook tetap authority; poll read-only |
| Webhook replay/out-of-order | Idempotent/state transition tests | API baru tidak relevan langsung | Pertahankan signature/idempotency/transitions |
| Guest order enumeration | Session-owned/verified order + email verification | JSON may reveal status difference | Generic error, throttle, opaque access state |
| Payment-proof abuse | Ownership, image/mime/max 4 MB, private storage pattern | Multipart API baru | Reuse validation/storage; inspect content and state |
| Sensitive cache leakage | Existing SW cache sangat terbatas | SPA caching dapat menyimpan API response | Route allowlist + network-only private endpoints |
| CORS credential exposure | Existing supports_credentials=false | Temptasi membuka wildcard | Same-origin or explicit allowlist only |
| API scraping/DoS | Public limiter 120/min; auth-specific limiter | Mobile endpoints belum punya limiter | Limiter per browse/auth/cart/checkout/payment risk |
| Error detail leakage | API global JSON hides 500 detail | Controller existing kadang returns exception message | Stable public error codes; log detail server-side |

## Idempotency Contract

Order creation mobile harus menerima header:

```text
Idempotency-Key: <UUID generated once per submit intent>
```

Server mengikat key ke actor (user ID atau guest session), endpoint, checkout fingerprint, dan masa
berlaku. Request ulang dengan payload sama mengembalikan hasil order pertama. Key sama dengan payload
berbeda mengembalikan `409`. Penyimpanan dan unique constraint ditentukan pada Fase 4 database design.

UI disabled/loading bukan pengganti idempotensi server.

## Feature Flag dan Rollback

Feature flags minimum:

- `mobile_spa_enabled`: shell dapat diakses;
- `mobile_spa_pilot`: entry/prompt hanya cohort internal/pilot;
- `mobile_spa_catalog_enabled`: catalog API/route aktif;
- mutation flags per domain pada fase berikutnya.

Rollback:

1. Matikan entry/prompt dan flag route SPA yang belum aman.
2. Pertahankan desktop/mobile Blade existing sebagai fallback.
3. Jangan rollback database migration transaksi secara destruktif.
4. API yang sudah dipakai client lama mengikuti compatibility window.
5. Service worker mobile menampilkan update/retire behavior yang tidak menghapus data domain.

Tidak ada redirect berdasarkan user-agent pada pilot. Preferensi membuka `/app` hanya disimpan setelah
pilihan eksplisit pengguna.

## Observability Contract

- Semua response API membawa/menjaga request ID existing.
- Log mencatat route, status, latency, app version, dan user ID ter-hash/ID internal bila perlu.
- Jangan log cookie, CSRF token, password, NPWP mentah, alamat lengkap, atau provider payload mentah.
- Error client dikelompokkan per frontend version dan route.
- Funnel SPA dan Blade diberi `surface=mobile_spa|blade`.
- Alert minimum: 5xx, 401 spike, checkout/order error, provider error, dan service-worker update failure.

Provider monitoring belum dipilih dan tetap menjadi keputusan gate sebelum pilot, bukan sebelum shell
foundation.

## Security Verification Minimum per Fase

- Fase 1: CSRF, session expiry, open redirect, CORS, no token storage, SW cache inspection.
- Fase 2: hidden/inactive product, field whitelist, pagination bounds, XSS content.
- Fase 3: cart/address/wishlist ownership dan cross-user route binding.
- Fase 4: tampered price/company/shipping/coupon, replay/idempotency, stock race, webhook ordering.
- Fase 5: order/tracking/invoice/tax/payment-proof ownership dan sensitive masking.
- Fase 6: full threat regression, dependency audit, security header, and rollback drill.
