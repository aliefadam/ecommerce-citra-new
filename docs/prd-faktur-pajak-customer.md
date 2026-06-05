# PRD: Permintaan Faktur Pajak Customer

## Ringkasan

Fitur ini menambahkan alur permintaan faktur pajak untuk transaksi customer. Customer dapat mengisi data wajib pajak seperti nama NPWP, nomor NPWP, alamat NPWP, dan email penerima. Admin/finance kemudian memproses permintaan tersebut, membuat faktur pajak di sistem pajak resmi yang digunakan perusahaan, mengunggah file faktur pajak ke sistem, lalu mengirimkannya ke customer melalui email dan/atau menyediakan file download di detail transaksi.

Keputusan utama:

- Fitur ini adalah alur permintaan dan distribusi faktur pajak, bukan generator faktur pajak resmi otomatis.
- PPN yang sudah tercatat pada transaksi tetap menjadi sumber nilai transaksi.
- Data NPWP disimpan sebagai snapshot per transaksi agar tidak berubah ketika customer mengubah profil pajaknya.
- Customer dapat meminta faktur pajak saat checkout atau dari detail transaksi setelah checkout.
- Admin/finance bertanggung jawab memvalidasi data dan mengunggah file faktur pajak.
- Setelah faktur pajak tersedia, customer bisa menerima email dan melihat file di detail transaksi.

## Tujuan

- Customer bisa meminta faktur pajak untuk transaksi yang memiliki komponen PPN.
- Customer bisa mengisi data nama NPWP, nomor NPWP, alamat NPWP, dan email penerima faktur pajak.
- Customer bisa menyimpan data wajib pajak sebagai profil agar tidak perlu mengisi ulang pada transaksi berikutnya.
- Admin/finance bisa melihat daftar permintaan faktur pajak yang perlu diproses.
- Admin/finance bisa mengubah status permintaan faktur pajak.
- Admin/finance bisa mengunggah file faktur pajak.
- Sistem bisa mengirim email ke customer ketika faktur pajak sudah tersedia.
- Customer bisa mengunduh faktur pajak dari detail transaksi.
- Data sensitif seperti nomor NPWP ditampilkan dengan masking pada area yang tidak membutuhkan akses penuh.

## Non-Tujuan

- Tidak membuat integrasi otomatis ke DJP/Coretax pada fase pertama.
- Tidak membuat nomor faktur pajak secara otomatis.
- Tidak mengubah perhitungan PPN checkout yang sudah ada.
- Tidak mengubah invoice transaksi existing menjadi faktur pajak resmi.
- Tidak menjamin validitas NPWP secara otomatis pada fase pertama.
- Tidak mengirim faktur pajak sebelum admin/finance mengunggah file.
- Tidak membuka akses data NPWP penuh untuk semua role admin.

## Definisi

### Invoice Transaksi

Invoice transaksi adalah dokumen internal ecommerce yang menunjukkan ringkasan pesanan, item, pembayaran, pengiriman, subtotal, PPN, dan total.

Invoice transaksi bukan faktur pajak resmi.

### Faktur Pajak

Faktur pajak adalah dokumen pajak yang dibuat oleh pihak perusahaan melalui sistem pajak resmi atau proses perpajakan yang berlaku. Pada sistem ecommerce ini, faktur pajak diperlakukan sebagai file hasil proses admin/finance yang dilampirkan ke transaksi.

### Data Wajib Pajak Customer

Data wajib pajak customer adalah data yang digunakan untuk memproses permintaan faktur pajak:

- Nama NPWP.
- Nomor NPWP.
- Alamat NPWP.
- Email penerima faktur pajak.
- Catatan tambahan opsional.

## User Stories

### Customer

- Sebagai customer, saya bisa memilih bahwa saya membutuhkan faktur pajak saat checkout.
- Sebagai customer, saya bisa mengisi nama NPWP, nomor NPWP, alamat NPWP, dan email penerima.
- Sebagai customer, saya bisa meminta faktur pajak dari detail transaksi jika lupa meminta saat checkout.
- Sebagai customer, saya bisa melihat status permintaan faktur pajak.
- Sebagai customer, saya bisa menerima email ketika faktur pajak sudah tersedia.
- Sebagai customer, saya bisa mengunduh file faktur pajak dari detail transaksi.
- Sebagai customer, saya bisa menggunakan data wajib pajak yang pernah saya simpan.

### Admin / Finance

- Sebagai admin/finance, saya bisa melihat daftar transaksi yang meminta faktur pajak.
- Sebagai admin/finance, saya bisa membuka detail data wajib pajak customer.
- Sebagai admin/finance, saya bisa menandai permintaan sebagai sedang diproses.
- Sebagai admin/finance, saya bisa menolak permintaan jika data tidak valid atau tidak lengkap.
- Sebagai admin/finance, saya bisa mengunggah file faktur pajak.
- Sebagai admin/finance, saya bisa mencatat nomor faktur pajak dan tanggal faktur pajak.
- Sebagai admin/finance, saya bisa mengirim ulang email faktur pajak ke customer.
- Sebagai admin/finance, saya bisa melihat histori perubahan status permintaan.

### Owner / Supervisor

- Sebagai owner, saya bisa memantau jumlah permintaan faktur pajak.
- Sebagai owner, saya bisa melihat permintaan yang belum diproses.
- Sebagai owner, saya bisa membatasi role yang boleh melihat nomor NPWP penuh.
- Sebagai owner, saya bisa memastikan file faktur pajak hanya tersedia untuk transaksi terkait.

## Scope Functional

### 1. Permintaan Saat Checkout

Tambahkan opsi pada halaman checkout:

```text
[ ] Saya membutuhkan faktur pajak
```

Jika dipilih, tampilkan form data wajib pajak:

- Nama NPWP.
- Nomor NPWP.
- Alamat NPWP.
- Email penerima faktur pajak.
- Catatan opsional.
- Checkbox opsional untuk menyimpan data ini sebagai profil wajib pajak.

Behavior:

- Field wajib hanya aktif ketika customer mencentang opsi membutuhkan faktur pajak.
- Data wajib pajak disimpan sebagai snapshot pada transaksi.
- Permintaan faktur pajak tidak boleh mengubah total transaksi.
- Jika transaksi tidak memiliki PPN, sistem tetap boleh menerima permintaan tetapi admin diberi indikator khusus.

### 2. Permintaan Dari Detail Transaksi

Tambahkan aksi di detail transaksi customer:

```text
Minta Faktur Pajak
```

Behavior:

- Aksi tersedia jika transaksi sudah berhasil dibuat.
- Jika faktur pajak belum pernah diminta, customer dapat mengisi form data wajib pajak.
- Jika sudah diminta, customer melihat status permintaan.
- Jika faktur pajak sudah tersedia, customer melihat tombol download.
- Jika permintaan ditolak, customer melihat alasan penolakan dan dapat mengajukan ulang jika admin mengizinkan.

### 3. Profil Wajib Pajak Customer

Customer dapat menyimpan data wajib pajak agar bisa digunakan kembali.

Data profil:

- Customer ID.
- Nama NPWP.
- Nomor NPWP.
- Alamat NPWP.
- Email penerima.
- Default atau bukan default.

Behavior:

- Customer dapat memilih profil tersimpan saat checkout atau request dari detail transaksi.
- Ketika profil dipakai untuk transaksi, sistem tetap menyimpan snapshot ke transaksi.
- Perubahan profil setelah transaksi dibuat tidak mengubah data faktur pajak pada transaksi lama.

### 4. Status Permintaan Faktur Pajak

Status yang disarankan:

```text
not_requested
requested
processing
issued
sent
rejected
cancelled
```

Arti status:

- `not_requested`: transaksi tidak meminta faktur pajak.
- `requested`: customer sudah meminta faktur pajak.
- `processing`: admin/finance sedang memproses permintaan.
- `issued`: file faktur pajak sudah diunggah.
- `sent`: faktur pajak sudah dikirim ke customer.
- `rejected`: permintaan ditolak.
- `cancelled`: permintaan dibatalkan.

### 5. Admin Queue Faktur Pajak

Tambahkan menu admin:

```text
Admin -> Transactions -> Faktur Pajak
```

Atau jika lebih cocok:

```text
Admin -> Faktur Pajak
```

Daftar menampilkan:

- Nomor transaksi.
- Nama customer.
- Tanggal transaksi.
- Total transaksi.
- Nilai PPN.
- Status transaksi.
- Status faktur pajak.
- Nama NPWP.
- Nomor NPWP dengan masking.
- Tanggal request.
- Aksi detail.

Filter:

- Status faktur pajak.
- Tanggal transaksi.
- Tanggal request.
- Keyword transaksi/customer/NPWP.

### 6. Detail Admin Faktur Pajak

Halaman detail admin menampilkan:

- Ringkasan transaksi.
- Ringkasan nilai PPN.
- Data wajib pajak customer.
- Status permintaan.
- Catatan customer.
- Catatan admin.
- File faktur pajak jika sudah tersedia.
- Histori status.

Aksi admin:

- Tandai sedang diproses.
- Tolak permintaan dengan alasan.
- Upload file faktur pajak.
- Ubah nomor faktur pajak.
- Ubah tanggal faktur pajak.
- Kirim email ke customer.
- Kirim ulang email.

### 7. Upload File Faktur Pajak

Admin/finance dapat mengunggah file faktur pajak.

Requirement:

- Format file: PDF.
- Ukuran maksimum mengikuti konfigurasi aplikasi.
- File disimpan di storage private.
- File hanya dapat diakses oleh customer pemilik transaksi dan admin/finance yang berwenang.
- Upload mencatat admin yang mengunggah.
- Upload mencatat waktu upload.

Field upload:

- File faktur pajak.
- Nomor faktur pajak opsional.
- Tanggal faktur pajak opsional.
- Catatan admin opsional.
- Checkbox kirim email setelah upload.

### 8. Email Faktur Pajak

Ketika faktur pajak sudah tersedia, sistem dapat mengirim email ke customer.

Rekomendasi:

- Email berisi ringkasan transaksi, status faktur pajak, dan link download.
- Lampiran PDF bisa dipertimbangkan, tetapi link download lebih aman untuk kontrol akses dan audit.
- Link download harus membutuhkan autentikasi customer.
- Admin dapat mengirim ulang email dari halaman detail.

Penerima email:

- Email akun customer.
- Email penerima faktur pajak jika berbeda.

### 9. Download Customer

Customer dapat mengunduh file faktur pajak dari detail transaksi.

Behavior:

- Tombol download hanya muncul jika file sudah tersedia.
- Customer hanya bisa mengakses file untuk transaksinya sendiri.
- Sistem mencatat waktu download terakhir jika diperlukan.
- Jika faktur pajak dikirim via email, detail transaksi tetap menjadi sumber file terbaru.

### 10. Keamanan dan Privasi

Data NPWP termasuk data sensitif dan harus diperlakukan lebih hati-hati.

Requirement:

- Nomor NPWP dimasking di daftar admin dan daftar customer.
- Nomor NPWP penuh hanya tampil di halaman detail untuk role berwenang.
- File faktur pajak disimpan private.
- Download file menggunakan route terproteksi, bukan public URL langsung.
- Setiap perubahan status dan upload file dicatat.
- Permission admin dipisahkan dari permission transaksi umum.
- Data tidak boleh muncul di log aplikasi secara mentah.

## Data Requirements

### Tabel `user_tax_profiles`

Tabel opsional untuk menyimpan data wajib pajak customer yang bisa digunakan ulang.

Field yang disarankan:

- `id`
- `user_id`
- `taxpayer_name`
- `taxpayer_number`
- `taxpayer_address`
- `taxpayer_email`
- `is_default`
- `created_at`
- `updated_at`

### Tabel `transaction_tax_invoices`

Tabel utama untuk menyimpan request faktur pajak per transaksi.

Field yang disarankan:

- `id`
- `transaction_id`
- `requested_by_user_id`
- `status`
- `taxpayer_name`
- `taxpayer_number`
- `taxpayer_address`
- `taxpayer_email`
- `customer_note`
- `admin_note`
- `tax_invoice_number`
- `tax_invoice_date`
- `tax_invoice_file_path`
- `uploaded_by_admin_id`
- `requested_at`
- `processing_at`
- `issued_at`
- `sent_at`
- `rejected_at`
- `rejected_reason`
- `created_at`
- `updated_at`

Relasi:

- `Transaction` memiliki satu `TransactionTaxInvoice`.
- `TransactionTaxInvoice` milik satu `Transaction`.
- `TransactionTaxInvoice` milik satu `requestedByUser`.
- `TransactionTaxInvoice` dapat memiliki satu `uploadedByAdmin`.
- `User` dapat memiliki banyak `UserTaxProfile`.

Alasan memakai tabel terpisah:

- Data faktur pajak punya workflow sendiri.
- File, status, dan histori tidak membebani tabel transaksi.
- Lebih mudah menambah audit log atau histori status di fase berikutnya.

## Permissions

Permission admin yang disarankan:

- `tax_invoices.index`
- `tax_invoices.show`
- `tax_invoices.process`
- `tax_invoices.reject`
- `tax_invoices.upload`
- `tax_invoices.send`
- `tax_invoices.view_sensitive`

Behavior:

- Admin tanpa `tax_invoices.view_sensitive` hanya melihat nomor NPWP dalam bentuk masking.
- Upload dan pengiriman email hanya dapat dilakukan oleh role admin/finance berwenang.

## UI Requirements

### Customer Checkout

- Opsi faktur pajak tidak mendominasi checkout.
- Form data wajib pajak hanya muncul ketika checkbox dipilih.
- Jika customer punya profil wajib pajak, tampilkan pilihan profil.
- Tampilkan copy singkat bahwa faktur pajak akan diproses oleh admin setelah transaksi dibuat.

### Customer Detail Transaksi

- Tampilkan panel status faktur pajak.
- Jika belum diminta, tampilkan tombol minta faktur pajak.
- Jika sedang diproses, tampilkan status singkat.
- Jika tersedia, tampilkan tombol download.
- Jika ditolak, tampilkan alasan penolakan.

### Admin

- Queue admin harus mudah discan.
- Status faktur pajak harus terlihat jelas.
- Data NPWP penuh tidak tampil di tabel.
- Detail admin harus menampilkan ringkasan transaksi dan form proses faktur pajak dalam satu halaman.

## Validasi

Validasi saat customer request:

- Nama NPWP wajib jika meminta faktur pajak.
- Nomor NPWP wajib jika meminta faktur pajak.
- Alamat NPWP wajib jika meminta faktur pajak.
- Email penerima wajib dan harus format email valid.
- Nomor NPWP boleh menerima format 15 digit, 16 digit, atau format bertitik/berstrip selama disimpan dalam bentuk normalisasi yang konsisten.

Catatan:

- Validasi nomor NPWP jangan terlalu ketat pada fase pertama karena format identitas perpajakan dapat berubah.
- Validasi resmi dan aturan pajak perlu dikonfirmasi dengan admin pajak/konsultan pajak perusahaan sebelum implementasi final.

## Acceptance Criteria

- Customer dapat meminta faktur pajak saat checkout.
- Customer dapat meminta faktur pajak dari detail transaksi.
- Data nama NPWP, nomor NPWP, alamat NPWP, dan email tersimpan sebagai snapshot transaksi.
- Customer dapat memakai profil wajib pajak tersimpan.
- Admin dapat melihat daftar permintaan faktur pajak.
- Admin dapat melihat detail permintaan faktur pajak sesuai permission.
- Admin dapat mengubah status menjadi processing.
- Admin dapat menolak permintaan dengan alasan.
- Admin dapat mengunggah file PDF faktur pajak.
- Admin dapat mengirim email faktur pajak ke customer.
- Customer dapat mengunduh file faktur pajak setelah tersedia.
- Nomor NPWP dimasking pada daftar dan hanya tampil penuh untuk role berwenang.
- File faktur pajak tidak dapat diakses oleh customer lain.
- Fitur ini tidak mengubah total transaksi, PPN, payment gateway, atau status pembayaran existing.

## Edge Cases

- Customer meminta faktur pajak setelah transaksi selesai.
- Customer meminta faktur pajak untuk transaksi tanpa PPN.
- Customer salah mengisi NPWP dan admin menolak permintaan.
- Customer mengubah profil wajib pajak setelah request dibuat.
- Admin upload file salah lalu perlu mengganti file.
- Email gagal terkirim tetapi file sudah tersedia.
- Customer email penerima faktur berbeda dari email akun.
- Transaksi dibatalkan setelah faktur pajak diminta.
- File faktur pajak dihapus dari storage secara tidak sengaja.

## Rencana Implementasi

### Phase 1: Data Model dan Permission

- Buat tabel `user_tax_profiles`.
- Buat tabel `transaction_tax_invoices`.
- Tambahkan relasi model.
- Tambahkan status enum/constant untuk faktur pajak.
- Tambahkan permission admin faktur pajak.
- Tambahkan helper masking nomor NPWP.

### Phase 2: Customer Request Faktur Pajak

- Tambahkan form faktur pajak di checkout.
- Tambahkan aksi minta faktur pajak di detail transaksi.
- Simpan snapshot data wajib pajak ke transaksi.
- Tambahkan fitur simpan profil wajib pajak customer.
- Tampilkan status faktur pajak di detail transaksi customer.

### Phase 3: Admin Queue dan Detail

- Tambahkan menu admin faktur pajak.
- Buat halaman daftar request faktur pajak.
- Buat filter status/tanggal/keyword.
- Buat halaman detail admin.
- Tambahkan aksi ubah status ke processing.
- Tambahkan aksi reject dengan alasan.

### Phase 4: Upload, Download, dan Email

- Tambahkan upload file PDF faktur pajak.
- Simpan file di storage private.
- Tambahkan route download terproteksi untuk customer dan admin.
- Tambahkan email notification faktur pajak tersedia.
- Tambahkan aksi kirim ulang email.

### Phase 5: Audit, Privacy, dan Hardening

- Tambahkan audit log perubahan status.
- Tambahkan audit log upload file.
- Pastikan nomor NPWP dimasking sesuai permission.
- Pastikan file tidak public.
- Pastikan data sensitif tidak masuk log.
- Tambahkan handling email gagal.

### Phase 6: Testing

- Test customer request saat checkout.
- Test customer request dari detail transaksi.
- Test profil wajib pajak tersimpan dan snapshot transaksi.
- Test admin queue menampilkan request.
- Test admin upload file PDF.
- Test customer download hanya untuk transaksi miliknya.
- Test customer lain tidak bisa download file.
- Test email dikirim ketika faktur pajak tersedia.
- Test permission admin untuk data sensitif.
- Test fitur tidak mengubah total transaksi dan PPN.

buka dan baca file docs/prd-faktur-pajak-customer.md. ### Phase 1: Data Model dan Permission
### Phase 1: Data Model dan Permission
buka dan baca file docs/prd-faktur-pajak-customer.md.
### Phase 2: Customer Request Faktur Pajak
buka dan baca file docs/prd-faktur-pajak-customer.md.
kerjakan ### Phase 3: Admin Queue dan Detail
buka dan baca file docs/prd-faktur-pajak-customer.md.
kerjakan ### Phase 4: Upload, Download, dan Email
buka dan baca file docs/prd-faktur-pajak-customer.md.
kerjakan ### Phase 5: Audit, Privacy, dan Hardening
buka dan baca file docs/prd-faktur-pajak-customer.md.
kerjakan ### Phase 6: Testing

## Open Questions

- Apakah faktur pajak hanya boleh diminta setelah transaksi dibayar?
- Berapa batas waktu customer boleh meminta faktur pajak setelah transaksi dibuat?
- Apakah permintaan faktur pajak untuk transaksi tanpa PPN boleh diterima atau harus ditolak otomatis?
- Apakah file faktur pajak dikirim sebagai attachment email atau hanya link download?
- Apakah admin boleh mengganti file faktur pajak setelah status `sent`?
- Apakah perlu SLA internal, misalnya diproses maksimal 3 hari kerja?
- Apakah perlu data tambahan seperti nama perusahaan, cabang, atau NITKU?
- Apakah nomor NPWP harus disimpan terenkripsi di database?
- Siapa role final yang boleh melihat nomor NPWP penuh?
- Apakah customer boleh memiliki lebih dari satu profil wajib pajak?

## Rekomendasi MVP

Untuk fase pertama, gunakan pendekatan berikut:

- Customer bisa request saat checkout dan dari detail transaksi.
- Simpan nama NPWP, nomor NPWP, alamat NPWP, dan email penerima.
- Admin/finance melihat queue request dan mengunggah PDF.
- Sistem mengirim email berisi link download, bukan attachment.
- File faktur pajak tetap tersedia di detail transaksi customer.
- Nomor NPWP dimasking di daftar dan hanya dibuka penuh di detail untuk role berwenang.
- Integrasi DJP/Coretax ditunda sampai proses manual stabil.
