# Fase 6 — Pilot dan Rollout Mobile SPA

Status engineering: **Selesai / release candidate**  
Versi: `rollout-6`  
Cache PWA: `ecommerce-citra-mobile-rollout-v6`  
Tanggal: 10 November 2026

Implementasi PRD Fase 0–6 ditutup pada release candidate ini. Aktivasi production tetap merupakan
keputusan operasional dan tidak dilakukan otomatis oleh perubahan kode.

## Hardening Fase 5

Audit sebelum rollout menemukan dan memperbaiki:

- filter order hanya mengenali status literal dan melewatkan `menunggu_verifikasi`, `paid`, serta
  `failure`; filter kini memakai kelompok status domain;
- permintaan faktur pajak kini ditolak server-side untuk order gagal/dibatalkan;
- target sentuh search, kategori, quantity cart, dan aksi wishlist dinaikkan menjadi minimum 44 px;
- link eksplisit `Kembali ke Versi Web` tersedia tanpa mengganti session.

Regression API membuktikan ownership order/address/notification, masking NPWP/telepon, state guard,
completion lock, status aliases, dan cross-user 404.

## Analytics dan Privacy

Endpoint `POST /api/mobile/v1/telemetry` menerima event allowlist saja. Data yang disimpan:

- `surface=mobile_spa`, versi app, nama route aman, timestamp server;
- hash session/fingerprint dengan HMAC; IP, user-agent, cookie, dan session ID mentah tidak disimpan;
- context allowlist seperti status, step, result count, duration, dan error type.

Search term, product identifier, email, alamat, NPWP, password, token, cookie, dan payment payload
tidak diterima. Analytics default mati dan diaktifkan eksplisit:

```env
MOBILE_SPA_ANALYTICS_ENABLED=true
MOBILE_SPA_ANALYTICS_RETENTION_DAYS=90
```

Operasional:

```bash
php artisan mobile-spa:analytics-summary --hours=24
php artisan mobile-spa:analytics-prune --days=90
```

Prune berjalan harian. Dashboard eksternal dapat membaca agregat event/version tanpa data personal.

## Performance dan Accessibility Gate

- Viewport otomatis: 320×568, 360×800, 390×844, 430×932, dan 768×1024.
- Tidak ada horizontal overflow, termasuk simulasi text zoom 200%.
- LCP p75 ≤ 2,5 detik dan CLS p75 ≤ 0,1 pada empat sample release candidate lokal.
- Indikator feedback navigasi mulai tampil ≤ 100 ms; durasi lazy-load route dicatat terpisah.
- Core touch target ≥ 44×44 px dan input visible ≥ 16 px.
- Heading menerima focus setelah route transition; keyboard, reduced motion, skip link, landmark,
  loading/error/offline/session-expired state diuji.
- Banner LCP dipreload dan memakai `fetchpriority=high`; route Fase 5 tetap lazy-loaded.
- Lighthouse Chromium: accessibility 100, best practices 100, SEO 100, tanpa failure.
- Initial mobile core sekitar 54,94 KB gzip, CSS 6,04 KB gzip; route chunk terbesar 2,70 KB gzip.

Hasil ini bukan pengganti field data p75 dan smoke test Safari iOS/Chrome Android nyata.

## Hasil Verifikasi Final

- Backend penuh: **273 test / 1.785 assertion lulus** (`composer test`).
- Browser penuh: **40 skenario lulus**, 1 utility pembuat screenshot di-skip secara desain
  (`npm run test:e2e`).
- Gate SPA/PWA: 11/11 skenario catalog, commerce, foundation, dan rollout lulus.
- Production build Vite lulus; `npm audit --audit-level=high` melaporkan 0 vulnerability.
- Lighthouse `/app`: accessibility 100, best practices 100, SEO 100, tanpa failure.
- Pint dan `git diff --check` lulus.

Regression test lama yang masih mengunci copy Inggris dan asumsi data tunggal juga telah diselaraskan
dengan lokalisasi serta data suite saat ini; tidak ada failure baseline yang tersisa.

## Device Matrix

| Target | Gate otomatis | Gate fisik sebelum production |
|---|---|---|
| Chromium 320–430 px | Lulus | Chrome Android current/current-1 |
| Tablet 768 px | Lulus | Android tablet/iPad bila termasuk pilot |
| Desktop 1440 px isolation | Lulus | Tidak diperlukan untuk SPA pilot |
| PWA standalone | Manifest/scope/cache otomatis lulus | Install/update/offline smoke Android dan iOS |
| Safari/iOS | Layout WebKit-independent direview | Safari iOS current/current-1 wajib |

Catat hasil perangkat fisik pada bagian sign-off sebelum mengaktifkan cohort customer.

## Rollout Bertahap

1. **Internal** — `MOBILE_SPA_ENABLED=true`, pilot badge aktif, analytics aktif, link hanya dibagikan
   kepada tim internal. Storefront Blade tetap default.
2. **Pilot terbatas** — bagikan link eksplisit ke cohort 5%; tidak ada redirect user-agent.
3. **Ekspansi** — 25%, 50%, lalu 100% hanya setelah review 24 jam, 72 jam, dan 7 hari.
4. **Legacy decision** — mobile Blade baru boleh dinyatakan legacy setelah dua periode rilis stabil
   dan sign-off owner; PRD ini tidak menghapus fallback.

Gunakan [`../pilot-review-template.md`](../pilot-review-template.md) untuk keputusan setiap tahap.

## Go / Hold / Rollback

GO bila:

- tidak ada P0/P1 terbuka dan duplicate payment/order/stock movement tetap nol;
- cross-user access dan payment signature failure tetap nol;
- API 5xx < 2%, checkout completion/error sesuai baseline, LCP p75 ≤ 2,5 s, CLS p75 ≤ 0,1;
- smoke test perangkat fisik dan owner/QA sign-off lengkap.

HOLD bila metrics belum cukup atau terdapat regresi P2 tanpa mitigasi. ROLLBACK segera bila ada P0,
cross-user exposure, duplicate financial side effect, overselling, atau error checkout berulang.

Rollback drill yang diuji otomatis:

1. set `MOBILE_SPA_ENABLED=false`;
2. jalankan `php artisan config:clear` (atau refresh immutable release config);
3. pastikan `/app` dan `/api/mobile/v1/*` mengembalikan 404;
4. pastikan storefront `/`, login, dan guest checkout Blade tetap tersedia;
5. jangan rollback migration transaksi/analytics secara destruktif; data tetap dipertahankan untuk
   audit dan forward-fix.

## Support Playbook

| Gejala | Tindakan pertama | Eskalasi |
|---|---|---|
| SPA tidak terbuka | cek flag, config cache, health endpoint | release owner |
| Session berakhir | login ulang; jangan meminta cookie/token customer | application support |
| Checkout gagal | simpan request ID dan order ID, jangan ulang dengan key baru | commerce engineer |
| Pembayaran belum berubah | cek provider/webhook/status API; jangan set status manual | payment owner |
| Order ganda/stock salah | hentikan rollout dan aktifkan rollback flag | incident commander |
| PWA versi lama | gunakan tombol Perbarui/reload; cek service worker version | frontend owner |
| Tracking gagal | resi tetap ditampilkan; cek provider setelah cache 15 menit | shipping owner |

Support tidak boleh meminta password, OTP, cookie, token, NPWP lengkap, payload payment, atau foto
identitas melalui kanal tiket.

## Sign-off Production

- [ ] Migration analytics diterapkan dan backup tervalidasi.
- [ ] Midtrans sandbox/production credential smoke tanpa mengekspos secret.
- [ ] Safari iOS dan Chrome Android perangkat fisik lulus.
- [ ] Standalone install/update/offline lulus.
- [ ] Dashboard/alert dan retention analytics aktif.
- [ ] Support memahami rollback dan incident playbook.
- [ ] Product owner: `____________` tanggal `____________`.
- [ ] QA owner: `____________` tanggal `____________`.
- [ ] Technical owner: `____________` tanggal `____________`.

Tanpa tiga sign-off terakhir, status deployment adalah **HOLD**, meskipun engineering PRD telah selesai.
