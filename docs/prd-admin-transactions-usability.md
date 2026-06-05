# PRD: Admin Transactions Usability

## Ringkasan

Fitur ini meningkatkan halaman admin `Transactions` agar lebih mudah dipakai sebagai halaman kerja operasional harian. Fokus utama adalah membantu admin cepat menemukan transaksi yang perlu ditindaklanjuti, memahami prioritas, dan menjalankan aksi utama tanpa terlalu banyak klik.

Keputusan utama:

- Halaman transaksi tetap menjadi pusat kerja admin untuk order checkout.
- Informasi yang ditampilkan harus membantu keputusan operasional, bukan hanya data mentah.
- Aksi utama ditampilkan sesuai status transaksi.
- Menu titik tiga tetap dipakai untuk aksi sekunder.
- Perubahan ini tidak mengubah alur checkout customer.

## Tujuan

- Admin bisa cepat melihat jumlah transaksi yang perlu diverifikasi, diproses, atau dikirim.
- Admin bisa memfilter transaksi berdasarkan status tanpa mengetik di search.
- Admin bisa melihat metode pembayaran dan waktu order langsung dari tabel.
- Admin bisa menjalankan aksi utama langsung dari baris transaksi.
- Admin bisa mencetak resi beberapa transaksi sekaligus.
- Halaman tetap rapi dan mudah discan walaupun transaksi bertambah banyak.

## Non-Tujuan

- Tidak membuat sistem fulfillment eksternal.
- Tidak membuat integrasi tracking kurir real-time.
- Tidak mengubah status machine transaksi yang sudah ada.
- Tidak mengubah perhitungan total transaksi.
- Tidak mengubah halaman customer.

## User Stories

### Admin Operasional

- Sebagai admin, saya bisa melihat transaksi mana yang butuh tindakan hari ini.
- Sebagai admin, saya bisa memfilter transaksi berdasarkan status.
- Sebagai admin, saya bisa melihat metode pembayaran tanpa membuka detail.
- Sebagai admin, saya bisa melihat kapan transaksi dibuat dan apakah sudah terlalu lama pending.
- Sebagai admin, saya bisa klik aksi utama langsung dari tabel.
- Sebagai admin, saya bisa print resi untuk beberapa transaksi sekaligus.

### Owner / Supervisor

- Sebagai owner, saya bisa melihat ringkasan transaksi dan potensi pekerjaan operasional.
- Sebagai owner, saya bisa memantau transaksi pending, paid, process, dan kirim dari satu halaman.

## Scope Functional

### 1. Filter Status Cepat

Tambahkan filter status berbentuk tab atau segmented control di atas tabel.

Filter minimal:

- `Semua`
- `Menunggu Bayar`
- `Menunggu Verifikasi`
- `Dibayar`
- `Diproses`
- `Dikirim`
- `Dibatalkan`

Mapping status:

```text
Menunggu Bayar       = pending, menunggu
Menunggu Verifikasi  = menunggu_verifikasi
Dibayar              = paid, settlement, capture
Diproses             = process, processing
Dikirim              = kirim, shipping, shipped
Dibatalkan           = cancel, expire, deny, failed, dibatalkan
```

Behavior:

- Filter bekerja bersama search.
- Saat filter aktif, tabel hanya menampilkan status terkait.
- Tampilkan jumlah transaksi pada setiap filter jika memungkinkan.
- Filter default adalah `Semua`.

### 2. Kartu Ringkasan Kecil

Tambahkan kartu ringkasan di atas tabel atau di antara header dan search.

Kartu minimal:

- `Menunggu Verifikasi`
- `Perlu Diproses`
- `Perlu Dikirim`
- `Transaksi Hari Ini`

Definisi:

```text
Menunggu Verifikasi = payment_type manual_transfer dan status menunggu_verifikasi
Perlu Diproses      = status paid, settlement, capture
Perlu Dikirim       = status process, processing
Transaksi Hari Ini  = transaksi dibuat pada tanggal hari ini
```

Tampilan:

- Setiap kartu menampilkan count.
- `Transaksi Hari Ini` menampilkan count dan total `grand_total`.
- Kartu bisa diklik untuk mengaktifkan filter terkait jika relevan.

### 3. Kolom Metode Pembayaran

Tambahkan kolom `Pembayaran` pada tabel.

Isi kolom:

- Label metode pembayaran, contoh:
  - `Transfer Manual`
  - `QRIS`
  - `BCA Virtual Account`
  - `BNI Virtual Account`
- Jika tersedia, tampilkan informasi pendukung kecil:
  - `Manual`
  - `VA`
  - `QRIS`

Behavior:

- Untuk transaksi manual transfer, beri visual kecil agar admin tahu transaksi mungkin butuh verifikasi.
- Jika data kosong, tampilkan `-`.

### 4. Kolom Tanggal / Umur Order

Tambahkan kolom `Tanggal` atau `Order Time`.

Isi kolom:

- Tanggal transaksi dibuat.
- Umur order relatif, contoh:
  - `2 jam lalu`
  - `Kemarin`
  - `04 Jun 2026 18:07`

Behavior:

- Untuk transaksi pending/manual yang sudah mendekati expired, tampilkan aksen peringatan jika data `expires_at` ada.
- Sorting default tetap transaksi terbaru.

### 5. Highlight Baris Butuh Aksi

Tambahkan aksen visual pada baris transaksi yang perlu tindakan.

Rules:

- `menunggu_verifikasi`: aksen amber, karena perlu verifikasi manual.
- `paid`, `settlement`, `capture`: aksen blue/emerald, karena perlu diproses.
- `process`, `processing`: aksen indigo/blue, karena perlu dikirim.
- `kirim`: netral atau amber ringan, karena sedang berjalan.
- `dibatalkan` dan status gagal: redup atau red subtle.

Behavior:

- Highlight tidak boleh membuat tabel sulit dibaca.
- Badge status tetap menjadi indikator utama.
- Aksen cukup berupa border kiri, background tipis, atau icon kecil.

### 6. Primary Action Langsung

Tambahkan tombol aksi utama pada kolom `Aksi` sesuai status transaksi.

Rules:

```text
manual_transfer + menunggu_verifikasi = Verifikasi
paid/settlement/capture               = Proses
process/processing                    = Kirim
kirim + tracking_number               = Print Resi atau Lacak
pending                               = Detail atau Cek Status
dibatalkan                            = Detail
```

Behavior:

- Tombol utama tampil langsung di baris, bukan hanya di dropdown titik tiga.
- Dropdown titik tiga tetap berisi aksi sekunder:
  - Show Detail
  - Halaman Detail
  - Print Invoice
  - Print Resi
  - Lacak Pesanan
- Tombol utama harus menyesuaikan permission admin.
- Jika aksi tidak tersedia, tampilkan menu titik tiga saja.

### 7. Bulk Action Ringan

Tambahkan checkbox per transaksi dan checkbox pilih semua pada halaman aktif.

Bulk action minimal:

- `Print Resi Terpilih`

Kriteria transaksi yang bisa dipilih untuk print resi:

- Memiliki data alamat pengiriman.
- Memiliki `shipping_label` jika tersedia.
- Memiliki `tracking_number` jika status sudah `kirim`.

Behavior:

- Tombol bulk action muncul setelah minimal satu transaksi dipilih.
- Jika ada transaksi tidak valid untuk print resi, tampilkan pesan jelas.
- Print resi terpilih membuka halaman print berisi beberapa label berurutan.
- Tidak perlu bulk process/approve pada fase pertama untuk menghindari salah klik operasional.

## UI / UX Requirements

- Tabel tetap mudah discan di desktop.
- Hindari menambah terlalu banyak teks dalam satu cell.
- Kolom prioritas:
  1. Invoice
  2. Customer
  3. Tanggal
  4. Pembayaran
  5. Status
  6. Grand Total
  7. Aksi
- Pada layar kecil, tabel boleh tetap horizontal scroll seperti sekarang.
- Gunakan badge, icon, dan warna ringan untuk mempercepat scanning.
- Empty state harus jelas saat filter tidak menemukan transaksi.

## Data Requirements

Data yang dibutuhkan dari `transactions`:

- `id`
- `invoice_no`
- `order_id`
- `status`
- `payment_type`
- `payment_method`
- `payment_va_bank`
- `payment_va_number`
- `payment_qr_url`
- `payment_proof_path`
- `payment_admin_note`
- `shipping_label`
- `tracking_number`
- `shipping_recipient_name`
- `shipping_phone`
- `shipping_address_line`
- `shipping_city`
- `shipping_province`
- `shipping_postal_code`
- `grand_total`
- `created_at`
- `expires_at`

Relasi:

- `user`
- `details`

## Acceptance Criteria

- Admin bisa memfilter transaksi berdasarkan status cepat.
- Search tetap bekerja saat filter status aktif.
- Kartu ringkasan menampilkan count yang sesuai status.
- Kartu ringkasan `Transaksi Hari Ini` menampilkan total transaksi hari ini.
- Tabel menampilkan metode pembayaran.
- Tabel menampilkan tanggal atau umur order.
- Baris transaksi yang perlu tindakan memiliki aksen visual.
- Baris manual transfer `menunggu_verifikasi` memiliki tombol `Verifikasi`.
- Baris paid/settlement/capture memiliki tombol `Proses`.
- Baris process memiliki tombol `Kirim`.
- Dropdown aksi tetap memiliki detail, invoice, dan resi.
- Admin bisa memilih beberapa transaksi dan klik `Print Resi Terpilih`.
- Print resi terpilih menampilkan beberapa label dalam satu halaman print.
- Transaksi tanpa data resi/alamat tidak menyebabkan error dan diberi pesan yang jelas.

## Edge Cases

- Tidak ada transaksi pada filter tertentu.
- Transaksi manual transfer belum upload bukti pembayaran.
- Transaksi paid dari Midtrans tidak perlu verifikasi manual.
- Transaksi sudah dikirim tapi nomor resi kosong.
- Transaksi tidak memiliki alamat snapshot.
- Admin tidak punya permission untuk proses/kirim/verifikasi.
- Search menghasilkan nol data saat filter aktif.
- Banyak transaksi dipilih lintas halaman pagination.

## Implementation Steps

### Phase 1: Filter dan Ringkasan

1. Tambahkan mapping status di JavaScript halaman transaksi.
2. Tambahkan segmented filter status.
3. Integrasikan filter status dengan search tabel.
4. Tambahkan kartu ringkasan.
5. Buat kartu ringkasan bisa mengaktifkan filter terkait.

### Phase 2: Kolom Tabel

1. Tambahkan `created_at`, `expires_at`, dan payment fields ke payload transaksi.
2. Tambahkan kolom `Tanggal`.
3. Tambahkan kolom `Pembayaran`.
4. Update render row agar tetap responsif.
5. Tambahkan highlight baris berdasarkan status.

### Phase 3: Primary Action

1. Tambahkan helper untuk menentukan primary action dari status.
2. Render tombol primary action di kolom aksi.
3. Hubungkan tombol ke modal/verifikasi/proses/kirim yang sudah ada.
4. Pastikan dropdown titik tiga tetap memuat aksi sekunder.

### Phase 4: Bulk Print Resi

1. Tambahkan checkbox per row dan select-all.
2. Simpan selected transaction ids di state JavaScript.
3. Tambahkan toolbar bulk action.
4. Buat route print resi bulk.
5. Buat view print multi-label berdasarkan transaksi terpilih.
6. Validasi transaksi yang tidak bisa dicetak.

### Phase 5: Testing

1. Test filter status `Semua`.
2. Test filter `Menunggu Verifikasi`.
3. Test search saat filter aktif.
4. Test kartu ringkasan count.
5. Test tombol `Verifikasi` pada manual transfer.
6. Test tombol `Proses` pada paid.
7. Test tombol `Kirim` pada process.
8. Test print resi single tetap jalan.
9. Test print resi bulk dengan beberapa transaksi valid.
10. Test print resi bulk dengan transaksi yang belum punya resi/alamat.

lihat dan baca file docs/prd-admin-transactions-usability.md lalu jakankan Phase 2: Kolom Tabel
lihat dan baca file docs/prd-admin-transactions-usability.md lalu jakankan Phase 3: Primary Action
lihat dan baca file docs/prd-admin-transactions-usability.md lalu jakankan Phase 4: Bulk Print Resi
lihat dan baca file docs/prd-admin-transactions-usability.md lalu jakankan Phase 5: Testing


## Open Questions

- Apakah bulk selection perlu tetap tersimpan saat pindah halaman pagination?
- Apakah transaksi pending perlu tombol `Batalkan` dari admin di fase ini?
- Apakah filter tanggal perlu ditambahkan juga, misalnya `Hari Ini`, `7 Hari`, `Bulan Ini`?
- Apakah kolom email masih perlu dipertahankan jika kolom tanggal dan pembayaran ditambahkan?
- Apakah print resi bulk perlu langsung auto-print atau cukup membuka halaman print?
