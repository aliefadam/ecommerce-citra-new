# PRD: Mobile Storefront SPA/PWA

Status: Fase 4 checkout dan payment selesai; Fase 5 belum dimulai
Tanggal keputusan: 8 November 2026  
Area: Storefront mobile / B2C customer experience  
Referensi pengalaman: Astro Lite (`astronauts.id`) sebagai referensi pola, bukan untuk disalin identik  
Otoritas dokumen: PRD ini menggantikan seluruh ketentuan mobile yang bertentangan dalam
`prd-modernisasi-frontend-customer.md`

> Hasil Fase 0 tersedia di [`mobile-spa-phase-0/README.md`](mobile-spa-phase-0/README.md), sedangkan
> bukti implementasi dan gate Fase 1 tersedia di
> [`mobile-spa-phase-1/README.md`](mobile-spa-phase-1/README.md). Hasil Fase 2 tersedia di
> [`mobile-spa-phase-2/README.md`](mobile-spa-phase-2/README.md). Hasil Fase 3 tersedia di
> [`mobile-spa-phase-3/README.md`](mobile-spa-phase-3/README.md). Hasil Fase 4 tersedia di
> [`mobile-spa-phase-4/README.md`](mobile-spa-phase-4/README.md). Fase 5 belum dimulai.

## Ringkasan Keputusan

Ecommerce Citra akan memiliki storefront mobile berbentuk **Single Page Application (SPA)** yang
dapat dipasang sebagai **Progressive Web App (PWA)**. Mobile SPA ditempatkan pada namespace `/app`
dan menggunakan backend, data, serta aturan bisnis Laravel yang sama dengan storefront desktop.

Storefront desktop existing tetap menggunakan Laravel Blade, route, URL publik, dan tampilan yang
sudah ada. Program ini tidak memberi izin untuk merombak tampilan desktop. Mobile SPA adalah lapisan
presentasi baru, bukan sistem ecommerce atau sumber data baru.

Keputusan teknologi awal:

- Mobile SPA menggunakan Vue 3, Vue Router, dan Pinia melalui Vite.
- Laravel tetap menjadi backend dan sumber kebenaran seluruh aturan bisnis.
- API mobile berada di `/api/mobile/v1` dan menggunakan JSON contract ter-versi.
- Autentikasi first-party menggunakan Laravel Sanctum berbasis session/cookie dan proteksi CSRF.
- PWA menggunakan web app manifest dan service worker dengan strategi cache yang aman.
- Public Company Catalog API tidak dipakai untuk cart, checkout, customer, atau transaksi.

Perubahan framework di atas hanya berlaku untuk mobile SPA. Blade tetap menjadi rendering layer
desktop.

## Hubungan dengan PRD Lama

Mulai 8 November 2026:

1. Larangan penggunaan React/Vue pada `prd-modernisasi-frontend-customer.md` dicabut untuk mobile
   SPA.
2. Seluruh requirement, sprint, acceptance criteria, dan Definition of Done khusus mobile pada PRD
   tersebut dipindahkan ke PRD ini.
3. Pekerjaan desktop, design token, asset Vite, performa, aksesibilitas, dan business behavior yang
   tidak bertentangan tetap berlaku.
4. Implementasi mobile Blade existing boleh dipertahankan sementara sebagai fallback sampai mobile
   SPA lulus rollout gate.
5. Dokumen dan bukti sprint lama tetap disimpan sebagai histori; tidak boleh dipakai untuk menyatakan
   mobile SPA telah selesai.

Jika terdapat konflik, urutan otoritas adalah:

1. PRD domain transaksi/security terkait.
2. PRD Mobile Storefront SPA/PWA ini untuk pengalaman mobile.
3. PRD Modernisasi Frontend Customer untuk desktop dan fondasi bersama.

## Latar Belakang

Storefront sekarang menggunakan Blade responsif untuk desktop dan mobile. Pendekatan tersebut telah
memperbaiki konsistensi visual, tetapi pengalaman mobile masih terikat pada struktur halaman desktop,
navigasi full-page, dan koordinasi beberapa sticky element.

Referensi Astro menunjukkan pola yang ingin diadopsi secara konseptual:

- pengalaman mobile satu kolom yang menyerupai aplikasi;
- search sebagai aksi utama;
- bottom navigation yang persisten;
- drawer/bottom sheet untuk interaksi kontekstual;
- perpindahan layar tanpa reload dokumen penuh;
- manifest PWA, portrait orientation, dan install experience;
- lebar aplikasi tetap terkendali ketika dibuka pada layar lebar.

Visual, merek, copy, aset, layout detail, dan source code Astro tidak boleh disalin. Ecommerce Citra
tetap menggunakan identitas industrial-modern dan kebutuhan katalog produk tekniknya sendiri.

## Masalah yang Ingin Diselesaikan

1. Navigasi mobile belum terasa seperti aplikasi yang cepat dan berkesinambungan.
2. Struktur desktop membatasi optimasi urutan informasi dan aksi satu tangan pada mobile.
3. Search, kategori, cart, transaksi, dan profil membutuhkan navigasi mobile yang lebih konsisten.
4. Reload halaman penuh menghilangkan state UI, posisi scroll, dan konteks filter.
5. Sticky CTA, chat, toast, keyboard, dan bottom navigation berpotensi berebut ruang.
6. Mobile belum dapat dipasang sebagai aplikasi web yang memiliki shell dan lifecycle terkontrol.
7. Kontrak API transaksi yang aman dan khusus first-party mobile belum tersedia secara lengkap.

## Tujuan

- Memberikan pengalaman mobile app-like yang cepat pada viewport 360–767 px.
- Menjaga tampilan dan alur desktop existing tanpa redesign.
- Mempertahankan URL desktop publik dan SEO yang sudah berjalan.
- Menyediakan navigasi client-side dengan deep link dan browser history yang benar.
- Menyelesaikan search → produk → cart → checkout → payment pada mobile SPA.
- Mendukung guest dan member, multi-company, PPN, kupon, point, faktur pajak, serta metode pembayaran
  existing tanpa mengubah business rule.
- Menjaga kalkulasi harga, stok, diskon, pajak, ongkir, dan status pembayaran tetap server-authoritative.
- Menjadikan mobile SPA installable sebagai PWA tanpa mengklaim sebagai aplikasi native.
- Memungkinkan rollout, observability, dan rollback secara independen dari desktop.

## Non-Tujuan

- Tidak meredesain atau memigrasikan desktop Blade menjadi SPA.
- Tidak membuat aplikasi Android/iOS native dalam program ini.
- Tidak mengubah aturan harga, stok, pajak, kupon, point, payment, atau multi-company.
- Tidak mengganti database atau menduplikasi backend ecommerce.
- Tidak membuat checkout dapat bekerja offline.
- Tidak menyimpan data customer, token sensitif, cart final, atau payment payload di service-worker cache.
- Tidak memindahkan otorisasi atau kalkulasi transaksi ke client.
- Tidak menyalin identitas visual, aset, copy, atau layout Astro secara identik.
- Tidak langsung menghapus mobile Blade fallback sebelum rollout selesai.

## Persona dan User Stories

### Customer mobile guest

- Saya dapat mencari dan melihat produk tanpa login.
- Saya dapat mempertahankan cart selama session dan melanjutkan guest checkout.
- Saya dapat kembali dari detail tanpa kehilangan filter dan posisi daftar.

### Customer mobile member

- Saya dapat login, mengelola profil/alamat, melihat wishlist, dan melacak pesanan.
- Session saya aman dan dapat dipulihkan setelah refresh atau membuka deep link.
- Saya mendapat status jelas ketika session berakhir.

### Customer procurement

- Saya dapat melihat SKU, seller, spesifikasi, varian, satuan, stok, dan dokumen transaksi dengan
  jelas.
- Saya dapat melakukan transaksi lintas perusahaan tanpa pencampuran seller, ongkir, atau payment.

### Tim operasional dan owner

- Saya dapat merilis atau mematikan mobile SPA tanpa mengganggu desktop.
- Saya dapat memantau error, funnel, performa, dan versi frontend yang digunakan customer.

## Prinsip Pengalaman Mobile

1. **Task-first**: satu layar memiliki satu aksi primer yang jelas.
2. **Search-first**: search selalu mudah ditemukan dari Home dan Catalog.
3. **One-hand reach**: aksi penting berada dalam area yang mudah dijangkau.
4. **Persistent context**: filter, scroll, draft aman, dan tab aktif dipertahankan bila sesuai.
5. **No stacked bars**: hanya satu koordinator untuk bottom navigation, sticky CTA, toast, chat, dan
   keyboard.
6. **Fast perceived response**: skeleton, optimistic UI yang aman, dan route-level loading tersedia.
7. **Server authority**: client menampilkan hasil server, bukan menentukan nilai transaksi.
8. **Accessible by design**: semantic, focus, screen reader, target sentuh, contrast, zoom, dan reduced
   motion menjadi acceptance criteria.
9. **Recoverable**: setiap kegagalan jaringan, session, stok, payment, atau submit memiliki jalan pulih.
10. **Desktop isolation**: perubahan mobile tidak menggeser tampilan maupun behavior desktop.

## Information Architecture dan Route

Mobile SPA menggunakan namespace berikut:

| Area | Route awal | Catatan |
| --- | --- | --- |
| Home | `/app` | Search, kategori, produk, promo, trust |
| Search | `/app/search` | Query pada URL dan history |
| Kategori | `/app/categories` | Semua kategori |
| Listing | `/app/c/{slug}` | Filter/sort pada query string |
| Produk | `/app/products/{slug}` | Deep link dan share |
| Cart | `/app/cart` | Dikelompokkan per perusahaan |
| Checkout | `/app/checkout` | Guard terhadap cart tidak valid |
| Payment status | `/app/orders/{id}/payment` | Recovery dan polling terkendali |
| Transaksi | `/app/orders` | Member atau akses guest terverifikasi |
| Detail transaksi | `/app/orders/{id}` | Ownership wajib diverifikasi server |
| Wishlist | `/app/wishlist` | Member-only |
| Profil | `/app/account` | Member-only |
| Login/register | `/app/auth/*` | Return URL tervalidasi |

Semua route harus dapat dibuka langsung dan di-refresh tanpa `404`. Tombol Back/Forward browser
harus memulihkan route, state penting, dan posisi scroll yang relevan.

## Global Mobile Shell

### Header

- Menampilkan konteks halaman, search, back action, atau aksi spesifik sesuai route.
- Tinggi ringkas dan memperhitungkan `env(safe-area-inset-top)`.
- Tidak menduplikasi heading utama secara berlebihan.

### Bottom navigation

Tab awal: `Home`, `Katalog`, `Keranjang`, `Transaksi`, dan `Profil`.

- Memiliki icon dan label; state tidak bergantung pada warna saja.
- Cart badge menangani nilai besar tanpa mengubah lebar item.
- Menggunakan `env(safe-area-inset-bottom)`.
- Disembunyikan atau dikoordinasikan pada layar yang memerlukan CTA transaksi penuh.
- Tab member-only mengarahkan ke login dengan return URL internal yang aman.

### Layer coordinator

Satu store global mengatur bottom sheet, modal, sticky CTA, toast, chat, install prompt, keyboard, dan
safe area. Dua modal/drawer interaktif tidak boleh aktif bersamaan.

### Desktop behavior

- Route desktop existing di luar `/app` tidak berubah.
- Jika `/app` dibuka pada desktop, shell mobile ditampilkan maksimum 430 px dan dipusatkan; tidak
  menggantikan storefront desktop.
- Tidak ada redirect paksa desktop ke `/app`.

## Scope Functional

### Home, search, dan kategori

- Search bar menjadi aksi utama dan mendukung suggestion async.
- Kategori disajikan ringkas dalam grid yang dapat dipindai.
- Produk pertama terlihat tanpa scroll berlebihan.
- Promo tidak menutupi pencarian atau CTA utama.
- Search/filter/sort disimpan pada URL dan dipulihkan ketika kembali dari detail.
- Request lama dibatalkan; loading, empty, error, retry, dan no-connection state tersedia.

### Product listing dan card

- Card menampilkan foto, nama, SKU/ringkasan spesifikasi, seller, harga, satuan, serta status stok.
- Listing menggunakan pagination/load-more terkontrol, bukan payload katalog penuh.
- Infinite scroll, jika digunakan, wajib memiliki fallback dan posisi kembali yang stabil.
- Wishlist dan quick add hanya tersedia jika state produknya memungkinkan.

### Detail produk

- Galeri swipe, indikator gambar, harga, seller, stok, varian, spesifikasi, ongkir, dan CTA jelas.
- Variant selection tidak mengizinkan kombinasi unavailable.
- Sticky purchase bar tidak bertumpuk dengan bottom navigation, keyboard, chat, atau toast.
- Perubahan harga/stok setelah pemilihan harus berasal dari respons server yang tervalidasi.

### Cart

- Item dikelompokkan dan dihitung per perusahaan.
- Seller, varian, satuan, harga, diskon, stok, subtotal, dan alasan item invalid terlihat.
- Update quantity dapat optimistik hanya jika rollback terhadap respons server tersedia.
- Cart direkonsiliasi dengan server saat login, refresh, checkout, dan sebelum payment.
- Total client hanya presentasi; total final selalu dihitung ulang server.

### Checkout dan payment

- Tahap: penerima/alamat, pengiriman per perusahaan, voucher/pajak, pembayaran, review, konfirmasi.
- Guest checkout tetap tersedia.
- CTA tidak tertutup keyboard dan double submit dicegah server-side.
- Order creation menggunakan idempotency key dan transaction boundary existing.
- Perubahan stok/harga/ongkir saat checkout menghasilkan diff dan meminta konfirmasi ulang.
- Payment popup/deep link/provider error memiliki recovery action.
- Polling status berhenti pada terminal state dan tidak menggantikan validasi webhook.
- Client tidak pernah dapat menetapkan status payment/order sendiri.

### Account, wishlist, order, dan tracking

- Route protected memulihkan tujuan awal setelah login.
- API memverifikasi ownership customer dan company scope pada setiap resource.
- Order ID yang ditebak tidak boleh mengungkap data customer lain.
- Data pajak dan customer sensitif tetap dimasking.
- Invoice/faktur/tracking hanya tersedia pada state dan otorisasi yang benar.

## Kontrak API Mobile

API mobile harus terpisah dari Open Company Catalog API:

```text
/api/mobile/v1/*
```

Kelompok endpoint minimum:

- bootstrap/config dan current user;
- homepage, search, kategori, listing, serta detail produk;
- cart dan cart reconciliation;
- alamat dan opsi pengiriman;
- voucher, point, pajak, serta faktur pajak;
- checkout preview, order creation, dan payment initiation/status;
- wishlist, notifikasi, order, tracking, profil, dan session.

Aturan kontrak:

- JSON envelope dan error code konsisten.
- Breaking change membuat versi baru atau melalui masa kompatibilitas.
- Validation error dapat dipetakan ke field dan tahap checkout.
- Response tidak mengekspos model mentah, secret, credential, angka stok sensitif, atau payload provider.
- Request tidak menerima harga, total, diskon, status, role, atau ownership sebagai nilai otoritatif.
- Company context diturunkan dan diverifikasi server-side, bukan dipercaya dari client.
- Rate limit dibedakan antara public browse, authentication, cart, checkout, dan payment.
- Endpoint mutation memiliki CSRF, authorization, validation, auditability, dan idempotensi sesuai risiko.

## Autentikasi dan Security

- SPA first-party menggunakan secure, HTTP-only, same-site cookie; token tidak disimpan di
  `localStorage`.
- Login, register, reset password, dan endpoint sensitif mempertahankan throttle existing.
- Return URL hanya boleh internal `/app/*` untuk mencegah open redirect.
- Session expiry menghasilkan layar re-auth tanpa membuang cart yang masih aman dipulihkan.
- Cart guest menggunakan opaque identifier yang tidak dapat dipakai membaca cart pengguna lain.
- Seluruh order/customer object wajib melalui policy/ownership check setelah route binding.
- Checkout, reservation, order, stock movement, dan payment mempertahankan transaction boundary dan
  idempotensi.
- Webhook tetap memvalidasi signature/credential dan tidak bergantung pada status dari SPA.
- Analytics dan log tidak merekam password, cookie, token, data pajak mentah, atau payment payload.
- Content dari API di-render aman; HTML produk tidak boleh menjadi jalur XSS.

## PWA dan Strategi Cache

Manifest minimum:

- `name`, `short_name`, icon 192/512, `theme_color`, `background_color`;
- `start_url: /app`, `scope: /app/`, `display: standalone`, orientasi portrait-preferred;
- screenshot/shortcut dapat ditambahkan setelah core flow stabil.

Service worker:

- Cache-first hanya untuk asset build ber-hash dan aset statis aman.
- Network-first atau stale-while-revalidate terbatas untuk katalog publik.
- Network-only untuk auth, cart mutation, checkout, customer, order, payment, pajak, dan upload.
- Response private/authenticated tidak masuk shared cache.
- Update versi memberi notifikasi dan tidak me-refresh saat form/checkout sedang aktif.
- Offline mode hanya menyediakan app shell dan pesan recovery; tidak menjanjikan checkout offline.

## SEO, Canonical, dan Discovery

- URL Blade desktop tetap menjadi URL publik/canonical untuk homepage, kategori, produk, dan content.
- Route `/app` tidak membuat halaman publik duplikat; gunakan canonical ke URL publik yang setara atau
  `noindex` berdasarkan keputusan SEO per tipe halaman.
- Share product dari SPA menghasilkan URL yang dapat dibuka di mobile maupun desktop.
- Redirect atau prompt mobile tidak boleh menjadi cloaking dan tidak boleh memblokir crawler.
- Tidak ada redirect paksa berdasarkan user-agent; preferensi `/app` boleh disimpan setelah pilihan
  eksplisit pengguna.

## Analytics dan Observability

Event minimum:

- app open/install prompt/install accepted;
- search submitted/result viewed;
- product viewed/variant selected/add to cart;
- cart viewed/checkout started;
- checkout step completed/validation failed;
- payment initiated/completed/failed/recovered;
- route/API error, session expired, dan service-worker update.

Event tidak boleh memuat data personal atau payment sensitif. Dashboard harus memisahkan mobile SPA
dari desktop Blade serta mencatat versi frontend/API.

## Performance dan Accessibility Budget

Target release candidate pada perangkat mobile representatif:

- tidak ada horizontal overflow pada lebar 360 px;
- route transition memberikan feedback visual maksimal 100 ms;
- LCP p75 target ≤ 2,5 detik dan CLS p75 ≤ 0,1 pada halaman publik utama;
- initial JavaScript budget ditetapkan pada Sprint 0 dan kenaikannya memerlukan persetujuan;
- gambar menggunakan ukuran responsif, lazy loading, dan placeholder berdimensi tetap;
- touch target minimum 44×44 px dan input form minimum 16 px;
- seluruh core flow mendukung zoom 200%, screen reader dasar, keyboard, dan reduced motion;
- focus berpindah ke heading/region yang benar setelah route transition dan kembali ke trigger setelah
  modal ditutup.

## Strategi Rollout dan Fallback

1. `/app` dirilis di belakang feature flag dan hanya untuk tim internal.
2. Pilot menggunakan link eksplisit; mobile Blade tetap menjadi default.
3. Persentase customer mobile dinaikkan bertahap setelah funnel dan error stabil.
4. Pengguna dapat kembali ke `Versi Web` tanpa kehilangan session yang aman.
5. Rollback mematikan entry/prompt SPA, bukan menghapus backend atau data.
6. Mobile Blade baru boleh dinyatakan legacy setelah dua periode rilis stabil dan owner sign-off.

## Rencana Implementasi

### Fase 0 — Discovery dan Contract Inventory

- Petakan seluruh route, policy, service, session, payment, dan API gap.
- Tetapkan viewport, device, browser, performance baseline, dan JavaScript budget.
- Buat prototype shell tanpa transaksi nyata.
- Dokumentasikan pemetaan URL desktop ↔ `/app`.

Gate: arsitektur, threat model, API inventory, design direction, dan rollback plan disetujui.

### Fase 1 — Foundation SPA/PWA

Status: **Selesai 9 November 2026**. Bukti gate dan hasil verifikasi tersedia di
[`mobile-spa-phase-1/README.md`](mobile-spa-phase-1/README.md).

- Setup Vue, router, store, API client, error boundary, feature flag, dan design token.
- Implementasi shell, bottom navigation, safe area, route loading, manifest, dan service worker awal.
- Implementasi session bootstrap dan observability tanpa checkout.

Gate: direct load/refresh/back-forward, isolation desktop, asset build, accessibility shell, dan update
PWA lulus.

### Fase 2 — Discovery dan Catalog

Status: **Selesai 9 November 2026**. Bukti gate dan hasil verifikasi tersedia di
[`mobile-spa-phase-2/README.md`](mobile-spa-phase-2/README.md).

- Home, search, kategori, listing, filter, product card, dan detail produk.
- Deep link, share, scroll restoration, loading/empty/error/offline state.

Gate: search → detail lulus pada viewport/device matrix dan tidak mengubah desktop.

### Fase 3 — Auth, Cart, dan Wishlist

Status: **Selesai 9 November 2026**. Bukti gate dan hasil verifikasi tersedia di
[`mobile-spa-phase-3/README.md`](mobile-spa-phase-3/README.md).

- Guest/member session, auth lifecycle, cart, cart reconciliation, dan wishlist.
- Negative test untuk CSRF, ownership, tampering, dan cross-company access.

Gate: add/update/delete/reconcile cart aman dan dapat dipulihkan.

### Fase 4 — Checkout dan Payment

Status: **Selesai 10 November 2026**. Bukti security gate dan verifikasi tersedia di
[`mobile-spa-phase-4/README.md`](mobile-spa-phase-4/README.md).

- Address, shipping, voucher, point, PPN, faktur, review, order creation, dan payment recovery.
- Multi-company, stock race, price change, idempotency, popup close, provider error, serta webhook.

Gate: seluruh happy path dan critical negative path transaksi lulus tanpa duplicate order/payment/stock
movement.

### Fase 5 — Account, Order, dan Tracking

- Profil, alamat, order history/detail, payment retry, tracking, invoice, faktur pajak, notifikasi.
- Ownership, masking, dan state-based action diverifikasi.

Gate: account lifecycle dan order access lulus security/browser test.

### Fase 6 — Pilot dan Rollout

- Performance, accessibility, device matrix, analytics funnel, support documentation, dan pilot.
- Rollout bertahap serta observasi error dan conversion.

Gate: tidak ada P0/P1 terbuka, target performa disetujui, rollback drill berhasil, dan owner/QA sign-off.

## Quality Gate Setiap Fase

- [ ] Scope dan perubahan kontrak API terdokumentasi.
- [ ] Desktop screenshot regression menunjukkan tidak ada perubahan yang tidak disetujui.
- [ ] Unit/feature/API/browser test relevan lulus.
- [ ] Happy path dan negative path authorization/tampering relevan lulus.
- [ ] `npm run build` lulus dan bundle budget diperiksa.
- [ ] Tidak ada console error pada core flow.
- [ ] Loading, empty, validation, error, retry, offline, dan session-expired state diuji.
- [ ] Keyboard, focus, screen reader, contrast, zoom, touch target, dan safe area direview.
- [ ] Company isolation, server calculation, idempotency, dan transaction boundary tidak melemah.
- [ ] Tidak ada secret atau data customer/payment sensitif di client cache, log, maupun analytics.
- [ ] Observability dan rollback tersedia sebelum exposure diperluas.

## Acceptance Criteria Global

- [ ] `/app` adalah SPA dengan client-side navigation, deep link, refresh, Back, dan Forward yang benar.
- [ ] PWA dapat dipasang dan membuka scope `/app` dalam mode standalone.
- [ ] Tampilan serta route desktop existing tidak berubah akibat program ini.
- [ ] Mobile shell maksimum 430 px pada layar lebar dan usable mulai 360 px.
- [ ] Home, search, kategori, detail, cart, checkout, payment, order, dan profile tercakup.
- [ ] Guest dan member flow lulus end-to-end.
- [ ] Multi-company, stok, harga, diskon, ongkir, PPN, point, faktur pajak, dan payment tetap dihitung
  server-side.
- [ ] Tidak ada cross-customer atau cross-company data exposure.
- [ ] Mutation terlindungi CSRF, validation, authorization, rate limit, dan idempotensi yang relevan.
- [ ] Service worker tidak menyimpan response auth, cart, checkout, order, customer, atau payment.
- [ ] Mobile Blade fallback dan rollback telah dibuktikan sebelum rollout penuh.
- [ ] Desktop dan mobile analytics dapat dibedakan tanpa merekam data sensitif.
- [ ] Performance dan accessibility budget memiliki bukti pada release candidate.
- [ ] Dokumentasi customer/support dan screenshot final telah diperbarui.

## Definition of Done

Program selesai ketika:

1. Fase 0–6 dan seluruh quality gate ditutup dengan bukti.
2. Core flow berikut lulus pada mobile SPA:
   - search → detail → cart → checkout → payment;
   - guest buy-now → checkout → payment/manual proof;
   - multi-company cart → checkout → transaksi independen;
   - login → order → tracking/invoice/faktur pajak;
   - wishlist → detail → pembelian.
3. Desktop visual regression dan business regression lulus.
4. Tidak ada defect P0/P1 security, transaksi, visual, accessibility, atau performance yang terbuka.
5. Pilot stabil, rollback drill berhasil, dan owner, technical owner, serta QA menyetujui rollout.

## Risiko dan Mitigasi

### Dua frontend meningkatkan biaya pemeliharaan

Mitigasi: satu backend/service layer, kontrak API ter-versi, design token bersama, dan scope desktop
dibekukan dari perubahan SPA.

### Perbedaan behavior desktop dan mobile

Mitigasi: contract test, shared business service, parity matrix, serta larangan kalkulasi domain di
client.

### API memperluas attack surface

Mitigasi: Sanctum/CSRF, policy, company scope, rate limit per risiko, resource whitelist, idempotensi,
dan negative security test.

### Service worker menyajikan data basi atau sensitif

Mitigasi: allowlist cache eksplisit, network-only untuk domain privat/transaksi, cache versioning, dan
automated cache inspection.

### SPA mengganggu SEO

Mitigasi: Blade tetap canonical, `/app` memiliki aturan canonical/noindex eksplisit, dan tidak ada
redirect paksa crawler.

### Rollout mengganggu conversion

Mitigasi: feature flag, pilot, persentase bertahap, funnel comparison, fallback, dan instant kill
switch.

## Keputusan yang Sudah Ditetapkan

- Mobile storefront baru adalah SPA/PWA pada `/app`.
- Desktop tetap Laravel Blade dan tidak didesain ulang oleh program ini.
- Vue 3, Vue Router, Pinia, dan Vite menjadi stack mobile SPA awal.
- Laravel dan service domain existing tetap menjadi sumber kebenaran.
- API mobile memakai `/api/mobile/v1`; Open Company Catalog API tidak diperluas untuk transaksi.
- Autentikasi first-party memakai Sanctum session/cookie, bukan token di `localStorage`.
- Blade mobile existing adalah fallback sementara, bukan target akhir pengalaman mobile.
- Tidak ada redirect paksa berdasarkan user-agent pada rollout awal.
- Astro adalah referensi pola pengalaman saja, bukan sumber desain yang disalin.

## Pertanyaan Terbuka Setelah Fase 3

- Bottom navigation Fase 1 telah menetapkan Home, Katalog, Keranjang, Transaksi, dan Profil. Apakah
  komposisi ini tetap dipertahankan setelah tersedia data penggunaan pilot?
- Apakah route `/app` akan menjadi default mobile setelah pilot atau tetap opt-in?
- Berapa periode rilis stabil sebelum mobile Blade boleh dinyatakan legacy?
- Provider monitoring/error reporting mana yang disetujui dan bagaimana retensi datanya?
- Apakah budget initial JavaScript Fase 1 sebesar 150 KB gzip dipertahankan untuk Fase 2? Build
  foundation saat ini menggunakan 43,77 KB gzip untuk initial JavaScript mobile.
