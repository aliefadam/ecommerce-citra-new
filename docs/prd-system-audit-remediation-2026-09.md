# PRD: Remediasi Hasil Audit Sistem September 2026

## Ringkasan

Audit sistem pada 18 September 2026 menemukan bahwa happy path aplikasi sudah berjalan, tetapi masih ada celah kritis pada kalkulasi checkout, isolasi data antarperusahaan, reservasi stok, flash sale, dan kupon. Dokumen ini menjadi rencana kerja bertahap untuk memperbaiki temuan tersebut tanpa mencampurnya dengan pengembangan fitur baru.

Baseline audit:

- commit yang diaudit: `beb2365`;
- backend: 167 test lulus dengan 977 assertion;
- browser E2E: 11 skenario mencapai status `ok`;
- build production Vite berhasil;
- 279 route aplikasi berhasil dimuat;
- runner Playwright tidak berhenti sendiri setelah test terakhir di Windows;
- test Playwright storefront menimpa screenshot baseline yang dilacak Git.

Kelulusan test di atas membuktikan happy path yang sudah dicakup, tetapi belum membuktikan keamanan nilai transaksi, concurrency, atau isolasi perusahaan pada area yang belum memiliki negative test.

## Tujuan

- Semua nilai uang pada checkout dihitung dan dipercaya dari server, bukan browser.
- Data admin dan laporan selalu mengikuti perusahaan aktif, kecuali laporan konsolidasi yang memiliki permission khusus.
- Stok, kuota flash sale, dan batas kupon aman terhadap request bersamaan.
- Customer tidak dapat membayar order yang stoknya sudah tidak tersedia.
- Seluruh temuan audit mempunyai regression test yang dapat dijalankan di CI.
- Suite Playwright selesai dengan exit code yang benar dan tidak mengubah file tracked.

## Non-Tujuan

- Mengubah provider Midtrans atau RajaOngkir.
- Mendesain ulang keseluruhan halaman checkout.
- Menambah tipe promosi baru.
- Membuat data warehouse atau sistem inventory eksternal.
- Membuat laporan konsolidasi baru sebelum permission dan scope dasarnya aman.

## Prinsip Implementasi

1. Setiap bug harus mempunyai test yang gagal sebelum implementasi diperbaiki.
2. Endpoint pembayaran tidak boleh menerima nominal final sebagai sumber kebenaran.
3. Perubahan stok, promo, kupon, dan transaksi dilakukan dalam database transaction dengan row lock yang relevan.
4. Perbaikan dikirim per fase kecil. Fase berikutnya dimulai setelah test fase sebelumnya hijau.
5. Tidak ada request test yang memanggil transaksi uang, pengiriman, email massal, atau provider production.
6. Perubahan schema harus memiliki migration, backfill, verifikasi, dan rollback yang aman.

## Daftar Temuan

| ID | Prioritas | Temuan | Dampak utama |
|---|---|---|---|
| AUD-01 | P0 | Harga item dan ongkir checkout dipercaya dari payload browser | Order dapat dibuat dengan harga atau ongkir yang dimanipulasi |
| AUD-02 | P0 | `SalesReportController` tidak di-scope ke perusahaan aktif | Kebocoran omzet, transaksi, stok, kupon, dan retur lintas perusahaan |
| AUD-03 | P1 | Invoice admin tidak memeriksa perusahaan aktif | Admin dapat membaca invoice perusahaan lain melalui ID |
| AUD-04 | P1 | Kuota flash sale tidak diperiksa atau diperbarui saat transaksi | Diskon tetap berlaku setelah kuota habis |
| AUD-05 | P1 | Flash sale menerima ID varian perusahaan lain | Admin satu perusahaan dapat memengaruhi harga produk perusahaan lain |
| AUD-06 | P1 | Stok tidak direservasi saat order dibuat | Overselling dan order berbayar yang tidak dapat diproses |
| AUD-07 | P1 | Batas penggunaan kupon tidak atomic | Kupon dapat dipakai melebihi batas saat checkout bersamaan |
| AUD-08 | P2 | Kode kupon masih unique global | Perusahaan berbeda tidak dapat memakai kode kupon yang sama |
| AUD-09 | P2 | Normalisasi harga salah untuk format campuran | Nilai seperti `1.000,00` dapat berubah menjadi `100000` |
| AUD-10 | P2 | Harness Playwright menggantung dan menulis screenshot tracked | CI menggantung dan working tree menjadi kotor |

## Urutan Implementasi

### Fase 0 — Safety Net dan Reproduksi

Tujuan fase ini adalah mengunci perilaku bug sebelum mengubah kode produksi.

Langkah:

1. Tambahkan negative feature test untuk checkout dengan harga item Rp0, nama produk palsu, company palsu, dan ongkir Rp0 hasil manipulasi request.
2. Tambahkan test dua perusahaan untuk seluruh halaman laporan dan invoice.
3. Tambahkan test flash sale yang mengirim varian perusahaan lain.
4. Tambahkan test kuota flash sale habis.
5. Tambahkan test concurrency atau simulasi transaksi berurutan dengan sisa stok/kupon satu.
6. Pastikan test baru gagal karena alasan yang sesuai dengan masing-masing temuan, bukan karena setup test.
7. Rekam baseline query dan response penting agar perubahan berikutnya dapat dibandingkan.

Acceptance criteria:

- Setiap `AUD-01` sampai `AUD-10` mempunyai minimal satu regression test atau pemeriksaan otomatis.
- Test eksploitasi P0/P1 terbukti gagal pada kode sebelum perbaikan.
- Tidak ada test yang menggunakan credential atau provider production.

### Fase 1 — Kalkulasi Checkout Server-Authoritative

Mencakup `AUD-01` dan menjadi blocker untuk fase transaksi lain.

Langkah:

1. Buat service bersama, misalnya `CheckoutPricingService`, yang menerima hanya identitas varian, kuantitas, perusahaan, alamat/destination, pilihan kurir, dan kode kupon.
2. Ambil nama produk, gambar, harga reguler, harga flash sale, berat, perusahaan, dan stok dari database.
3. Abaikan atau hapus `items.*.price`, `items.*.name`, `items.*.image`, `items.*.companyId`, dan `shipping_cost` sebagai sumber kebenaran endpoint pembayaran.
4. Ubah endpoint pilihan ongkir agar menghasilkan quote server-side dengan token opaque, masa berlaku, perusahaan, destination, berat, layanan, dan harga.
5. Saat pembayaran dibuat, validasi token quote lalu gunakan nilai ongkir yang tersimpan di server. Token yang kedaluwarsa atau tidak cocok harus ditolak.
6. Gunakan hasil kalkulasi service yang sama untuk manual transfer dan Midtrans.
7. Bangun `TransactionDetail` dari snapshot server, bukan dari label/nilai yang dikirim browser.
8. Validasi `gross_amount` callback Midtrans terhadap total transaksi lokal sebelum status pembayaran diubah.
9. Log penolakan manipulasi tanpa menyimpan payload sensitif secara penuh.

Acceptance criteria:

- Mengubah harga, nama produk, company, diskon, atau ongkir di DevTools tidak mengubah total server.
- Manual transfer dan Midtrans menghasilkan subtotal, diskon, pajak, ongkir, dan grand total identik untuk input bisnis yang sama.
- Callback dengan nominal berbeda tidak dapat menandai transaksi sebagai paid.
- Seluruh happy path guest/member yang sudah ada tetap lulus.

### Fase 2 — Isolasi Perusahaan dan Otorisasi

Mencakup `AUD-02`, `AUD-03`, dan `AUD-05`.

Langkah:

1. Terapkan `ScopesToActiveCompany` pada `SalesReportController`.
2. Buat helper query terpusat untuk transaksi, detail transaksi, produk/varian, stok, kupon, retur, wishlist, dan metrik customer yang terkait perusahaan.
3. Tambahkan `where('company_id', activeCompanyId)` atau `whereHas` perusahaan pada seluruh query laporan.
4. Pisahkan laporan konsolidasi ke route dan permission `reports.consolidated`; jangan menggunakan ketiadaan filter sebagai konsolidasi implisit.
5. Pada invoice admin, wajibkan permission `transactions.show` dan `guardCompanyOwnership`.
6. Pertahankan akses invoice customer hanya untuk pemilik transaksi.
7. Validasi setiap `product_variant_id` flash sale melalui relasi produk yang memiliki `company_id` sama dengan perusahaan aktif.
8. Terapkan pemeriksaan kepemilikan yang sama pada create dan update flash sale di dalam transaction.
9. Audit ulang seluruh route model binding admin yang memakai model ber-`company_id`.

Acceptance criteria:

- Admin perusahaan A menerima 404/403 saat membaca atau memutasi data perusahaan B.
- Semua angka dan daftar laporan berubah mengikuti company switcher.
- Konsolidasi hanya bisa diakses user dengan `reports.consolidated`.
- Varian perusahaan B tidak dapat dimasukkan ke flash sale perusahaan A lewat request buatan.

### Fase 3 — Reservasi Stok dan Kuota Flash Sale

Mencakup `AUD-04` dan `AUD-06`.

Keputusan desain:

- Stok tersedia dihitung sebagai stok fisik dikurangi stok yang masih direservasi.
- Reservasi dibuat secara atomic saat order berhasil dibuat.
- Reservasi dilepas saat order dibatalkan atau kedaluwarsa sebelum pembayaran.
- Saat fulfillment dimulai, reservasi dikonversi menjadi pengurangan stok fisik tepat satu kali.
- Kuota flash sale menggunakan mekanisme reservasi serupa agar order pending tidak dapat melewati batas.

Langkah:

1. Tambahkan struktur persistensi reservasi stok yang terhubung ke transaction dan product variant, dengan unique constraint untuk mencegah reservasi ganda.
2. Tambahkan status dan timestamp reservasi: `reserved`, `committed`, atau `released`.
3. Saat order dibuat, lock seluruh varian dalam urutan ID yang konsisten, hitung stok tersedia, lalu buat reservasi dalam transaction yang sama.
4. Tolak seluruh order jika salah satu item tidak dapat direservasi; jangan membuat order parsial dalam satu perusahaan.
5. Release reservasi secara idempotent ketika order dibatalkan, ditolak, atau kedaluwarsa.
6. Commit reservasi secara idempotent ketika fulfillment pertama kali dimulai; cegah stock movement ganda.
7. Tambahkan reservasi kuota flash sale dan periksa `sold + reserved + requested <= quota` di bawah row lock.
8. Terapkan harga flash sale hanya bila waktu aktif, item aktif, perusahaan cocok, dan kuota berhasil direservasi.
9. Tampilkan sisa kuota berdasarkan nilai server yang sama dengan checkout.
10. Tambahkan command terjadwal untuk melepaskan reservasi kedaluwarsa dan rekonsiliasi reservasi yatim.

Acceptance criteria:

- Dua checkout bersamaan terhadap satu stok tersisa menghasilkan tepat satu keberhasilan.
- Customer tidak dapat membayar order tanpa reservasi stok yang valid.
- Cancel/expire mengembalikan ketersediaan tepat satu kali.
- Process ulang tidak mengurangi stok atau kuota dua kali.
- Setelah kuota flash sale habis, harga kembali ke harga reguler.

### Fase 4 — Kupon Atomic dan Perusahaan-Aware

Mencakup `AUD-07` dan `AUD-08`.

Langkah:

1. Ubah unique constraint kupon dari `code` global menjadi gabungan `company_id + normalized_code`.
2. Normalisasi kode menggunakan aturan yang sama saat store, update, apply, dan checkout.
3. Semua pencarian kupon harus memfilter `company_id` dan kode sekaligus; hindari `first()` berdasarkan kode global.
4. Batasi nilai kupon persen ke rentang 1–100.
5. Buat pencatatan reservasi/redemption kupon per transaksi dengan status `reserved`, `redeemed`, atau `released`.
6. Lock row kupon ketika memeriksa `usage_limit` dan membuat reservation.
7. Redeem kupon saat pembayaran valid sesuai keputusan bisnis; release ketika transaksi dibatalkan/kedaluwarsa sebelum redeem.
8. Pertahankan `used_count` sebagai nilai yang direkonsiliasi dari redemption, bukan counter bebas tanpa referensi transaksi.
9. Tambahkan migration backfill kode normalized dan pemeriksaan collision sebelum unique index baru dibuat.

Acceptance criteria:

- Dua perusahaan dapat memiliki kode `WELCOME10` secara independen.
- Kupon perusahaan A tidak pernah dipakai pada checkout perusahaan B.
- Dua request bersamaan pada sisa limit satu menghasilkan tepat satu penggunaan.
- Persentase di atas 100 ditolak.
- Retry checkout tidak menambah pemakaian kupon dua kali.

### Fase 5 — Hardening Input Harga dan Upload

Mencakup `AUD-09` serta review regresi commit `beb2365`.

Langkah:

1. Pisahkan parser Rupiah integer dari parser decimal ukuran/berat.
2. Definisikan format yang diterima secara eksplisit: angka polos, pemisah ribuan Indonesia, atau format decimal database.
3. Tolak format ambigu daripada menebak dan menyimpan nilai yang berpotensi salah 100 kali.
4. Tambahkan unit test untuk `1000`, `1.000`, `1000.00`, `1.000,00`, `1,000.00`, nilai kosong, negatif, dan karakter nonangka.
5. Pastikan import spreadsheet memakai nilai numerik cell, bukan hanya tampilan terformat.
6. Uji upload kategori untuk JPG, PNG, WebP, file rusak, ukuran besar, kegagalan optimizer, dan penggantian gambar.
7. Hapus file baru bila database transaction gagal agar tidak meninggalkan orphan file.

Acceptance criteria:

- Seluruh format harga yang didukung menghasilkan nilai yang terdokumentasi.
- Format ambigu ditolak dengan pesan yang jelas.
- Upload gagal tidak mengubah record lama dan tidak meninggalkan file yatim.

### Fase 6 — Stabilitas Playwright dan CI

Mencakup `AUD-10`.

Langkah:

1. Pisahkan visual baseline generation dari test regresi biasa.
2. Jangan menulis screenshot ke `docs/` pada `npm run test:e2e`; gunakan `test-results/` untuk artifact sementara.
3. Tambahkan script terpisah yang eksplisit, misalnya `test:e2e:update-baseline`, untuk memperbarui baseline dengan sengaja.
4. Perbaiki teardown web server di Windows sehingga PHP child process ditutup setelah test.
5. Tambahkan timeout CI tingkat job dan pemeriksaan bahwa tidak ada proses server tertinggal.
6. Jalankan test pada viewport desktop dan mobile tanpa mengubah working tree.
7. Setelah suite selesai, CI harus memeriksa `git diff --exit-code`.

Acceptance criteria:

- `npm run test:e2e` selesai sendiri dengan exit code 0.
- Tidak ada PHP/Node child process tertinggal.
- Working tree tetap bersih setelah suite dijalankan.
- Baseline hanya berubah melalui command update yang disengaja.

## Urutan Pull Request yang Disarankan

1. `test: add audit regression coverage` — Fase 0 saja.
2. `fix: make checkout pricing server authoritative` — Fase 1 tanpa reservasi concurrency.
3. `fix: enforce company isolation in reports invoices and flash sales` — Fase 2.
4. `feat: add atomic inventory and flash-sale reservations` — Fase 3.
5. `feat: add company-scoped atomic coupon redemption` — Fase 4.
6. `fix: harden money parsing and image rollback` — Fase 5.
7. `test: make Playwright deterministic and self-terminating` — Fase 6.

Setiap PR wajib dapat di-deploy dan di-rollback secara independen. Perubahan schema besar tidak digabung dengan perubahan UI yang tidak terkait.

## Test Matrix Minimum

| Area | Unit | Feature | Browser/E2E | Concurrency |
|---|---:|---:|---:|---:|
| Kalkulasi harga/pajak/diskon | Ya | Ya | Ya | Tidak |
| Quote ongkir | Ya | Ya | Ya | Tidak |
| Isolasi perusahaan | Tidak | Ya | Smoke | Tidak |
| Reservasi stok | Ya | Ya | Happy path | Ya |
| Flash sale | Ya | Ya | Happy path | Ya |
| Kupon | Ya | Ya | Happy path | Ya |
| Parser harga/upload | Ya | Ya | Dropzone | Tidak |
| Teardown Playwright | Tidak | Tidak | Ya | Tidak |

## Migration dan Rollback

- Jalankan audit collision kode kupon sebelum mengganti unique index.
- Backfill nilai normalized code di transaction terpisah dan verifikasi jumlah row.
- Tabel reservasi harus dapat ditambahkan tanpa mengubah perilaku produksi sampai feature flag diaktifkan.
- Aktivasi reservasi dilakukan setelah command rekonsiliasi menunjukkan tidak ada stok negatif.
- Rollback aplikasi tidak boleh menghapus tabel/data reservasi; migration penghapusan dijalankan hanya setelah dipastikan tidak ada versi aplikasi yang masih memakainya.
- Sebelum deploy Fase 1, ambil backup database dan simpan hasil smoke test checkout sandbox.

## Observability Wajib

- Counter penolakan price/quote tampering.
- Counter kegagalan reservasi stok, flash-sale, dan kupon.
- Gauge reservasi kedaluwarsa atau yatim.
- Log company mismatch dengan request ID, actor ID, active company ID, dan target company ID tanpa PII sensitif.
- Alert ketika transaksi paid tidak memiliki reservasi stok valid.
- Laporan rekonsiliasi harian untuk stok, coupon redemption, dan flash-sale quota.

## Release Gate

Status release tetap **NO-GO** bila salah satu kondisi berikut ada:

- `AUD-01` atau `AUD-02` belum ditutup.
- Ada order yang totalnya berasal dari nilai browser.
- Ada akses laporan/invoice lintas perusahaan tanpa permission konsolidasi.
- Test concurrency membuktikan overselling atau pemakaian promo melebihi batas.
- Transaksi paid dapat tercipta tanpa stok yang dijamin.
- Full backend test, Playwright, build, atau pemeriksaan working tree gagal.

## Definition of Done

Remediasi selesai jika seluruh `AUD-01` sampai `AUD-10` berstatus closed, setiap temuan mempunyai regression test, seluruh suite backend/E2E/build hijau, test concurrency tidak menghasilkan overselling atau over-redemption, working tree tetap bersih setelah Playwright, dan bukti hasil pengujian dicatat pada laporan release.

## Status Pelaksanaan

### Fase 0 — Baseline dan regression tests

Status: **selesai dibuat, seluruh kegagalan yang diharapkan sudah direproduksi** pada 19 September 2026.

Test yang ditambahkan atau diubah:

- `AuditCheckoutTrustBoundaryTest`: manipulasi harga produk, ongkir arbitrer, overselling, dan kuota flash sale habis.
- `AuditCompanyIsolationRegressionTest`: kebocoran laporan dan akses invoice lintas perusahaan.
- `AuditPromotionIntegrityRegressionTest`: varian flash sale lintas perusahaan, diskon persen di atas 100%, serta kode kupon yang seharusnya unik per perusahaan.
- `CouponCheckoutPolicyTest`: melarang controller checkout melakukan pola `check-then-increment` kupon secara langsung.
- `PlaywrightHarnessPolicyTest`: melarang screenshot runtime ditulis ke direktori dokumentasi yang dilacak Git.
- `ProductUpdateTest`: menambahkan format Rupiah `13.000,00` sebagai kasus regresi.

Baseline terarah: **12 gagal, 3 lulus**. Kegagalan tersebut adalah indikator bug yang akan dibuat hijau bertahap pada Fase 1–6, bukan kerusakan baru akibat test. Pemeriksaan teardown Playwright tetap dicatat sebagai verifikasi harness pada Fase 6 karena hang terjadi setelah semua skenario browser selesai.

### Fase 1 — Kalkulasi checkout server-authoritative

Status: **selesai** pada 19 September 2026. `AUD-01` ditutup.

Implementasi yang selesai:

- Snapshot item manual transfer dan Midtrans dibangun oleh `CheckoutPricingService` dari varian, produk, perusahaan, harga, flash sale, gambar, berat, dan stok di database.
- Nilai `name`, `image`, `price`, `companyId`, `shipping_cost`, `shipping_label`, dan `redeemPoints` dari browser tidak lagi menjadi sumber kebenaran transaksi.
- Endpoint RajaOngkir menghitung berat dari varian database dan menerbitkan quote token terenkripsi yang terikat ke perusahaan, tujuan, fingerprint item, berat, ongkir, label, dan kedaluwarsa 15 menit.
- Manual transfer dan Midtrans memverifikasi quote token dan menggunakan service pricing yang sama.
- Harga flash sale tidak dipakai jika kuota yang tercatat sudah habis.
- Callback Midtrans dengan signature valid tetapi `gross_amount` berbeda dari `grand_total` lokal ditolak.
- Frontend checkout mengirim identitas varian/kuantitas ke endpoint quote dan membawa `shipping_quote_token` terpilih ke endpoint pembayaran.

Bukti verifikasi:

- 5 regression test trust-boundary Fase 1 lulus, termasuk manipulasi snapshot manual, quote palsu, konflik nilai ongkir dengan quote valid, snapshot Midtrans, dan kuota flash sale habis.
- 55 test alur checkout guest/member/admin yang terdampak tetap lulus dengan 395 assertion.
- 6 test callback Midtrans lulus dengan 42 assertion.
- Test quote ongkir membuktikan berat request buatan diabaikan dan berat database yang dikirim ke provider.
- 5 skenario Playwright checkout lulus, termasuk happy path guest manual, kegagalan provider ongkir, email member, stok habis, dan bukti pembayaran invalid. Runner masih perlu dihentikan setelah test terakhir karena masalah teardown `AUD-10` belum masuk lingkup Fase 1.
- Full suite pada checkpoint awal Fase 1: **172 lulus, 9 gagal**. Kesembilan kegagalan adalah regression test untuk Fase 2–6 yang memang belum dikerjakan; tidak ada kegagalan baru dari Fase 1. Setelah penambahan pemeriksaan konflik quote, total test hijau terarah bertambah satu.

### Fase 2 — Isolasi perusahaan dan otorisasi

Status: **selesai** pada 20 September 2026. `AUD-02`, `AUD-03`, dan `AUD-05` ditutup.

Implementasi yang selesai:

- Seluruh query laporan mengikuti perusahaan aktif melalui scope terpusat; invoice admin dan mutasi flash sale juga memverifikasi kepemilikan perusahaan.
- Laporan lintas perusahaan dipisahkan ke route khusus dengan permission `reports.consolidated` dan menampilkan rincian per perusahaan serta total konsolidasi.
- Regression test membuktikan admin perusahaan A tidak dapat membaca invoice atau memasukkan varian perusahaan B, dan angka laporan berubah mengikuti perusahaan aktif.

### Fase 3 — Reservasi stok dan kuota flash sale

Status: **selesai** pada 20 September 2026. `AUD-04` dan `AUD-06` ditutup.

Implementasi yang selesai:

- Ditambahkan reservasi stok dan flash-sale persisten dengan status `reserved`, `committed`, dan `released`, expiry, foreign key, index, serta unique constraint idempotensi.
- Checkout mengunci varian dan item flash sale dalam urutan stabil, lalu menghitung stok/kuota tersedia setelah reservasi aktif.
- Cancel dan expiry melepaskan reservasi; proses fulfillment mengurangi stok fisik dan kuota tepat satu kali serta membuat satu stock movement.
- Callback atau approval pembayaran ditolak bila reservasi transaksi sudah dilepas.
- Command `commerce:release-expired-reservations` dijadwalkan setiap lima menit dan aman dijalankan ulang.

### Fase 4 — Kupon atomic dan perusahaan-aware

Status: **selesai** pada 20 September 2026. `AUD-07` dan `AUD-08` ditutup.

Implementasi yang selesai:

- Kode kupon dinormalisasi konsisten dan unique constraint diubah menjadi `company_id + normalized_code`, didahului backfill serta pemeriksaan collision.
- Apply dan checkout selalu mencari kupon dalam perusahaan transaksi; persentase dibatasi 1–100.
- Reservasi/redemption kupon memakai row lock, status idempotent, dan `used_count` direkonsiliasi ketika pembayaran sah.
- Retry, cancel, dan expiry tidak menambah penggunaan kupon dua kali.

### Fase 5 — Hardening input harga dan upload

Status: **selesai** pada 20 September 2026. `AUD-09` ditutup.

Implementasi yang selesai:

- Parser harga membedakan angka polos, ribuan Indonesia, format desimal database, dan format internasional; input ambigu atau invalid ditolak.
- Kasus regresi seperti `13.000,00` menghasilkan 13.000, bukan 1.300.000.
- File produk dan kategori yang baru diunggah dihapus kembali bila transaksi database gagal, sehingga record lama dan storage tidak meninggalkan orphan baru.

### Fase 6 — Stabilitas Playwright dan CI

Status: **selesai** pada 20 September 2026. `AUD-10` ditutup.

Implementasi yang selesai:

- Screenshot runtime diarahkan ke `test-results/`; test biasa tidak lagi menulis baseline ke `docs/`.
- Runner menyiapkan SQLite E2E, menjalankan PHP built-in server sebagai satu child process yang dimiliki runner, menunggu readiness, menjalankan Playwright langsung melalui Node, lalu menghentikan server pada blok cleanup.
- Mock fulfillment diselaraskan dengan kontrak quote token server-authoritative dan state error ongkir tidak lagi menampilkan banner sukses.

Bukti verifikasi final:

- Backend: **194 test lulus** dengan **1.156 assertion**.
- Browser E2E: **15 skenario lulus** dalam 2,2 menit dan runner selesai sendiri dengan exit code 0.
- Build production Vite berhasil.
- Setelah Playwright, tidak ada proses PHP/Node milik runner yang tertinggal dan tidak ada screenshot tracked yang berubah.

Status release gate: **GO** untuk cakupan `AUD-01` sampai `AUD-10`.
