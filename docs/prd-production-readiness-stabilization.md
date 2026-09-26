# PRD: Production Readiness & Stabilization

> Status aktual (27 September 2026): **Implementasi aplikasi selesai, keputusan release masih NO-GO**. Backend, E2E, build, guard, backup tooling, dan runbook tersedia; bukti feature freeze, sign-off, off-server backup/restore drill, load test, alert delivery, production smoke, serta sertifikasi Midtrans/RajaOngkir/email masih harus dipenuhi di environment release.

> Addendum wajib: temuan audit terbaru dan urutan remediasinya didefinisikan dalam [PRD Remediasi Hasil Audit Sistem September 2026](prd-system-audit-remediation-2026-09.md). Seluruh temuan P0/P1 pada addendum tersebut menjadi release blocker dan harus ditutup sebelum keputusan go-live.

## Ringkasan

Sistem saat ini sudah memiliki fitur commerce dan operasional yang cukup luas, termasuk guest/member checkout, pembayaran Midtrans dan transfer manual, perhitungan ongkir dan pelacakan resi RajaOngkir, pengiriman email, WhatsApp gateway, retur/refund, multi-company penjual, serta dokumen B2B dari quotation sampai invoice.

Fase ini tidak menambah fitur procurement enterprise. Fokusnya adalah memastikan fitur yang sudah ada aman, dapat diprediksi, dapat dipantau, dan dapat dipulihkan sebelum digunakan untuk transaksi produksi.

Temuan baseline pada 11 Agustus 2026:

- Suite backend: 95 tes lulus dan 1 tes gagal dari 96 tes (547 assertions).
- Browser E2E: 5 dari 5 tes lulus, tetapi baru mencakup guest checkout.
- `composer audit`: 31 advisory keamanan pada 11 package, termasuk beberapa severity high.
- `npm audit --omit=dev --audit-level=high`: tidak menemukan vulnerability production dependency.
- Integrasi eksternal belum memiliki release checklist sandbox/live yang terdokumentasi dan dapat diulang.
- Belum ada bukti terukur untuk kapasitas beban, pemulihan backup, alerting, dan audit keamanan menyeluruh.

Status produk setelah fase ini ditargetkan menjadi **production-ready untuk pilot dan peluncuran awal**, bukan setara skala atau fitur enterprise Monotaro.

## Keputusan Produk

- Dependency security dan seluruh tes yang gagal adalah release blocker.
- E2E tetap diperlukan, tetapi hanya untuk alur yang berisiko langsung terhadap uang, stok, kepemilikan order, dan fulfillment. Tidak semua halaman harus diuji lewat browser.
- Integrasi live tidak dijalankan pada setiap CI. CI memakai fake/mock atau sandbox; verifikasi live dilakukan lewat smoke test terkontrol sebelum go-live.
- Tidak boleh ada transaksi uang nyata, pengiriman nyata, atau pesan massal yang dipicu otomatis oleh test suite.
- Fitur enterprise customer organization, multiple buyer login, approval order, TOP/credit limit, purchase order, dan quotation customer self-service tidak dikerjakan pada fase ini.
- Release tidak boleh dilakukan hanya berdasarkan pengujian manual.

## Tujuan

- Menghilangkan vulnerability dependency severity critical/high yang diketahui.
- Membuat seluruh automated test hijau dan stabil.
- Melindungi alur pembayaran, stok, fulfillment, refund, dan dokumen B2B dari regresi utama.
- Memastikan integrasi Midtrans, RajaOngkir, email, dan WhatsApp dapat gagal dengan aman dan dapat didiagnosis.
- Menetapkan logging, monitoring, alerting, backup, restore, dan prosedur incident response minimum.
- Membuktikan kapasitas awal aplikasi menggunakan load test yang dapat diulang.
- Membuat keputusan go/no-go produksi berdasarkan bukti dan release gate.

## Non-Tujuan

- Menyamai jumlah produk, pengguna, gudang, atau volume transaksi Monotaro.
- Membuat akun organisasi pembeli atau multiple login untuk customer perusahaan.
- Membuat approval order berjenjang.
- Membuat pembayaran TOP, credit scoring, atau credit limit customer.
- Membuat purchase order customer atau PO Waiting.
- Membuat quotation customer self-service.
- Mengganti payment gateway, penyedia ongkir, email provider, atau WhatsApp gateway yang sekarang.
- Membuat WMS, OMS, ERP, ticketing customer support, atau data warehouse baru.
- Menjamin aplikasi bebas bug sepenuhnya. Target fase ini adalah mengurangi risiko kritis dan membuat masalah cepat terdeteksi serta dapat dipulihkan.

## Pengguna dan Stakeholder

### Customer

- Bisa checkout dan membayar tanpa terkena transaksi ganda.
- Mendapat status pembayaran dan pengiriman yang konsisten.
- Tidak dapat melihat atau mengubah order milik customer lain.
- Mendapat pesan yang jelas saat layanan eksternal gagal.

### Admin Operasional

- Bisa memverifikasi pembayaran, memproses, dan mengirim order tanpa pengurangan stok ganda.
- Bisa mengetahui integrasi atau queue yang gagal.
- Memiliki jejak perubahan status penting.

### Owner / Product Owner

- Memiliki indikator kesehatan aplikasi dan release checklist.
- Mengetahui kapan sistem aman untuk diluncurkan atau harus di-rollback.

### Developer / Operator

- Dapat menjalankan tes dan pemeriksaan security dengan satu prosedur yang terdokumentasi.
- Dapat menelusuri error menggunakan request/order identifier tanpa mengekspos data sensitif.
- Dapat melakukan restore dari backup yang sudah diverifikasi.

## Scope Functional

### 1. Dependency Security & Test Baseline

#### 1.1 Dependency remediation

- Inventaris semua advisory dari Composer dan NPM beserta package asal, dependency chain, severity, versi terpasang, dan versi perbaikan.
- Perbarui dependency secara terarah, dimulai dari:
  - `laravel/framework`;
  - `guzzlehttp/guzzle` dan `guzzlehttp/psr7`;
  - package Symfony terkait;
  - `league/commonmark`;
  - `phpseclib/phpseclib`.
- Hindari upgrade mayor yang tidak dibutuhkan jika patch/minor aman tersedia.
- Setelah setiap kelompok update, jalankan PHPUnit, Playwright, dan smoke test halaman utama.
- Commit dependency lockfile agar hasil instalasi reproducible.
- Advisory severity critical/high harus nol sebelum release.
- Advisory medium yang belum dapat diperbaiki harus memiliki risk acceptance tertulis: dampak, apakah code path digunakan, mitigasi, pemilik, dan batas waktu penyelesaian.
- Jangan menonaktifkan audit atau memasukkan advisory ke ignore list tanpa risk acceptance.

#### 1.2 Perbaikan tes gagal

- Perbaiki kegagalan `AdminTransactionsUsabilityTest::manual transaction create form renders customer product and totals`.
- Tentukan apakah kegagalan disebabkan regresi fitur atau assertion lama setelah form berpindah ke AJAX.
- Jika perilaku baru memang benar, ubah tes untuk memverifikasi kontrak baru: endpoint pencarian, hasil AJAX, pemilihan customer/produk, kalkulasi total, dan submit.
- Tes tidak boleh dilonggarkan hanya agar hijau; assertion harus tetap membuktikan perilaku penting.

#### 1.3 Release test commands

Release checklist minimal menjalankan:

```text
composer audit --no-interaction
npm audit --omit=dev --audit-level=high
php artisan test
npm run test:e2e
```

Semua command harus menghasilkan exit code sukses. Output audit dan test disimpan sebagai artifact atau lampiran release.

### 2. Risk-Based E2E Coverage

E2E diperlukan karena controller/unit test tidak membuktikan bahwa JavaScript, session, CSRF, redirect, form, database, dan perubahan status bekerja bersama di browser. Cakupan dibuat kecil dan bernilai tinggi agar tidak menjadi suite yang lambat dan rapuh.

#### 2.1 Pembayaran

Skenario minimum:

- Guest transfer manual: checkout, order dibuat, upload bukti, status menunggu verifikasi.
- Member transfer manual: alamat tersimpan, checkout, upload bukti, order muncul di profil.
- Member Midtrans sandbox/mock: charge dibuat satu kali dan callback valid mengubah status menjadi paid/settlement.
- Callback Midtrans yang sama dikirim dua kali tidak menggandakan order, stok, poin, notifikasi, atau status history.
- Callback dengan signature tidak valid ditolak dan tidak mengubah transaksi.
- Order expired/cancelled tidak dapat kembali menjadi pending akibat sinkronisasi terlambat.
- Payment gagal atau timeout menampilkan pesan yang dapat ditindaklanjuti dan tidak membuat order duplikat.

#### 2.2 Member checkout

Skenario minimum:

- Cart multi-item dengan alamat tersimpan berhasil sampai order dibuat.
- Kuantitas atau stok yang berubah sebelum bayar divalidasi ulang.
- Voucher dan redeem point tidak dapat dipakai dua kali atau melebihi saldo.
- Customer tidak dapat membuka order, invoice, bukti bayar, atau faktur pajak user lain.

#### 2.3 Admin fulfillment

Skenario minimum:

- Admin dengan permission tepat dapat memverifikasi pembayaran, memproses order, dan mengirim order dengan resi.
- Admin tanpa permission ditolak.
- Transisi status tidak valid ditolak.
- Menekan aksi dua kali tidak mengurangi stok dua kali atau membuat status history ganda.
- Order yang sudah dikirim tampil pada halaman tracking.

#### 2.4 Refund/return

Skenario minimum:

- Customer yang memenuhi syarat dapat mengajukan return/refund untuk qty yang valid.
- Customer lain dan guest yang belum memiliki akses tidak dapat mengajukan pada order tersebut.
- Qty return tidak dapat melebihi qty yang dapat diretur.
- Admin dapat approve/reject dan status serta catatan terlihat oleh pemilik order.
- Pada fase ini transfer dana refund tetap manual; UI tidak boleh menyatakan dana sudah dikembalikan jika baru disetujui.

#### 2.5 Alur B2B

Karena modul B2B sudah tersedia dan menyentuh dokumen finansial, satu happy path dan beberapa invariant wajib diuji meskipun fitur enterprise baru tidak ditambahkan:

- Quotation dibuat dan dikonversi menjadi Sales Order.
- Sales Order menghasilkan proforma invoice atau delivery note sesuai alur yang dipilih.
- Pembayaran parsial tidak menandai invoice lunas.
- Total dokumen dan sisa tagihan konsisten sepanjang konversi.
- Dokumen yang memiliki pembayaran tidak hilang ketika Sales Order dibatalkan.
- Scope company mencegah admin perusahaan A mengakses dokumen perusahaan B.

Jika modul B2B belum dipakai saat go-live, browser E2E B2B dapat dijadikan P1 setelah peluncuran, tetapi authorization dan perhitungan finansial backend tetap release blocker.

### 3. External Integration Verification

#### 3.1 Prinsip umum

- Pisahkan mode `fake`, `sandbox/staging`, dan `production` melalui konfigurasi environment.
- Kredensial tidak boleh ditulis di repository, log, screenshot, atau test artifact.
- Semua request eksternal memiliki connect timeout, response timeout, dan penanganan error yang eksplisit.
- Retry hanya untuk operasi yang aman/idempotent. Operasi charge, refund, dan pengiriman pesan harus memiliki idempotency/deduplication.
- Simpan provider reference ID yang diperlukan untuk rekonsiliasi, tetapi mask payload sensitif.
- Kegagalan provider tidak boleh menyebabkan stok atau status transaksi menjadi setengah berubah tanpa jejak.

#### 3.2 Midtrans

- Automated test menggunakan fake HTTP atau sandbox credentials khusus test.
- Verifikasi signature callback, mapping semua status penting, idempotency, callback out-of-order, expiry, cancel, deny, settlement, dan timeout.
- Lakukan satu smoke transaction sandbox dari checkout sampai callback sebelum release.
- Production smoke test memakai nominal dan produk khusus yang disetujui operator, lalu langsung direkonsiliasi; tidak dijalankan otomatis oleh CI.
- Dokumentasikan proses pengecekan transaksi antara database lokal dan dashboard Midtrans.
- Alert jika callback berulang kali gagal atau status lokal tertinggal dari provider.

#### 3.3 RajaOngkir

- Contract test memastikan payload origin, destination, weight, courier, AWB, dan parsing response tetap sesuai kontrak provider.
- Uji alamat valid, alamat tidak didukung, timeout, rate limit, response kosong, courier tidak didukung, dan AWB belum aktif.
- Cache tracking tidak boleh menutupi status internal order; kegagalan tracking tetap menampilkan nomor resi dan status internal.
- Lakukan smoke test staging/live terkontrol untuk satu perhitungan ongkir dan satu AWB valid.
- Tracking API hanya dibaca sebagai informasi. Tidak boleh menampilkan klaim bahwa pengiriman sudah dibatalkan hanya karena order lokal dibatalkan.

#### 3.4 Email

- Automated test memakai mail fake/array dan memverifikasi penerima, subject, CTA, order ID, serta tidak adanya data customer lain.
- Staging smoke test mengirim ke alamat internal allowlist.
- Production mengaktifkan SPF, DKIM, dan DMARC pada domain pengirim jika provider mendukung.
- Catat delivery failure tanpa menyimpan isi email penuh atau data sensitif di log.
- Email transaksi tidak boleh bergantung pada request web sampai selesai; gunakan queue untuk pengiriman yang tidak perlu blocking.

#### 3.5 WhatsApp

- Automated test memakai fake gateway dan memverifikasi deduplication, nomor tujuan yang dinormalisasi, template, dan error handling.
- Staging/live smoke test hanya boleh mengirim ke nomor internal allowlist.
- Tidak ada broadcast atau campaign dalam smoke test.
- Jika gateway offline, transaksi tetap berhasil dan kegagalan notifikasi masuk queue/failed job untuk ditindaklanjuti.
- Token internal dan QR/session secret tidak boleh muncul di browser customer atau log umum.

### 4. Logging, Monitoring & Alerting

#### 4.1 Structured logging

- Log produksi minimal memiliki timestamp, level, environment, request/correlation ID, route, company ID, user/admin ID jika tersedia, order ID jika relevan, event name, dan exception class.
- Jangan log password, API key, Midtrans server key, token, cookie/session ID, nomor rekening penuh, NPWP penuh, alamat lengkap, atau payload bukti bayar.
- Email, telepon, NPWP, dan identifier sensitif harus dimask bila diperlukan untuk diagnosis.
- Request ID yang sama diteruskan ke log integrasi agar satu transaksi dapat ditelusuri end-to-end.

#### 4.2 Metrics minimum

- HTTP request count, error rate, dan latency p50/p95/p99.
- Checkout started, order created, payment success/failure, callback failure, dan duplicate callback count.
- Queue depth, oldest job age, failed job count, dan retry count.
- RajaOngkir/Midtrans/email/WhatsApp success rate dan latency.
- Order yang pending melewati batas waktu operasional.
- Disk usage, database availability, dan backup freshness.

#### 4.3 Alerts

Alert minimum:

- Error rate 5xx di atas 2% selama 5 menit.
- Payment callback gagal 3 kali berturut-turut atau tidak ada callback pada periode yang biasanya aktif.
- Queue failed job lebih dari 0 untuk job pembayaran/order, atau queue tertunda lebih dari 10 menit.
- Database tidak dapat diakses.
- Backup terakhir lebih tua dari 26 jam.
- Disk usage di atas 80% warning dan 90% critical.
- Integrasi eksternal gagal di atas 20% selama 10 menit.

Alert dikirim ke kanal operasional yang benar-benar dipantau. Setiap alert memiliki owner dan tautan runbook.

### 5. Backup, Restore & Disaster Recovery

- Backup database otomatis sedikitnya sekali sehari.
- Untuk produksi aktif, tambahkan backup lebih sering atau point-in-time recovery sesuai kemampuan database/provider.
- Backup file penting mencakup bukti pembayaran, invoice/faktur pajak, label pengiriman, dan upload operasional yang tidak dapat diregenerasi.
- Backup dienkripsi saat disimpan dan saat dikirim.
- Simpan minimal 7 backup harian dan 4 backup mingguan; target awal retention 30 hari.
- Backup disimpan terpisah dari server aplikasi utama.
- Akses restore dibatasi dan diaudit.
- Restore drill dilakukan sebelum go-live dan minimal setiap tiga bulan.
- Restore drill harus memulihkan database dan sample file ke environment terisolasi, lalu menjalankan integrity checks dan smoke test.

Target awal:

- RPO: maksimal kehilangan data 24 jam untuk pilot; target berikutnya 1 jam saat volume transaksi meningkat.
- RTO: layanan inti kembali maksimal 4 jam untuk pilot.

RPO/RTO harus dievaluasi ulang berdasarkan volume transaksi dan dampak bisnis sebelum public launch berskala besar.

### 6. Load & Resilience Testing

#### 6.1 Skenario beban

- Browse home/category/search/product detail.
- Login dan membuka profil/order history.
- Add/update cart dan membuka checkout.
- Kalkulasi shipping menggunakan stub provider untuk baseline aplikasi.
- Pembuatan order dengan payment provider di-fake agar tidak membuat charge nyata.
- Admin membuka list transaction dan detail order.
- Lonjakan callback payment yang idempotent.

#### 6.2 Target awal pilot

Target sementara sampai data traffic nyata tersedia:

- 100 concurrent virtual users untuk browse mix.
- 20 checkout submission per menit.
- 10 callback payment per detik selama burst 60 detik.
- Error rate aplikasi di bawah 1%, tidak termasuk failure yang sengaja diinjeksi.
- p95 halaman katalog dinamis di bawah 2,5 detik.
- p95 endpoint internal checkout di bawah 1,5 detik, tidak menghitung waktu provider eksternal.
- Tidak ada overselling, duplicate transaction, duplicate stock movement, atau database deadlock yang tidak tertangani.

- Dataset load test harus cukup representatif untuk pagination dan query: minimal 10.000 produk, 10.000 transaksi, dan histori status terkait di environment test.
- Jalankan soak test minimal 60 menit pada 50% target concurrency untuk mendeteksi memory leak, queue buildup, dan koneksi database yang tidak dilepas.
- Load test tidak dijalankan terhadap production tanpa jadwal, persetujuan, dan batas keamanan.
- Hasil test menyimpan konfigurasi, commit hash, dataset size, environment, latency, throughput, error, dan bottleneck.

### 7. Application Security Audit

Audit minimum mengikuti risiko OWASP dan karakter aplikasi:

- Authentication, session fixation, password reset, dan Google OAuth callback.
- Authorization/IDOR pada order, invoice, bukti pembayaran, faktur pajak, return request, dan tracking guest.
- Cross-company data isolation untuk seluruh route admin dan API katalog.
- CSRF pada semua state-changing web route.
- Rate limit pada login, reset password, tracking order, upload bukti, dan endpoint integrasi yang relevan.
- Mass assignment dan validation pada model/request kritis.
- File upload: MIME/content validation, ukuran, nama acak, lokasi non-public jika sensitif, dan kontrol akses download.
- XSS pada catatan item, nama customer, konten CMS, review, dan data provider eksternal.
- SQL injection, SSRF, open redirect, host-header handling, dan webhook spoofing.
- Secret management dan pemeriksaan agar `.env`, backup, log, atau token tidak dapat diunduh.
- Konfigurasi production: `APP_DEBUG=false`, HTTPS, secure cookies, trusted proxies/hosts, security headers, dan directory listing mati.
- Audit trail untuk verifikasi pembayaran, perubahan status, perubahan resi, refund, pembatalan, dan perubahan permission admin.

Critical/high finding harus diperbaiki sebelum release. Medium finding harus diperbaiki atau diterima risikonya secara tertulis dengan mitigasi dan due date.

### 8. Health Checks & Operational Runbooks

- Sediakan liveness check yang tidak bergantung pada provider eksternal.
- Sediakan readiness check terproteksi untuk database, cache, queue, dan storage.
- Jangan tampilkan credential, exception detail, hostname internal, atau data sensitif pada health response publik.
- Buat runbook minimum untuk:
  - Midtrans callback tertunda/gagal;
  - RajaOngkir gagal menghitung ongkir;
  - email atau WhatsApp gagal;
  - queue menumpuk;
  - database unavailable;
  - disk penuh;
  - rollback deployment;
  - restore backup;
  - dugaan kebocoran credential/data.
- Setiap runbook menjelaskan gejala, pemeriksaan, mitigasi aman, eskalasi, dan validasi pemulihan.

## Data Integrity Invariants

Ketentuan berikut tidak boleh dilanggar:

- Satu provider transaction ID/order ID hanya merujuk ke satu transaksi lokal.
- Callback atau aksi admin berulang tidak boleh mengurangi stok, memberi poin, atau mengirim refund dua kali.
- Status terminal `dibatalkan` tidak kembali menjadi pending karena callback/sync terlambat.
- Stok tidak boleh negatif.
- Total detail, diskon, PPN, ongkir, pembayaran, dan grand total konsisten dengan dokumen final.
- Customer/admin tidak dapat mengakses data di luar ownership/company scope.
- Pembayaran yang sudah tercatat tidak boleh hilang ketika dokumen induk dibatalkan.
- Nomor dokumen unik sesuai company dan jenis dokumen.
- Tracking kurir tidak boleh mengubah status finansial transaksi.

Invariant kritis harus memiliki automated test pada service/feature level; gunakan E2E hanya jika interaksi browser ikut menentukan hasil.

## Implementation Phases

### Phase 1: Baseline Hijau & Dependency Security

1. Dokumentasikan baseline test dan audit.
2. Perbaiki satu backend test yang gagal dan perilaku terkait bila memang regresi.
3. Update dependency vulnerable secara bertahap.
4. Jalankan seluruh backend dan browser tests setelah update.
5. Tambahkan release checklist dan artifact hasil audit/test.

Exit criteria:

- PHPUnit dan Playwright 100% hijau.
- Tidak ada critical/high dependency advisory.
- Medium advisory memiliki fix atau risk acceptance.

### Phase 2: Critical-Path Automated Tests

1. Tambahkan E2E member checkout dan transfer manual.
2. Tambahkan test Midtrans callback signature, idempotency, dan out-of-order status.
3. Tambahkan admin fulfillment E2E.
4. Tambahkan return/refund E2E minimum.
5. Tambahkan B2B happy path serta company isolation backend test.
6. Hilangkan flaky test; retry tidak boleh digunakan untuk menyembunyikan race condition.

Exit criteria:

- Semua skenario P0 pada Scope 2 lulus berulang minimal tiga kali.
- Tidak ada duplicate order/stock/payment side effect.
- Test dapat dijalankan tanpa credential production.

### Phase 3: Integration Certification

1. Standarkan fake/sandbox/production configuration.
2. Jalankan Midtrans sandbox smoke test.
3. Jalankan RajaOngkir cost dan tracking smoke test.
4. Jalankan email dan WhatsApp allowlist smoke test.
5. Verifikasi timeout, retry, deduplication, log masking, dan fallback UI.
6. Dokumentasikan hasil serta reconciliation procedure.

Exit criteria:

- Setiap provider memiliki bukti happy path dan controlled failure test.
- Tidak ada credential atau PII bocor di log/artifact.
- Operator dapat merekonsiliasi status lokal dengan provider.

### Phase 4: Operability, Recovery & Security

1. Implement structured logging, correlation ID, metrics, dashboard, dan alert.
2. Implement backup terjadwal dan retention.
3. Jalankan restore drill dan catat RPO/RTO aktual.
4. Jalankan load, burst, dan soak test.
5. Audit authorization, upload, webhook, configuration, dan secret handling.
6. Buat runbook dan rollback procedure.

Exit criteria:

- Restore drill berhasil.
- Target load pilot tercapai tanpa pelanggaran data integrity.
- Critical/high security finding nol.
- Alert penting terbukti terkirim ke operator.

### Phase 5: Go-Live Readiness Review

1. Jalankan seluruh release gate pada release candidate.
2. Freeze perubahan fitur sampai hasil stabilisasi disetujui.
3. Backup production awal dan verifikasi akses restore.
4. Lakukan production smoke test terbatas.
5. Mulai pilot dengan traffic terbatas dan observability aktif.
6. Review error, conversion, payment reconciliation, dan support ticket setelah 24 jam, 72 jam, dan 7 hari.

## Acceptance Criteria

- Seluruh PHPUnit dan Playwright tests lulus tanpa retry tersembunyi.
- Tidak ada dependency advisory critical/high pada production dependency.
- Semua medium advisory yang tersisa terdokumentasi dan disetujui.
- Guest dan member checkout kritis memiliki automated E2E.
- Payment callback valid, invalid, duplicate, expired, dan out-of-order sudah diuji.
- Admin fulfillment tidak dapat mengurangi stok dua kali.
- Return/refund menjaga ownership dan qty limit.
- Jalur B2B menjaga total finansial dan company isolation.
- Midtrans, RajaOngkir, email, dan WhatsApp lulus sandbox/controlled live checklist.
- Log memiliki correlation/order ID dan tidak membocorkan secret/PII sensitif.
- Alert error, queue, database, backup, disk, dan provider failure terbukti berfungsi.
- Backup database/file berjalan otomatis dan restore drill berhasil.
- Load test memenuhi target pilot tanpa overselling atau duplicate side effect.
- Security audit tidak memiliki temuan critical/high terbuka.
- Runbook incident dan rollback dapat diikuti oleh operator selain pembuat fitur.
- Dokumen go/no-go ditandatangani owner yang ditunjuk sebelum public launch.

## Release Gate: Go / No-Go

Release dinyatakan **NO-GO** jika salah satu terjadi:

- Ada automated test yang gagal atau flaky pada critical path.
- Ada vulnerability critical/high yang belum diperbaiki.
- Payment callback belum terbukti signature-safe dan idempotent.
- Backup belum pernah berhasil direstore.
- Ada authorization/IDOR atau cross-company isolation finding terbuka.
- Load test menyebabkan stok negatif, duplicate order, atau data korup.
- Monitoring/alert tidak dapat memberi tahu operator saat failure disimulasikan.
- Integrasi menggunakan credential production dalam CI atau test otomatis.

Release dinyatakan **GO untuk pilot** bila seluruh acceptance criteria P0 terpenuhi dan risiko medium yang tersisa memiliki owner serta due date.

## Deliverables

- Dependency remediation report dan lockfile terbarui.
- Backend, E2E, audit, dan load-test reports.
- Critical-path test suite yang dapat dijalankan ulang.
- Integration matrix untuk fake/sandbox/production.
- Reconciliation checklist Midtrans dan provider lain.
- Dashboard operasional dan konfigurasi alert.
- Backup policy, restore report, dan disaster recovery runbook.
- Security audit report dan risk register.
- Go-live checklist serta go/no-go record.

## Urutan Prioritas

### P0 — wajib sebelum pilot

- Phase 1 seluruhnya.
- Payment/member/admin fulfillment critical tests.
- Midtrans signature dan idempotency.
- Authorization/IDOR dan company isolation.
- Backup + restore drill.
- Logging error, queue monitoring, dan alert kritis.
- Dependency serta security finding critical/high nol.

### P1 — wajib sebelum traffic publik diperbesar

- Return/refund E2E lengkap.
- B2B browser happy path jika modul digunakan di produksi.
- Full provider failure matrix.
- Soak test dan tuning performa lanjutan.
- Audit trail admin yang lebih lengkap.

### P2 — optimasi berkelanjutan

- Target RPO mendekati 1 jam.
- Perluasan observability dan business metrics.
- Chaos/failure injection lebih luas.
- Penambahan E2E untuk alur non-kritis berdasarkan insiden nyata.

## Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Update dependency menyebabkan regresi | Checkout/admin gagal | Update bertahap, lockfile, full regression test |
| E2E terlalu banyak dan flaky | Release lambat/tidak dipercaya | Risk-based E2E, deterministik, API fake, data terisolasi |
| Sandbox berbeda dari production | Bug baru muncul saat live | Controlled production smoke dan reconciliation checklist |
| Retry menciptakan side effect ganda | Stok/poin/payment ganda | Idempotency key, unique constraint, transaction, duplicate tests |
| Log membocorkan PII/credential | Insiden keamanan | Masking, allowlist field, access control, retention |
| Backup ada tetapi tidak bisa dipulihkan | Kehilangan data berkepanjangan | Restore drill berkala dan integrity checks |
| Load test tidak representatif | Produksi lambat saat traffic naik | Dataset realistis, browse/checkout mix, soak test |
| Provider eksternal down | Checkout/notifikasi terganggu | Timeout, fallback, queue, alert, runbook |

## Open Questions Operasional

Pertanyaan berikut tidak memblokir Phase 1-2, tetapi harus diputuskan sebelum Phase 4 selesai:

- Siapa operator/on-call yang menerima alert dan pada jam berapa?
- Di mana backup off-server disimpan dan siapa yang memegang recovery access?
- Berapa estimasi traffic, order per hari, dan jam puncak tiga bulan pertama?
- Modul B2B akan aktif saat go-live pertama atau diaktifkan setelah pilot?
- Provider email dan hosting produksi apa yang digunakan untuk menetapkan dashboard/alert yang tepat?
- Nomor serta alamat email internal mana yang masuk allowlist smoke test?

## Definition of Done

Fase stabilisasi selesai ketika seluruh P0 acceptance criteria terpenuhi, bukti test/audit/restore/load tersedia, tidak ada critical/high risk terbuka, dan owner menyetujui go-live pilot melalui checklist tertulis. Banyaknya fitur atau jumlah test bukan ukuran selesai; ukurannya adalah alur kritis terbukti benar, failure dapat diketahui, dan data dapat dipulihkan.
