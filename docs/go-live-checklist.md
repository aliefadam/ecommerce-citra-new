# Go-Live Checklist dan Sign-Off

Release candidate/commit: `________________`  
Environment: `________________`  
Release owner: `________________`  
Tanggal/jam keputusan: `________________`

## Freeze dan automated gate

- [ ] Feature freeze aktif; hanya perbaikan release blocker yang boleh masuk.
- [ ] Branch/commit final dicatat dan dependency lockfile tidak berubah setelah gate.
- [ ] `scripts/release-gate.ps1 -SkipInstall` lulus pada runner bersih/release candidate.
- [ ] Backend, E2E, build, Composer audit, npm audit, dan whitespace gate lulus.
- [ ] `php artisan ops:production-check` exit 0 pada environment production.

## Data, recovery, dan monitoring

- [ ] Backup awal production dibuat ke off-server storage.
- [ ] Artifact tersebut lulus `ops:restore-verify`.
- [ ] Restore penuh di environment terisolasi lulus; RPO/RTO aktual dicatat.
- [ ] Database, queue, disk, backup freshness, HTTP error, dan provider alerts diterima operator yang bertugas.
- [ ] Dashboard/log dapat ditelusuri memakai request ID dan order ID.

## Integrasi dan smoke

- [ ] Midtrans checkout → callback → database/dashboard direkonsiliasi.
- [ ] RajaOngkir cost dan satu AWB valid lulus.
- [ ] Email internal allowlist diterima dan domain authentication diperiksa.
- [ ] WhatsApp lulus bila termasuk release scope; bila tidak, `RELEASE_WHATSAPP_REQUIRED=false` disetujui owner.
- [ ] Production smoke memakai produk/nominal/penerima yang disetujui dan tidak meninggalkan order palsu tanpa rekonsiliasi.

## Load, security, dan support

- [ ] Browse 100 VU, checkout 20/min, callback 10/s burst, serta soak test memenuhi target PRD.
- [ ] Tidak ada stok negatif, duplicate order/payment/stock movement, atau deadlock tak tertangani.
- [ ] Critical/high security finding nol; risiko medium memiliki owner dan due date.
- [ ] Support dan operator memahami runbook incident/rollback.
- [ ] Pilot traffic limit, maintenance window, dan rollback owner ditentukan.

## Keputusan

- [ ] **GO untuk pilot**
- [ ] **NO-GO**

Alasan/exception: `____________________________________________________________`

Persetujuan release owner: `________________`  Tanggal: `________________`

Persetujuan technical owner: `______________`  Tanggal: `________________`

Persetujuan operations owner: `_____________`  Tanggal: `________________`
