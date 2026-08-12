# Phase 4 Operations, Recovery, and Incident Runbook

## Health dan alert

- Liveness publik: `GET /up`. Tidak memanggil provider eksternal.
- Readiness internal: `GET /internal/ready` dengan header `X-Operations-Token`. Token wajib berasal dari secret manager dan route sengaja menjawab 404 jika token salah/kosong.
- Probe terjadwal: `php artisan ops:check`. Exit code non-zero berarti ada kondisi critical.
- Release guard: `php artisan ops:production-check` wajib exit 0 sebelum deployment production.
- Alert harus diarahkan oleh platform/cron ke kanal yang dipantau; aplikasi tidak menebak webhook produksi.

Threshold awal:

- failed job lebih dari 0: critical;
- queue tertua lebih dari 10 menit: critical;
- backup lebih tua dari 26 jam: critical;
- disk 80%: warning; 90%: critical;
- readiness database/cache/storage gagal: critical.

## Backup dan restore

Set `OPS_BACKUP_PASSWORD` minimal 16 karakter dan simpan di secret manager. `OPS_BACKUP_DISK` production harus menunjuk storage off-server. Untuk MySQL, set `OPS_MYSQLDUMP_BINARY`; password diteruskan lewat environment proses dan tidak ditulis ke command line.

```bash
php artisan ops:backup
php artisan ops:restore-verify backups/NAMA-ARTIFACT.zip
```

Backup mencakup consistent database snapshot, faktur pajak privat, bukti pembayaran privat baru, serta bukti pembayaran legacy. Seluruh entry ZIP memakai AES-256 dan SHA-256 manifest. Verifikasi tidak menimpa database aktif.

Restore penuh wajib dilakukan di environment terisolasi:

1. hentikan worker dan aktifkan maintenance mode pada target terisolasi;
2. unduh artifact dari backup disk dan jalankan `ops:restore-verify`;
3. ekstrak memakai password dari secret manager;
4. restore `database.sql` dengan akun restore terbatas atau ganti SQLite snapshot;
5. salin file private/public ke disk target tanpa membuka akses publik baru;
6. jalankan migration status, integrity queries, backend tests, dan smoke checkout;
7. catat waktu mulai/selesai sebagai RTO dan umur backup sebagai RPO;
8. jangan pernah menjalankan drill terhadap database production aktif.

Target pilot: RPO 24 jam, RTO 4 jam. Simpan minimum 7 daily dan 4 weekly/off-server; retention aplikasi default 30 hari.

## Tanggap insiden

### Midtrans callback tertunda/gagal

Periksa request ID, order ID, signature rejection, dan status provider memakai `integrations:certify midtrans --execute --order-id=...`. Replay callback hanya dari sumber tepercaya; idempotency test harus tetap hijau. Jangan menandai paid tanpa bukti dashboard/provider.

### RajaOngkir gagal

Periksa timeout/rate limit dan jalankan controlled cost smoke. Checkout harus tetap diblokir dengan pesan aman bila ongkir belum tersedia. Tracking failure tidak boleh menghapus resi/status internal.

### Email/WhatsApp gagal

Pastikan order tetap tercatat. Cek failed jobs, sesi WA, allowlist, dan status provider. Jangan broadcast/retry mutasi tanpa idempotency. Gunakan invoice/download sebagai fallback customer.

### Queue menumpuk

Cek `ops:check`, worker, failed jobs, database/Redis, dan oldest job. Hentikan producer non-kritis bila backlog bertambah; tambah worker bertahap. Jangan menghapus job pembayaran sebelum payload/reference direkonsiliasi.

### Database unavailable atau disk penuh

Aktifkan maintenance mode bila write integrity terancam. Untuk disk warning, rotasi log dan pindahkan artifact yang memang sudah direplikasi; jangan menghapus backup terakhir. Untuk database, failover/restore sesuai provider kemudian jalankan readiness dan integrity checks.

### Kebocoran credential/data

Cabut akses, rotasi secret/provider key, invalidate session/token, simpan forensic artifact read-only, identifikasi request ID dan scope data, lalu eskalasi ke owner legal/security. Jangan menyalin PII atau secret ke chat/tiket umum.

## Deployment rollback

1. hentikan traffic write atau aktifkan maintenance mode;
2. pastikan backup pre-deploy dan restore verification lulus;
3. rollback aplikasi ke release artifact sebelumnya;
4. jangan rollback migration destruktif tanpa forward-fix atau prosedur data yang diuji;
5. clear/rebuild config, route, dan view cache;
6. restart queue worker;
7. jalankan `/up`, readiness, `ops:check`, smoke checkout, dan rekonsiliasi order selama window deployment;
8. buka traffic dan pantau error/latency/queue.

## Load test

Install k6 pada runner terisolasi lalu:

```bash
k6 run -e BASE_URL=https://staging.example.com -e VUS=100 -e DURATION=2m tests/Load/browse-smoke.js
```

Jangan arahkan ke production tanpa change window dan persetujuan. Script browse menetapkan error rate di bawah 1% dan p95 di bawah 2,5 detik. Checkout/callback load memerlukan dataset dan provider fake khusus staging agar tidak membuat charge nyata.
