# Pilot Review — 24 Jam, 72 Jam, dan 7 Hari

Release/commit: `________________`  
Pilot started at: `________________`  
Incident commander/on-call: `________________`

Gunakan data teragregasi; jangan salin email, telepon, alamat, credential, atau bukti pembayaran ke dokumen ini.

| Window | Traffic/orders | Error rate & p95 | Payment reconciliation | Queue/failed jobs | Provider success | Support ticket | Decision |
|---|---:|---|---|---|---|---|---|
| 24 jam | | | | | | | Continue / Hold / Rollback |
| 72 jam | | | | | | | Continue / Hold / Rollback |
| 7 hari | | | | | | | Expand / Hold / Rollback |

Untuk setiap window:

- [ ] jumlah order lokal cocok dengan payment/manual settlement yang direkonsiliasi;
- [ ] tidak ada duplicate side effect atau stok negatif;
- [ ] checkout conversion dan kegagalan shipping/payment dibandingkan baseline;
- [ ] backup terbaru dan queue age berada di bawah threshold;
- [ ] alert yang terjadi memiliki acknowledgement dan owner;
- [ ] ticket customer dikelompokkan berdasarkan akar masalah;
- [ ] risiko baru masuk risk register dengan owner/due date;
- [ ] keputusan traffic berikutnya ditandatangani release dan operations owner.
