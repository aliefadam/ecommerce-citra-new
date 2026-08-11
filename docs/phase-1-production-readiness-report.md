# Phase 1 Production Readiness Report

Tanggal verifikasi: 11 Agustus 2026

## Status

**PASS — seluruh exit criteria Phase 1 terpenuhi.**

Phase ini menutup baseline test yang gagal dan dependency security yang diketahui. Hasil ini belum mencakup perluasan E2E, sertifikasi integrasi sandbox/live, load test, backup/restore, monitoring, atau audit aplikasi pada phase berikutnya.

## Baseline Sebelum Perbaikan

| Pemeriksaan | Hasil |
|---|---|
| Backend tests | 95 passed, 1 failed; 547 assertions |
| Browser E2E | 5 passed |
| Composer audit | 31 advisory pada 11 package; terdapat severity high |
| NPM production audit | 0 vulnerability |

Tes backend yang gagal:

```text
AdminTransactionsUsabilityTest::test_manual_transaction_create_form_renders_customer_product_and_totals
```

Penyebabnya adalah assertion lama masih mengharapkan seluruh data customer dan produk tertanam di HTML, sedangkan form transaksi manual sudah memakai endpoint pencarian AJAX.

## Perubahan

### Test transaksi manual

- Mengganti assertion data statis dengan verifikasi URL endpoint AJAX.
- Memverifikasi endpoint customer mengembalikan ID, nama, email, dan alamat yang sesuai.
- Memverifikasi endpoint produk mengembalikan variant dan product yang sesuai.
- Tetap memverifikasi form, kalkulasi grand total, dan endpoint submit tersedia.

### Dependency security

Package utama yang diperbarui:

| Package | Sebelum | Sesudah |
|---|---:|---:|
| `laravel/framework` | 13.6.0 | 13.24.0 |
| `guzzlehttp/guzzle` | 7.10.0 | 7.15.3 |
| `guzzlehttp/psr7` | 2.9.0 | 2.13.0 |
| `league/commonmark` | 2.8.2 | 2.9.2 |
| `phpseclib/phpseclib` | 3.0.52 | 3.0.56 |
| `symfony/http-foundation` | 7.4.8 | 7.4.16 |
| `symfony/http-kernel` | 7.4.8 | 7.4.16 |
| `symfony/mailer` | 7.4.8 | 7.4.15 |
| `symfony/mime` | 7.4.8 | 7.4.16 |
| `symfony/routing` | 7.4.8 | 7.4.15 |

### PHPSpreadsheet

Setelah patch dependency pertama, masih ada empat advisory critical/high pada PHPSpreadsheet 1.30.4. Versi tersebut terkunci oleh `maatwebsite/excel` 3.1.

Penggunaan Laravel Excel di aplikasi hanya membungkus download satu template, sementara proses import sudah menggunakan PHPSpreadsheet secara langsung. Karena itu:

- `maatwebsite/excel` dilepas;
- `phpoffice/phpspreadsheet` diperbarui dari 1.30.4 ke 5.9.0;
- pembuatan template XLSX menggunakan `Spreadsheet` dan `Xlsx` secara langsung;
- ditambahkan regression test yang membuka file hasil download dan memverifikasi cell header serta contoh data.

Fitur download template dan import Excel tetap tersedia.

## Hasil Akhir Release Gate

| Gate | Command | Hasil |
|---|---|---|
| Composer manifest | `composer validate --no-check-publish` | PASS |
| PHP dependency audit | `composer audit --no-interaction` | PASS — 0 advisory |
| NPM production audit | `npm audit --omit=dev --audit-level=high` | PASS — 0 vulnerability |
| Backend regression | `php artisan test` | PASS — 97 tests, 564 assertions |
| Browser E2E | `npm run test:e2e` | PASS — 5 tests |
| Patch whitespace | `git diff --check` | PASS |

## Release Checklist Berulang

Jalankan dari root repository pada release candidate yang lockfile-nya sudah final:

```text
composer install --no-interaction --prefer-dist
composer validate --no-check-publish
composer audit --no-interaction
npm ci
npm audit --omit=dev --audit-level=high
php artisan test
npm run test:e2e
```

Checklist keputusan:

- [ ] Instalasi dari lockfile berhasil di environment bersih.
- [ ] Composer audit tidak memiliki advisory critical/high.
- [ ] NPM production audit tidak memiliki vulnerability critical/high.
- [ ] Seluruh backend test hijau.
- [ ] Seluruh critical browser E2E hijau.
- [ ] Tidak ada credential production pada environment test.
- [ ] Artifact test/audit disimpan bersama catatan release.
- [ ] Perubahan dependency dan migration ditinjau sebelum deployment.

Jika salah satu gate gagal, release berstatus **NO-GO** sampai penyebabnya diperbaiki atau, khusus advisory medium/low, diterima tertulis sesuai PRD.

## Risiko Tersisa di Luar Phase 1

- Browser E2E masih hanya mencakup guest checkout.
- Belum ada sertifikasi sandbox/live Midtrans, RajaOngkir, email, dan WhatsApp.
- Belum ada load/soak test.
- Belum ada bukti backup dapat direstore.
- Logging, monitoring, dan alerting production belum diverifikasi.
- Audit authorization/IDOR dan security aplikasi menyeluruh masuk phase berikutnya.

Risiko tersebut tidak membatalkan keberhasilan Phase 1, tetapi masih menjadi blocker sebelum klaim production-ready penuh sesuai `docs/prd-production-readiness-stabilization.md`.
