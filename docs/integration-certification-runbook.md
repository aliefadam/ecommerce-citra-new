# Runbook Sertifikasi Integrasi Eksternal

Runbook ini dipakai sebelum rilis untuk memeriksa Midtrans, RajaOngkir, SMTP, dan WA Gateway secara terkontrol. Jangan menaruh credential, alamat lengkap, nomor telepon, atau payload customer di tiket maupun artifact.

## Mode dan pengaman

- `fake`: hanya automated test; perintah menolak network call.
- `sandbox`: mode default untuk sertifikasi.
- `production`: network smoke diblokir kecuali operator menetapkan `INTEGRATION_SMOKE_ALLOW_PRODUCTION=true` setelah menyetujui nominal, produk, dan penerima.
- Email dan WhatsApp wajib memakai penerima internal yang tercantum di allowlist.
- Retry hanya berlaku untuk operasi baca/idempotent. Charge Midtrans dan mutasi sesi WA tidak di-retry otomatis.

Konfigurasi utama ada di `.env.example`. Setelah perubahan environment, jalankan `php artisan config:clear` atau bangun ulang config cache pada deployment.

## Preflight

```bash
php artisan integrations:certify all
```

Preflight hanya memastikan konfigurasi tersedia. Hasil ini bukan bukti bahwa provider dapat menerima request.

## Midtrans

1. Buat satu order sandbox dari checkout dengan produk dan nominal khusus test.
2. Selesaikan pembayaran melalui simulator Midtrans.
3. Pastikan callback diterima dan order lokal berubah ke status yang sesuai.
4. Jalankan pemeriksaan baca yang aman:

```bash
php artisan integrations:certify midtrans --execute --order-id=ORDER-SANDBOX
```

Rekonsiliasi:

- cocokkan `order_id`, gross amount, payment type, transaction status, dan provider reference di database dengan dashboard Midtrans;
- pastikan satu callback duplikat tidak menggandakan transaksi, stok, poin, email, maupun status history;
- jika status lokal tertinggal, simpan certification ID dan order ID, cek log callback, lalu baca ulang status provider; jangan mengubah status finansial langsung tanpa bukti provider;
- untuk production, gunakan order bernominal kecil yang disetujui operator, lalu refund/cancel sesuai SOP merchant.

## RajaOngkir

Cost smoke dapat memakai origin/destination tersimpan atau parameter eksplisit:

```bash
php artisan integrations:certify rajaongkir --execute --origin=ORIGIN_ID --destination=DESTINATION_ID --weight=1000
```

Tracking memerlukan AWB internal yang aman untuk pengujian:

```bash
php artisan integrations:certify rajaongkir --execute --origin=ORIGIN_ID --destination=DESTINATION_ID --awb=AWB --courier=jne
```

Cocokkan pilihan kurir, service, biaya, estimasi, dan timeline dengan dashboard/provider. Kegagalan tracking tidak boleh menghapus nomor resi atau mengubah status internal order.

## Email

Tambahkan alamat internal ke `INTEGRATION_SMOKE_EMAIL_ALLOWLIST`, lalu:

```bash
php artisan integrations:certify email --execute --email=internal@example.com
```

Periksa inbox/spam, sender, subject, timestamp, dan rendering. Untuk production, verifikasi SPF, DKIM, dan DMARC melalui provider/domain administrator. Jangan memakai alamat customer sebagai penerima smoke.

## WhatsApp

Status sesi dapat diperiksa dengan:

```bash
php artisan integrations:certify whatsapp --execute --wa-store=STORE_ID
```

Kontrak WA Gateway yang saat ini terintegrasi hanya menyediakan pengelolaan store/session, QR, status, dan usage. Belum ada endpoint kirim pesan pada aplikasi, sehingga command hanya memverifikasi reachability dan sesi connected serta melaporkan `PARTIAL`. Smoke-send baru boleh ditambahkan setelah endpoint resmi, mekanisme idempotency, normalisasi nomor, template, dan `INTEGRATION_SMOKE_WHATSAPP_ALLOWLIST` tersedia.

## Bukti dan keputusan rilis

Simpan output yang sudah dimask bersama tanggal, environment, commit, certification ID, operator, dan hasil rekonsiliasi. Status `PARTIAL` atau `FAIL` bukan kelulusan provider. Rilis yang membutuhkan provider tersebut harus menunggu bukti happy path dan controlled failure yang lengkap atau menerima risk acceptance tertulis.
