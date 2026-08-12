# Phase 5 — Go-Live Readiness Review

Tanggal review: 12 Agustus 2026  
Environment yang tersedia: local/sandbox  
Keputusan saat ini: **NO-GO untuk production/pilot**

## Bukti yang lulus

- Phase 1 dependency remediation selesai; Composer/npm audit tidak memiliki critical/high vulnerability.
- Phase 2 critical checkout/payment/admin/refund/B2B automated coverage lulus.
- Phase 3 configuration guard, contract test, dan RajaOngkir cost sandbox lulus.
- Phase 4 correlation log, readiness, backup encryption, restore verification, operational probe, security hardening, dan runbook tersedia.
- Local MySQL backup/verification berhasil tanpa menimpa data aktif.
- Bukti pembayaran baru sudah privat dan ownership/company scope diuji.
- Critical release suite **65 tests / 436 assertions** lulus tiga kali berturut-turut tanpa retry tersembunyi.
- Full backend regression **140 tests / 793 assertions** lulus; Composer manifest/audit, npm audit, production build, dan diff check lulus.
- Browser E2E terakhir pada release candidate foundation **6/6 lulus** (Phase 4); Phase 5 hanya menambah command/config/dokumentasi release review tanpa mengubah UI runtime.

## Release blockers P0

| Blocker | Owner yang diperlukan | Bukti penutupan |
|---|---|---|
| Feature freeze dan sign-off belum aktif | Product/release owner | `RELEASE_FEATURE_FREEZE=true`, owner dan approval timestamp |
| Production configuration belum aman/tersedia | DevOps | `ops:production-check` exit 0 |
| Off-server initial backup belum tersedia | DBA/DevOps | artifact path dan restore drill isolated |
| Load, burst, dan soak belum dieksekusi | QA/performance | report k6 release candidate |
| Alert belum terbukti diterima operator | Operations | alert delivery timestamp dan incident acknowledgement |
| Midtrans sandbox/production smoke belum lengkap | Payment owner | checkout, callback, dashboard/database reconciliation |
| RajaOngkir tracking belum punya AWB valid | Fulfillment owner | cost + tracking certification |
| Email allowlist delivery belum dibuktikan | Operations/marketing | inbox delivery dan domain authentication |
| Production smoke belum dijalankan | Release owner | approved controlled smoke timestamp |

WhatsApp saat ini dianggap bukan P0 release scope (`RELEASE_WHATSAPP_REQUIRED=false`) karena aplikasi belum memiliki kontrak send-message. Jika notifikasi WA diwajibkan untuk pilot, status otomatis menjadi blocker sampai integrasi resmi, queue/fallback, idempotency, allowlist, dan delivery smoke tersedia.

## Perintah keputusan

```powershell
scripts/release-gate.ps1 -SkipInstall
php artisan ops:production-check
php artisan ops:go-live-review
```

Field `RELEASE_*` hanya boleh diisi setelah buktinya benar-benar ada. Command go-live menolak tanggal kedaluwarsa, artifact backup yang hilang, dan load report yang tidak ada.

Dokumen keputusan manual: `docs/go-live-checklist.md`. Monitoring pilot: `docs/pilot-review-template.md`.

Phase 5 implementasi review telah selesai, tetapi release tidak boleh dibuka sampai seluruh blocker P0 ditutup dan sign-off tertulis tersedia.
