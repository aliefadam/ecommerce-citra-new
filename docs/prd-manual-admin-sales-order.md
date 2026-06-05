# PRD: Manual Admin Sales Order

## Ringkasan

Fitur ini menambahkan alur pembuatan transaksi manual oleh admin/staff untuk customer yang melakukan pemesanan di luar checkout ecommerce, misalnya melalui chat, telepon, atau komunikasi langsung. Admin mencatat customer, item, harga, pembayaran, dan pengiriman dari panel admin.

Keputusan utama:

- Checkout ecommerce yang sudah ada tetap berjalan dan tidak diubah alurnya.
- Transaksi manual dibuat dari admin/staff, bukan dari customer.
- Transaksi manual dan transaksi checkout harus bisa dibedakan dengan jelas.
- Pembayaran manual dikelola oleh admin/staff.
- Pengiriman pada fase pertama menggunakan input manual agar fleksibel.
- Integrasi cek ongkir seperti RajaOngkir dapat ditambahkan sebagai fase lanjutan, bukan syarat MVP.

## Tujuan

- Admin/staff bisa membuat transaksi untuk customer langganan tanpa customer melakukan checkout.
- Admin/staff bisa memilih atau membuat customer saat membuat transaksi manual.
- Admin/staff bisa memilih produk, menentukan qty, harga, diskon, ongkir, dan total transaksi.
- Admin/staff bisa mencatat status pembayaran manual.
- Admin/staff bisa mencatat data pengiriman manual, termasuk alamat, kurir, ongkir, resi, dan catatan.
- Sistem bisa membedakan transaksi dari checkout ecommerce dan transaksi manual admin.
- Fitur baru tidak mengganggu checkout, payment gateway, riwayat transaksi, invoice, dan fitur transaksi existing.

## Non-Tujuan

- Tidak mengganti seluruh checkout ecommerce pada fase pertama.
- Tidak membuat customer checkout dari frontend untuk transaksi manual.
- Tidak wajib membuat integrasi RajaOngkir pada fase pertama.
- Tidak wajib membuat payment link otomatis pada fase pertama.
- Tidak membuat integrasi tracking kurir real-time.
- Tidak mengubah status machine transaksi existing kecuali memang diperlukan dan kompatibel.
- Tidak mengubah perhitungan transaksi checkout existing tanpa kebutuhan eksplisit.

## Definisi Transaksi

### Transaksi Checkout Ecommerce

Transaksi checkout ecommerce adalah transaksi yang dibuat oleh customer dari alur frontend.

Ciri-ciri:

- Dibuat dari cart, buy now, atau alur checkout customer.
- Customer memilih alamat dan pengiriman dari frontend.
- Pembayaran dapat melalui manual transfer atau payment gateway.
- Sistem checkout existing menjadi sumber alur utama.
- Source transaksi disimpan sebagai `checkout` atau `ecommerce`.

### Transaksi Manual Admin

Transaksi manual admin adalah transaksi yang dibuat oleh admin/staff dari panel admin berdasarkan pesanan langsung customer.

Ciri-ciri:

- Dibuat dari menu admin.
- Customer tidak perlu melakukan checkout.
- Admin memilih customer dan produk.
- Admin dapat mengatur harga, diskon, ongkir, dan catatan.
- Pembayaran dicatat manual oleh admin.
- Pengiriman dicatat manual oleh admin.
- Source transaksi disimpan sebagai `manual` atau `admin`.

## User Stories

### Admin / Staff

- Sebagai admin, saya bisa membuat transaksi manual dari panel admin.
- Sebagai admin, saya bisa memilih customer lama yang sudah terdaftar.
- Sebagai admin, saya bisa membuat customer baru saat membuat transaksi jika customer belum ada.
- Sebagai admin, saya bisa memilih produk dan qty yang dipesan.
- Sebagai admin, saya bisa melihat subtotal, diskon, ongkir, dan grand total.
- Sebagai admin, saya bisa mengubah harga atau memberi diskon jika diberi permission.
- Sebagai admin, saya bisa mencatat metode pembayaran manual.
- Sebagai admin, saya bisa menandai transaksi sudah dibayar.
- Sebagai admin, saya bisa mencatat alamat pengiriman, kurir, ongkir, nomor resi, dan catatan.
- Sebagai admin, saya bisa melihat apakah transaksi berasal dari checkout ecommerce atau dibuat manual.

### Owner / Supervisor

- Sebagai owner, saya bisa membedakan transaksi checkout dan manual di laporan atau daftar transaksi.
- Sebagai owner, saya bisa memantau transaksi manual yang belum dibayar, belum diproses, atau belum dikirim.
- Sebagai owner, saya bisa membatasi staff tertentu agar tidak bisa mengubah harga atau diskon.

## Scope Functional

### 1. Menu Buat Transaksi Manual

Tambahkan menu admin baru:

```text
Admin -> Transactions -> Create Manual Transaction
```

Atau jika struktur menu saat ini lebih cocok:

```text
Admin -> Transactions -> Buat Transaksi
```

Behavior:

- Menu hanya tersedia untuk role/permission admin atau staff yang berwenang.
- Halaman ini khusus untuk transaksi manual admin.
- Halaman ini tidak mengubah halaman checkout customer.
- Setelah transaksi disimpan, admin diarahkan ke detail transaksi.

### 2. Pemilihan Customer

Admin bisa memilih:

- Customer existing.
- Customer baru.
- Guest/manual customer jika sistem mengizinkan.

Data customer minimal:

- Nama.
- Nomor HP.
- Email opsional.
- Alamat default opsional.

Behavior:

- Jika memilih customer existing, data kontak dan alamat bisa dipakai sebagai default.
- Jika membuat customer baru, validasi mengikuti aturan user/customer existing.
- Jika transaksi guest diperbolehkan, transaksi tetap menyimpan snapshot nama dan kontak customer.

### 3. Pemilihan Produk dan Item

Admin bisa menambahkan banyak item produk.

Data item:

- Produk.
- Qty.
- Harga satuan.
- Diskon item opsional.
- Catatan item opsional.

Behavior:

- Harga default mengikuti harga produk.
- Harga bisa diedit hanya jika admin/staff punya permission.
- Qty tidak boleh 0 atau negatif.
- Sistem menampilkan subtotal per item.
- Sistem menampilkan subtotal transaksi.
- Produk nonaktif atau stok kosong perlu diberi peringatan.

### 4. Diskon dan Total

Transaksi manual mendukung:

- Diskon per item, opsional.
- Diskon total transaksi, opsional.
- Ongkir manual.
- Grand total.

Rumus dasar:

```text
subtotal_produk = total item setelah diskon item
discount_amount = diskon total transaksi
shipping_cost = ongkir manual
grand_total = max(0, subtotal_produk - discount_amount) + shipping_cost
```

Catatan:

- Jika fitur PPN aktif di sistem, transaksi manual harus mengikuti aturan PPN yang sama atau dibuatkan keputusan terpisah sebelum implementasi.
- Nilai final harus dihitung ulang oleh backend, bukan hanya mengikuti frontend.

### 5. Pembayaran Manual

Transaksi manual menggunakan pencatatan pembayaran manual.

Data pembayaran:

- Status pembayaran.
- Metode pembayaran.
- Tanggal pembayaran opsional.
- Nominal dibayar opsional.
- Bukti pembayaran opsional.
- Catatan pembayaran opsional.

Status pembayaran minimal:

```text
unpaid
partial
paid
cancelled
```

Behavior:

- Default status pembayaran adalah `unpaid`.
- Admin bisa menandai transaksi sebagai `paid`.
- Jika pembayaran sebagian dibutuhkan, status `partial` dapat dipakai.
- Payment gateway tidak otomatis dipanggil untuk transaksi manual pada fase pertama.

### 6. Pengiriman Manual

Pengiriman fase pertama dibuat manual agar cocok untuk customer langganan dan order langsung.

Pilihan fulfillment:

- `Belum ditentukan`
- `Dikirim`
- `Ambil sendiri`
- `Kurir toko`
- `Ekspedisi manual`
- `Gratis ongkir`

Data pengiriman:

- Nama penerima.
- Nomor HP penerima.
- Alamat lengkap.
- Provinsi opsional.
- Kota/kabupaten opsional.
- Kecamatan opsional.
- Kode pos opsional.
- Jenis pengiriman.
- Nama kurir/ekspedisi manual.
- Ongkir.
- Nomor resi opsional.
- Catatan pengiriman opsional.

Behavior:

- Ongkir diinput manual oleh admin.
- Resi bisa kosong saat transaksi dibuat dan diisi belakangan.
- Jika `Ambil sendiri`, alamat pengiriman boleh tidak wajib.
- Jika `Dikirim`, alamat pengiriman wajib.
- Admin bisa memperbarui data pengiriman dari detail transaksi.

### 7. Opsi RajaOngkir Fase Lanjutan

RajaOngkir tidak menjadi syarat MVP.

Jika ditambahkan nanti:

- Tambahkan tombol `Cek Ongkir`.
- Admin memilih asal, tujuan, berat, dan ekspedisi.
- Sistem menampilkan pilihan ongkir dari RajaOngkir.
- Admin tetap bisa override ongkir manual.
- Transaksi tetap menyimpan snapshot ongkir, ekspedisi, service, dan estimasi.

Alasan tidak wajib pada fase pertama:

- Banyak customer langganan punya kesepakatan ongkir sendiri.
- Pengiriman bisa memakai kurir toko, travel, ambil sendiri, atau ekspedisi bebas.
- Integrasi ongkir otomatis membutuhkan data alamat dan berat yang lebih rapi.
- Admin tetap butuh fleksibilitas override.

### 8. Pembedaan Source Transaksi

Tambahkan penanda sumber transaksi pada data transaksi.

Contoh field:

```text
source = checkout | manual
created_by_admin_id = nullable admin/staff id
```

Label tampilan:

```text
Checkout Ecommerce
Manual Admin
```

Behavior:

- Tabel admin transaksi menampilkan label source.
- Detail transaksi menampilkan siapa pembuat transaksi manual.
- Filter source tersedia jika memungkinkan:
  - Semua
  - Checkout Ecommerce
  - Manual Admin
- Invoice dan print tetap bisa digunakan oleh kedua jenis transaksi.
- Riwayat customer bisa menampilkan transaksi manual jika transaksi terhubung ke customer.

### 9. Status Transaksi

Status transaksi manual harus kompatibel dengan status transaksi existing.

Rekomendasi status awal:

```text
created atau pending
```

Mapping operasional:

```text
created / pending       = transaksi dibuat, belum dibayar
paid                    = sudah dibayar
process / processing    = sedang diproses
kirim / shipped         = sudah dikirim
completed               = selesai
cancelled               = dibatalkan
```

Behavior:

- Jangan membuat status baru jika status existing sudah cukup.
- Jika perlu status baru, pastikan tidak merusak filter, badge, laporan, dan aksi existing.
- Manual transaction dapat memakai status payment terpisah jika status order existing kurang detail.

### 10. Stok Produk

Perlu aturan stok yang eksplisit.

Rekomendasi MVP:

- Stok dikurangi saat transaksi manual disimpan.
- Jika transaksi dibatalkan, stok dikembalikan.
- Jika qty transaksi diubah, stok disesuaikan.

Alternatif:

- Stok dikurangi saat transaksi ditandai paid.

Catatan:

- Pilihan ini harus dikonfirmasi sebelum implementasi karena berpengaruh ke risiko overselling.
- Untuk order manual dari customer langganan, mengurangi stok saat disimpan biasanya lebih aman karena barang dianggap dipesan.

## UI / UX Requirements

- Form transaksi manual harus cepat dipakai oleh admin/staff.
- Customer, produk, pembayaran, dan pengiriman dipisahkan dalam section yang jelas.
- Admin bisa menambah/menghapus item tanpa reload halaman jika memungkinkan.
- Total transaksi harus selalu terlihat saat admin mengedit item atau ongkir.
- Label source transaksi harus terlihat jelas pada daftar dan detail transaksi.
- Jangan menampilkan istilah yang membingungkan customer seperti "checkout" pada form admin manual.
- Gunakan istilah `Transaksi Manual` atau `Order Manual`.
- Validasi error harus spesifik, misalnya produk kosong, qty tidak valid, alamat wajib, atau ongkir tidak valid.

## Data Requirements

Data transaksi:

- `source`
- `created_by_admin_id`
- `user_id` nullable jika guest/manual customer diperbolehkan
- `manual_customer_name`
- `manual_customer_phone`
- `manual_customer_email`
- `status`
- `payment_status`
- `payment_method`
- `payment_paid_at`
- `payment_amount`
- `payment_proof_path`
- `payment_admin_note`
- `subtotal`
- `discount_amount`
- `shipping_cost`
- `grand_total`

Data item:

- `transaction_id`
- `product_id`
- `product_name` snapshot
- `sku` snapshot jika ada
- `qty`
- `unit_price`
- `discount_amount`
- `subtotal`
- `note`

Data pengiriman:

- `shipping_type`
- `shipping_recipient_name`
- `shipping_phone`
- `shipping_address_line`
- `shipping_province`
- `shipping_city`
- `shipping_district`
- `shipping_postal_code`
- `shipping_courier_name`
- `shipping_service`
- `shipping_cost`
- `tracking_number`
- `shipping_note`

Relasi:

- `user`
- `createdByAdmin`
- `details`

## Non-Interference Requirements

- Checkout customer existing tetap membuat transaksi dengan `source = checkout`.
- Alur Midtrans existing tidak dipanggil untuk transaksi manual kecuali nanti dibuat fitur khusus.
- Manual transaction tidak boleh mengubah cart customer.
- Manual transaction tidak boleh menghapus cart customer.
- Manual transaction tidak boleh memakai session checkout customer.
- Perhitungan checkout existing tidak boleh berubah karena form manual.
- Filter dan laporan existing harus tetap menampilkan transaksi lama.
- Jika field `source` belum ada pada transaksi lama, tampilkan sebagai `Checkout Ecommerce` atau `Unknown` sesuai kebutuhan migrasi.
- Route, controller, dan view manual transaction harus dipisahkan dari checkout customer.

## Acceptance Criteria

- Admin/staff dengan permission bisa membuka halaman buat transaksi manual.
- Admin/staff bisa membuat transaksi manual untuk customer existing.
- Admin/staff bisa membuat transaksi manual dengan data customer baru atau manual jika diizinkan.
- Admin/staff bisa menambahkan lebih dari satu produk.
- Sistem menghitung subtotal, diskon, ongkir, dan grand total dengan benar.
- Transaksi manual tersimpan dengan source `manual`.
- Transaksi checkout existing tersimpan atau tampil dengan source `checkout`.
- Daftar transaksi admin bisa membedakan `Checkout Ecommerce` dan `Manual Admin`.
- Detail transaksi manual menampilkan admin/staff pembuat transaksi.
- Pembayaran manual bisa dicatat dan statusnya bisa diperbarui.
- Data pengiriman manual bisa disimpan dan diperbarui.
- Nomor resi bisa diisi setelah transaksi dibuat.
- Checkout customer existing tetap berjalan seperti sebelum fitur ini dibuat.
- Cart customer tidak berubah saat admin membuat transaksi manual.
- Invoice/print transaksi tetap bisa digunakan untuk transaksi manual.

## Edge Cases

- Customer existing tidak punya alamat.
- Customer pesan lewat admin tetapi belum punya akun.
- Produk kehabisan stok saat admin menyimpan transaksi.
- Admin mengubah qty setelah stok sudah berkurang.
- Transaksi dibatalkan setelah stok sudah dikurangi.
- Ongkir belum diketahui saat transaksi dibuat.
- Customer ambil sendiri sehingga alamat tidak wajib.
- Pembayaran sebagian.
- Bukti pembayaran tidak diupload tetapi transaksi ditandai paid.
- Transaksi manual dibuat oleh staff yang kemudian akunnya dinonaktifkan.
- Transaksi lama belum punya nilai `source`.
- Diskon lebih besar dari subtotal produk.

## Implementation Steps

### Phase 1: Data Model dan Source Transaksi

1. Audit struktur `transactions` dan `transaction_details` existing.
2. Tambahkan field source transaksi jika belum tersedia.
3. Tambahkan field pembuat transaksi manual jika belum tersedia.
4. Pastikan transaksi existing memiliki default source yang aman.
5. Tambahkan label source pada daftar dan detail transaksi admin.

### Phase 2: Form Buat Transaksi Manual

1. Tambahkan route admin untuk create/store transaksi manual.
2. Buat halaman form transaksi manual.
3. Tambahkan pemilihan customer existing.
4. Tambahkan opsi customer baru/manual jika disetujui.
5. Tambahkan pemilihan produk dan qty.
6. Tambahkan perhitungan subtotal, diskon, ongkir, dan grand total.

### Phase 3: Simpan Transaksi Manual

1. Validasi customer, item, qty, harga, diskon, dan ongkir.
2. Hitung ulang total di backend.
3. Simpan transaksi dengan source `manual`.
4. Simpan item sebagai snapshot produk.
5. Terapkan aturan stok yang sudah disepakati.
6. Redirect ke detail transaksi.

### Phase 4: Pembayaran Manual

1. Tambahkan field dan UI status pembayaran manual.
2. Tambahkan aksi update status pembayaran.
3. Tambahkan upload bukti pembayaran jika diperlukan.
4. Tambahkan catatan pembayaran admin.
5. Pastikan status pembayaran manual tidak mengganggu payment gateway checkout.

### Phase 5: Pengiriman Manual

1. Tambahkan input jenis pengiriman.
2. Tambahkan input alamat dan penerima.
3. Tambahkan input kurir/ekspedisi manual.
4. Tambahkan input ongkir manual.
5. Tambahkan input nomor resi yang bisa diisi belakangan.
6. Tambahkan catatan pengiriman.

### Phase 6: Testing

1. Test checkout ecommerce existing tetap berhasil.
2. Test manual transaction untuk customer existing.
3. Test manual transaction untuk customer baru/manual jika fitur diaktifkan.
4. Test tambah beberapa produk.
5. Test validasi qty 0 atau stok kosong.
6. Test perhitungan diskon dan ongkir.
7. Test update status pembayaran.
8. Test update data pengiriman dan resi.
9. Test pembatalan transaksi dan efek stok.
10. Test daftar transaksi bisa filter atau membedakan source.

buka dan lihat file docs/prd-manual-admin-sales-order.md.
lalu kerjakan
### Phase 1: Data Model dan Source Transaksi
buka dan lihat file docs/prd-manual-admin-sales-order.md.
lalu kerjakan
### Phase 2: Form Buat Transaksi Manual
buka dan lihat file docs/prd-manual-admin-sales-order.md.
lalu kerjakan
### Phase 3: Simpan Transaksi Manual
buka dan lihat file docs/prd-manual-admin-sales-order.md.
lalu kerjakan
### Phase 4: Pembayaran Manual
buka dan lihat file docs/prd-manual-admin-sales-order.md.
lalu kerjakan
### Phase 5: Pengiriman Manual
buka dan lihat file docs/prd-manual-admin-sales-order.md.
lalu kerjakan
### Phase 6: Testing
## Open Questions

- Apakah transaksi manual boleh dibuat untuk customer tanpa akun?
- Apakah staff boleh mengubah harga produk, atau hanya admin tertentu?
- Apakah diskon manual perlu permission khusus?
- Apakah stok dikurangi saat transaksi dibuat atau saat transaksi dibayar?
- Apakah PPN pada transaksi manual harus mengikuti aturan checkout ecommerce?
- Apakah transaksi manual perlu tampil di riwayat pesanan customer?
- Apakah invoice transaksi manual perlu format berbeda dari invoice checkout?
- Apakah pembayaran sebagian diperlukan pada fase pertama?
- Apakah RajaOngkir perlu masuk fase awal atau cukup disiapkan sebagai opsi lanjutan?
