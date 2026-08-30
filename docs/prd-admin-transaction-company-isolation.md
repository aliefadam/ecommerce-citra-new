# PRD — Isolasi Transaksi Admin per Perusahaan

> Status: Disetujui untuk implementasi  
> Tanggal: 2026-08-30  
> Area: Admin Transactions dan data operasional turunannya

## 1. Latar Belakang

Admin memiliki company switcher yang menetapkan perusahaan aktif. Namun halaman `/admin/transactions` dan beberapa endpoint operasional transaksi masih membaca record lintas perusahaan. Akibatnya, admin yang sedang bekerja dalam konteks PT A dapat melihat atau memproses transaksi PT B.

Tabel `transactions` sudah memiliki `company_id`, dan transaksi checkout maupun manual sudah dicatat ke perusahaan pemiliknya. Masalah ini merupakan gap query dan authorization, bukan gap struktur data.

## 2. Tujuan

- Semua tampilan dan aksi transaksi admin mengikuti perusahaan aktif.
- Mencegah kebocoran data dan perubahan record lintas perusahaan, termasuk melalui manipulasi URL/ID.
- Menyamakan perilaku Transactions dengan modul company-aware lain.
- Menyediakan regression test untuk batas akses antarperusahaan.

## 3. Keputusan Produk

Company switcher adalah konteks kerja, bukan sekadar filter tampilan. Ketika PT A aktif:

- Admin, termasuk superadmin, hanya melihat dan mengoperasikan transaksi PT A.
- Record milik perusahaan lain tidak ditemukan (`404`) pada endpoint berbasis ID.
- Data lintas perusahaan tidak diam-diam ditampilkan pada halaman operasional.

Tampilan konsolidasi lintas perusahaan tidak termasuk fase ini. Jika dibutuhkan, fitur tersebut harus dibuat sebagai halaman terpisah, diberi label “Semua Perusahaan”, memiliki permission khusus, dan tidak menggunakan endpoint mutasi transaksi biasa.

## 4. Ruang Lingkup

### 4.1 Transaksi utama

- Daftar dan detail transaksi.
- Verifikasi pembayaran.
- Proses pesanan dan pengiriman.
- Update pembayaran/pengiriman transaksi manual.
- Payment proof admin.
- Shipping label tunggal dan bulk.

### 4.2 Data turunan transaksi

Data berikut mengikuti `transactions.company_id` melalui relasi induknya:

- Permintaan faktur pajak, termasuk ringkasan status dan semua aksi.
- Return/refund, termasuk update status.
- Ulasan produk yang berasal dari transaksi, termasuk moderasi dan hapus.
- Notifikasi transaksi pada topbar admin.

### 4.3 Di luar ruang lingkup

- Riwayat transaksi customer pada storefront.
- Saldo poin/member tier yang memang bersifat global.
- Laporan konsolidasi lintas perusahaan.
- Perubahan struktur tabel atau migrasi data.

## 5. Aturan Fungsional

1. Semua query list wajib memiliki filter perusahaan aktif.
2. Semua summary/count harus memakai scope yang sama dengan list.
3. Semua endpoint yang menerima route model wajib memvalidasi kepemilikan record sebelum membaca file, menampilkan data, atau melakukan mutasi.
4. Guard berlaku juga untuk superadmin. Superadmin dapat mengganti perusahaan melalui switcher, tetapi tidak melewati konteks aktif.
5. Bulk action hanya memproses ID milik perusahaan aktif; ID perusahaan lain diabaikan dan tidak dirender.
6. Pelanggaran ownership mengembalikan `404` agar keberadaan record perusahaan lain tidak bocor.
7. Validasi dilakukan di backend; menyembunyikan tombol di UI tidak dianggap sebagai kontrol keamanan.

## 6. Kriteria Penerimaan

- Daftar transaksi PT A tidak memuat invoice PT B.
- Detail, payment proof, label, proses, kirim, verifikasi pembayaran, dan update transaksi manual PT B menghasilkan `404` saat PT A aktif.
- Bulk shipping label PT A tidak memuat transaksi PT B walaupun ID-nya dikirim manual.
- Daftar/count faktur pajak, retur, ulasan, dan notifikasi topbar hanya memakai transaksi perusahaan aktif.
- Aksi faktur pajak, retur, dan ulasan milik perusahaan lain menghasilkan `404`.
- Perilaku yang sama berlaku bagi superadmin.
- Seluruh test transaksi existing tetap lulus.

## 7. Pendekatan Teknis

- Reuse `ScopesToActiveCompany` sebagai sumber company ID aktif dan ownership guard.
- Tambahkan `where('company_id', activeCompanyId())` untuk `Transaction`.
- Untuk model turunan tanpa `company_id`, gunakan `whereHas('transaction', ...)`.
- Pada query lock/mutasi, ulangi company constraint sebagai defense-in-depth.
- Tambahkan feature test dua perusahaan untuk list, read endpoint, dan mutation endpoint.

## 8. Risiko dan Mitigasi

- **ID lintas perusahaan dikirim lewat URL/AJAX:** ditolak oleh server dengan `404`.
- **List sudah terfilter tetapi count masih global:** list dan summary memakai scope identik.
- **Superadmin mengira dapat melihat semua data:** company switcher tetap menjadi konteks; konsolidasi kelak harus eksplisit.
- **Modul turunan lupa di-scope:** cakupan regression test meliputi transaksi utama dan relasinya.

## 9. Rollout

Tidak diperlukan migrasi database. Perubahan dapat dirilis sebagai patch aplikasi setelah feature test dan regression test relevan lulus.
