# PRD: Guest Checkout (Checkout Tanpa Login)

## Ringkasan

Saat ini seluruh alur checkout (cart, buy now, redeem point, pembayaran manual, Midtrans) terkunci di belakang middleware `auth` (`routes/web.php:306-343`), sehingga customer wajib login/daftar akun sebelum bisa belanja. Fitur ini membuka jalur checkout tanpa login khusus untuk pembelian satu produk langsung ("Beli Langsung"), tanpa merombak sistem cart multi-item dan alamat tersimpan yang sekarang terikat penuh ke akun.

Keputusan utama (sudah dikonfirmasi klien):

- Guest checkout hanya lewat alur **Beli Langsung** (`CartController::buyNow`, satu produk per transaksi). Cart multi-item (`/cart`, tabel `carts`) tetap wajib login — tidak ada migrasi skema cart di fase ini.
- Data yang diminta dari guest: nama, email, no. HP, alamat pengiriman. Data ini hanya dipakai untuk transaksi tersebut, **tidak disimpan sebagai alamat tersimpan/reusable**.
- **Transfer Manual dan Midtrans dua-duanya tersedia untuk guest** (revisi dari keputusan awal yang sempat membatasi ke Midtrans saja). Alasan: platform ini multi-perusahaan (multi-PT), dan Midtrans bersifat opt-in per perusahaan (lihat `docs/prd-multi-company-foundation.md:139-143`) — PT yang belum selesai approval Midtrans-nya hanya bisa terima pembayaran lewat Transfer Manual, jadi Transfer Manual wajib tetap ada di guest checkout juga, bukan sekadar pilihan.
- **Upload bukti Transfer Manual tanpa login** memakai dua jalur:
  1. **Jalur utama**: langsung di halaman "Menunggu Pembayaran" pada sesi/tab browser yang sama persis setelah checkout — tanpa link, tanpa email, tanpa login. Kepemilikan transaksi diverifikasi lewat session, bukan lewat akun.
  2. **Jalur cadangan** (guest tutup tab lalu balik lagi nanti/besok): tombol "Lihat Status Pesanan" di email konfirmasi pesanan (pola umum seperti toko online lain, bukan magic-link) membawa ke halaman Lacak Pesanan dengan nomor order **sudah terisi otomatis**; guest tinggal konfirmasi email untuk verifikasi, baru bisa upload bukti dari situ.
- Metode pembayaran yang terikat akun (saldo/poin loyalti) otomatis disembunyikan untuk guest.
- **Kalau email yang dipakai guest saat checkout ternyata sudah terdaftar sebagai akun, checkout guest diblokir dan diarahkan untuk login pakai email itu** — supaya histori pesanan tidak "nyasar" terpisah dari akun lama.
- Semua produk di toko boleh dibeli lewat guest checkout — tidak ada pengecualian kategori.
- Setelah pembayaran berhasil, guest ditawari opsi buat akun otomatis (tinggal isi password, data lain dipakai ulang dari form guest).
- Guest bisa melacak status pesanan lewat halaman baru "Lacak Pesanan" (input email + nomor order), tanpa login. Dibatasi **maksimal 3x percobaan per 15 menit**, dengan pesan error yang jelas saat gagal/terkunci, dan kode order harus cukup kompleks (bukan angka pendek yang gampang ditebak/diketik) sehingga praktiknya hanya bisa didapat lewat copy-paste dari email/halaman sukses checkout.
- Notifikasi pesanan untuk guest **cukup lewat email dulu di Phase 1**. WhatsApp menyusul di Phase 2 (perlu integrasi WA Business API terpisah).
- Fitur retur/komplain tetap 100% wajib login — guest yang mau retur diarahkan untuk daftar akun dulu. Tidak ada perubahan di `ReturnRequestController`.
- Fitur member existing (histori pesanan, poin, diskon member) tidak berubah untuk user yang login.

## Tujuan

- Customer bisa checkout dan membayar 1 produk tanpa membuat akun.
- Guest checkout tetap menghasilkan data transaksi yang lengkap dan valid untuk fulfillment (pengiriman, invoice, notifikasi).
- Guest bisa mengecek status pesanannya sendiri tanpa login.
- Guest punya jalur mudah untuk mengubah pesanannya jadi akun permanen setelah checkout.
- Checkout, cart, dan fitur akun existing untuk user login tidak berubah perilakunya.

## Non-Tujuan

- Tidak membuat cart multi-item tersedia untuk guest pada fase ini.
- Tidak menyimpan alamat guest sebagai data reusable/tabel `addresses`.
- Tidak membuka fitur retur untuk guest tanpa akun.
- Tidak membuka redeem poin loyalti untuk guest (butuh akun by design, lihat `app/Services/LoyaltyPointService.php:23,94,149,183`).
- Tidak mengubah middleware `auth` pada `/cart`, `/checkout/orders`, `/profil`, `/redeem-point/checkout`, notifikasi user, dan wishlist.
- Tidak mengubah struktur tabel `carts` atau `addresses`.
- Tidak membuat sistem token/magic-link baru untuk upload bukti Transfer Manual — jalur cadangan reuse verifikasi email+order yang sama dengan Lacak Pesanan, bukan token rahasia terpisah.
- Tidak membangun logic baru untuk visibilitas Midtrans per-perusahaan (`company_payment_credentials`) di fitur ini — saat ini `MidtransController` masih pakai satu `MIDTRANS_SERVER_KEY` global untuk semua perusahaan (lihat Kondisi Saat Ini), jadi guest checkout otomatis ikut logic yang sama dengan checkout user login. Kalau logic per-perusahaan itu nanti dibangun terpisah, guest checkout otomatis ikut karena pakai controller yang sama.
- Tidak mengirim notifikasi WhatsApp/SMS ke guest pada Phase 1 (menyusul Phase 2, butuh integrasi WA Business API).
- Tidak mengizinkan guest checkout lanjut dengan email yang sudah terdaftar sebagai akun — kasus ini diarahkan ke login, bukan dibiarkan lanjut sebagai transaksi terpisah.

## Kondisi Saat Ini (temuan teknis)

- Semua route checkout ada dalam satu grup `Route::middleware('auth')` (`routes/web.php:306-343`), termasuk `GET /checkout` (`FrontendController::checkout`, `routes/web.php:308`), `POST /checkout/buy-now` (`CartController::buyNow`, `routes/web.php:318`), dan `POST /checkout/complete` (`CartController::completeCheckout`, `routes/web.php:319`).
- `FrontendController::checkout()` (`app/Http/Controllers/FrontendController.php:749-791`) punya guard tambahan `abort_unless(auth()->check(), 403)` di baris 751, dan langsung memanggil `auth()->user()->addresses()` (baris 754) serta `auth()->user()->taxProfiles()` (baris 756) — keduanya akan fatal error kalau dipanggil tanpa user.
- `CartController::buyNow()` (`app/Http/Controllers/CartController.php:157-205`) sudah menyimpan item ke session (`session(['checkout' => [...]])`), bukan ke tabel `carts` — ini jalur yang paling siap dibuka untuk guest karena tidak menyentuh data cart per-user.
- `CartController::completeCheckout()` (`app/Http/Controllers/CartController.php:232-272`) memanggil `auth()->id()` di beberapa tempat (baris 256, 270, 276) — perlu null-safe untuk guest, tapi logic pembersihan cart hanya relevan untuk `source === 'cart_selected'`, yang tidak dipakai guest.
- Tabel `transactions.user_id` sudah `nullable()->nullOnDelete()` (`database/migrations/2026_05_03_230000_create_transactions_table.php:13`) — tidak perlu migrasi tambahan untuk mendukung transaksi tanpa user.
- `ManualPaymentController::checkout()` (`app/Http/Controllers/ManualPaymentController.php:25-211`) sudah null-safe untuk `user_id` di pembuatan transaksi (baris 103, 159) dan pengiriman email (baris 185), **tapi** baris 177, 180, dan 198 memanggil `$request->user()->id` tanpa null-safe operator — akan fatal error untuk guest, harus diperbaiki di Phase 1 sekarang (bukan lagi Phase 2). Validasi request (baris 27-53) juga belum punya field nama/email/no. HP guest.
- `ManualPaymentController::uploadProof()` (`app/Http/Controllers/ManualPaymentController.php:213-215`) dan route-nya (`routes/web.php:347`) mewajibkan login + `abort_unless($transaction->user_id === $request->user()->id)`. Untuk guest, ownership check ini perlu diganti/ditambah jalur baru: kalau `$transaction->user_id` null, verifikasi lewat session checkout (jalur utama, langsung setelah checkout) atau lewat flag "sudah verifikasi email di Lacak Pesanan" yang disimpan di session (jalur cadangan).
- `MidtransController` (`app/Http/Controllers/MidtransController.php`) punya banyak pemanggilan `$request->user()?->id` yang sudah null-safe, tapi perlu audit menyeluruh untuk baris yang belum null-safe (mis. sekitar baris 497, 526, 533, 842, 921, 930) sebelum dibuka ke guest.
- Semua pemanggilan `env('MIDTRANS_SERVER_KEY', ...)` di `MidtransController.php` (baris 64, 423, 586, 783) memakai **satu kredensial Midtrans global untuk seluruh perusahaan** — belum menggunakan tabel `company_payment_credentials`/model `CompanyPaymentCredential` yang skemanya sudah ada (`database/migrations/2026_07_19_000400_create_company_payment_credentials_table.php`, `app/Models/CompanyPaymentCredential.php`) tapi belum diintegrasikan ke controller manapun. Visibilitas Midtrans per-perusahaan yang dijelaskan di `docs/prd-multi-company-foundation.md:139-143` **belum diimplementasikan** — jadi baik checkout guest maupun user login saat ini sama-sama pakai satu akun Midtrans bersama.
- Order ID untuk transaksi Midtrans dibuat dengan pola `'ORD-'.now()->format('YmdHis').'-'.random_int(1000, 9999)` (`app/Http/Controllers/MidtransController.php:136`) — hanya 9.000 kombinasi acak dikombinasikan dengan waktu. Untuk mendukung permintaan klien soal kode order yang "tidak gampang ditebak, cukup rumit sampai cuma bisa kopas", entropi random suffix ini perlu ditingkatkan (lihat Scope Functional §6).
- `InvoiceController::show` (`routes/web.php:346`) juga wajib login — guest butuh jalur alternatif untuk lihat invoice-nya (lewat halaman Lacak Pesanan).

## User Stories

### Guest (belum login)

- Sebagai pengunjung, saya bisa klik "Beli Sekarang" pada produk dan langsung checkout tanpa disuruh login.
- Sebagai guest, saya mengisi nama, email, no. HP, dan alamat pengiriman saat checkout.
- Sebagai guest, saya hanya melihat metode pembayaran yang memang bisa saya pakai (tidak ada opsi saldo/poin member), tapi tetap bisa pilih Transfer Manual atau Midtrans.
- Sebagai guest yang bayar Transfer Manual, saya bisa langsung upload bukti transfer di halaman yang sama setelah checkout, tanpa perlu login atau buka email.
- Sebagai guest, setelah bayar berhasil saya menerima invoice/notifikasi ke email saya, termasuk tombol untuk cek status pesanan kalau saya belum sempat upload bukti transfer.
- Sebagai guest, saya bisa cek status pesanan saya kapan saja dengan memasukkan email + nomor order, tanpa login.
- Sebagai guest, saya ditawari untuk membuat akun setelah checkout supaya pesanan ini dan pesanan berikutnya tersimpan di riwayat saya.
- Sebagai guest yang mau retur barang, saya diarahkan untuk membuat akun dulu.

### User Login (tidak berubah)

- Sebagai user login, cart multi-item, alamat tersimpan, poin loyalti, dan riwayat pesanan saya tetap berjalan seperti sekarang.

## Scope Functional

### 1. Buka akses "Beli Langsung" untuk guest

- Pindahkan `POST /checkout/buy-now` dan `GET /checkout` keluar dari grup `auth`, atau buat grup route baru yang mengizinkan guest **khusus untuk source `buy_now`**.
- `POST /checkout/complete`, `POST /checkout/manual-payment`, `POST /checkout/midtrans/charge`, `GET /checkout/waiting/{orderId}`, `GET /checkout/midtrans/status/{orderId}` juga perlu dibuka untuk guest karena merupakan bagian dari alur yang sama.
- `/cart/*`, `/checkout/orders`, `/redeem-point/checkout`, `/rajaongkir/*` (opsional, dicek terpisah), `/wishlist/*`, `/notifications/*`, `/profil` **tetap** wajib login — tidak berubah.
- `FrontendController::checkout()` harus bercabang:
  - Jika `auth()->check()` false dan `session('checkout.source') !== 'buy_now'` → redirect ke halaman login (perilaku existing).
  - Jika `auth()->check()` false dan source `buy_now` → tampilkan halaman checkout versi guest (tanpa memanggil `addresses()`/`taxProfiles()`).

### 2. Form data guest

Field wajib saat checkout sebagai guest:

- Nama penerima
- Email
- No. HP
- Alamat pengiriman lengkap (alamat, kota, provinsi, kode pos)

Behavior:

- Tidak ada pemilihan dari alamat tersimpan (guest tidak punya).
- Data ini hanya dipakai untuk snapshot transaksi (`shipping_recipient_name`, `shipping_phone`, `shipping_address_line`, dst — kolom sudah ada di tabel `transactions`), tidak dibuat entri baru di tabel `addresses`.
- `MidtransController::createCharge()` perlu tambahan validasi field guest (`guest_name`, `guest_email`, `guest_phone`) dan menyimpannya ke kolom `manual_customer_name/phone/email` yang sudah ada (`database/migrations/2026_06_06_000200_add_manual_customer_columns_to_transactions_table.php`).
- **Sebelum melanjutkan ke pembayaran**, sistem mengecek apakah `guest_email` sudah terdaftar di tabel `users`. Jika sudah, tampilkan pesan "Email ini sudah terdaftar. Silakan login untuk melanjutkan." dan arahkan ke halaman login (state `session('checkout')` tetap dipertahankan supaya tidak hilang setelah login).

### 3. Metode pembayaran untuk guest

- Metode yang butuh akun (saldo, redeem poin) disembunyikan dari daftar pilihan pembayaran saat `auth()->check()` false.
- **Transfer Manual dan Midtrans dua-duanya ditampilkan** ke guest, sama seperti user login — tidak ada pembatasan tambahan berdasarkan status login.
- Kalau logic visibilitas Midtrans per-perusahaan (`company_payment_credentials`) dibangun di masa depan, guest checkout otomatis ikut aturan yang sama karena memakai controller yang sama dengan user login (lihat Non-Tujuan).

### 4. Upload bukti Transfer Manual tanpa login

**Jalur utama — upload langsung setelah checkout (sesi yang sama):**

- Setelah `ManualPaymentController::checkout()` berhasil membuat transaksi, simpan `order_id` transaksi itu ke session guest (mis. `session(['guest_owned_orders' => [...]])`, menampung beberapa order_id agar guest bisa checkout lebih dari sekali dalam satu sesi browser).
- Modifikasi guard di `ManualPaymentController::uploadProof()` (baris 215): kalau `$transaction->user_id` tidak null, tetap pakai cek kepemilikan lama (`$transaction->user_id === $request->user()->id`); kalau `$transaction->user_id` null (guest), izinkan akses jika `$transaction->order_id` ada di `session('guest_owned_orders', [])` **atau** guest sudah lolos verifikasi lewat jalur cadangan (lihat di bawah).
- Halaman "Menunggu Pembayaran" (`checkout.waiting`) menampilkan instruksi transfer + form upload langsung, tanpa elemen UI yang mengharuskan login.

**Jalur cadangan — guest kembali lagi nanti/besok (sesi lama sudah hilang):**

- Email konfirmasi pesanan (`InvoiceOrder` mail atau varian barunya untuk guest) berisi tombol "Lihat Status Pesanan" yang mengarah ke `GET /lacak-pesanan?order_id={order_id}` — nomor order sudah terisi otomatis di form, guest tinggal isi email untuk konfirmasi.
- Setelah email cocok dengan `order_id` di halaman Lacak Pesanan, set flag di session (mis. `session(['verified_orders' => [...]])`) yang juga diterima oleh guard di `uploadProof()` di atas.
- Tombol ini bukan magic-link — tidak membawa token rahasia di URL, dan tetap butuh konfirmasi email di halaman tujuan, jadi tidak membuka celah kalau link ter-forward ke orang lain tanpa tahu email pemesan.

### 5. Audit null-safety pembayaran

- Perbaiki `ManualPaymentController.php:177,180,198` — ganti `$request->user()->id` jadi `$request->user()?->id`, dan sesuaikan logic yang bergantung padanya (mis. `UserNotification::create` di baris 197-203 perlu dilewati atau diberi varian guest kalau `user_id` null, karena kemungkinan tabel `user_notifications` juga mewajibkan `user_id`).
- Audit penuh `MidtransController.php` (termasuk baris ~497, 526, 533, 842, 921, 930) untuk memastikan tidak ada pemanggilan `$request->user()->id`/`$request->user()->email` tanpa null-safe operator.
- Kirim email invoice/notifikasi ke `guest_email` di kedua controller kalau `$request->user()` null.

### 6. Lacak Pesanan tanpa login

- Halaman baru, misal `GET /lacak-pesanan`, form input: email + nomor order (nomor order bisa terisi otomatis lewat query string dari tombol email, lihat §4).
- Query `Transaction::where('order_id', $orderId)->where(function($q) use ($email) { $q->where('manual_customer_email', $email)->orWhereHas('user', fn($q) => $q->where('email', $email)); })`.
- Tampilkan status pesanan, ringkasan item, dan status pembayaran — reuse tampilan yang sudah ada di `frontend.checkout-orders` bila memungkinkan. Kalau transaksi berstatus `menunggu_verifikasi` dan metodenya Transfer Manual, tampilkan juga form upload bukti (lihat §4).
- **Rate limiting: maksimal 3 percobaan per 15 menit** (per kombinasi IP + email, atau per session — ditentukan saat implementasi). Setelah limit tercapai, tampilkan pesan jelas "Terlalu banyak percobaan, coba lagi dalam X menit", jangan diam-diam gagal.
- Pesan error saat email/nomor order tidak cocok harus general (tidak membocorkan apakah email terdaftar atau order ID ada).
- **Tingkatkan entropi order ID**, dari `'ORD-'.now()->format('YmdHis').'-'.random_int(1000, 9999)` (`MidtransController.php:136`) dan pola serupa `'MAN-'...` di `ManualPaymentController.php:55`, menjadi kombinasi dengan random string alfanumerik yang lebih panjang (mis. 8+ karakter acak, bukan cuma 4 digit), supaya kode order praktis tidak bisa ditebak manual dan hanya realistis didapat lewat copy-paste dari email/halaman sukses.

### 7. Buat akun setelah checkout

- Setelah transaksi guest berhasil dibuat (redirect ke halaman waiting/sukses), tampilkan ajakan "Buat akun dari data ini?".
- Form hanya minta password (nama/email/no. HP sudah ada dari data guest checkout).
- Setelah akun dibuat, hubungkan transaksi guest tersebut ke `user_id` akun baru (update `transactions.user_id` untuk `order_id` terkait, dicocokkan lewat email).

### 8. Retur tetap wajib login

- Tidak ada perubahan di `ReturnRequestController`.
- Di halaman Lacak Pesanan, kalau guest mencoba ajukan retur, arahkan ke pesan "Buat akun dulu untuk mengajukan retur" + tombol buat akun (reuse alur poin 7).

## UI/UX Requirements

- Tombol "Beli Sekarang" pada halaman produk tidak lagi mengarahkan ke login untuk pengunjung tanpa akun.
- Halaman checkout guest menampilkan section terpisah "Data Pengiriman" (form manual) tanpa opsi "pilih dari alamat tersimpan".
- Tampilkan banner kecil di checkout guest: "Sudah punya akun? Login untuk memakai alamat tersimpan & poin member" agar user existing tidak salah checkout sebagai guest.
- Setelah checkout sukses, CTA "Buat akun dalam 1 langkah" ditampilkan jelas, dengan opsi "Lewati".
- Halaman Lacak Pesanan mudah ditemukan dari footer/navbar meski belum login.

## Data Requirements

Kolom yang **sudah ada** dan dipakai ulang (tidak perlu migrasi baru):

- `transactions.user_id` (nullable)
- `transactions.manual_customer_name`
- `transactions.manual_customer_phone`
- `transactions.manual_customer_email`
- `transactions.shipping_recipient_name/phone/address_line/city/province/postal_code`

Perlu dicek/ditambahkan saat implementasi:

- Field untuk menandai transaksi berasal dari guest (mis. cek `user_id === null` sudah cukup, atau butuh flag eksplisit `is_guest`/`source` — selaraskan dengan konvensi `source` yang sudah dipakai di `docs/prd-manual-admin-sales-order.md`).
- Kolom baru `is_member_only` (boolean) di tabel `coupons`, supaya admin bisa menandai voucher tertentu sebagai khusus member. Guest checkout memfilter kupon yang `is_member_only = true` dari daftar kupon yang bisa dipakai. Klien belum menentukan voucher spesifik mana yang akan ditandai — ini keputusan operasional yang dilakukan admin lewat panel, bukan blocker development.

## Non-Interference Requirements

- Cart multi-item (`/cart`, tabel `carts`) dan seluruh `CartController` selain `buyNow`/`completeCheckout` tidak berubah perilakunya untuk user login.
- Alamat tersimpan (`Address` model, tabel `addresses`) tidak disentuh oleh alur guest.
- Poin loyalti (`LoyaltyPointService`) tidak dipanggil untuk transaksi guest (`user_id` null sudah otomatis di-skip, lihat `app/Services/LoyaltyPointService.php:23,94,149,183`).
- Checkout, retur, dan riwayat pesanan untuk user login tidak berubah.
- Transaksi guest checkout tetap tercatat dengan `source = checkout` seperti transaksi checkout user login (selaras dengan `docs/prd-manual-admin-sales-order.md`), bukan `source = manual`.

## Acceptance Criteria

- Pengunjung tanpa login bisa klik "Beli Sekarang" dari halaman produk dan sampai ke halaman checkout tanpa diarahkan ke login.
- Guest bisa mengisi data pengiriman manual dan menyelesaikan pembayaran lewat Transfer Manual maupun Midtrans (kartu/e-wallet/VA).
- Guest bisa upload bukti Transfer Manual langsung di halaman "Menunggu Pembayaran" pada sesi yang sama, tanpa login.
- Guest yang kembali lagi nanti (sesi lama hilang) bisa upload bukti transfer lewat tombol "Lihat Status Pesanan" di email → Lacak Pesanan.
- Guest tidak melihat metode pembayaran berbasis saldo/poin.
- Guest yang memasukkan email yang sudah terdaftar sebagai akun diblokir dari checkout guest dan diarahkan ke login, dengan data checkout tetap tersimpan.
- Transaksi guest tersimpan dengan `user_id` null dan data kontak lengkap di kolom `manual_customer_*`.
- Guest menerima invoice/notifikasi lewat email setelah checkout berhasil, termasuk tombol "Lihat Status Pesanan".
- Guest bisa membuka halaman Lacak Pesanan, memasukkan email + nomor order, dan melihat status pesanannya, dengan batas 3 percobaan per 15 menit dan pesan error yang jelas saat limit tercapai.
- Guest bisa membuat akun dari CTA setelah checkout, dan transaksi sebelumnya otomatis muncul di riwayat akun barunya.
- Guest yang mencoba akses fitur retur diarahkan untuk membuat akun, tidak error/crash.
- Cart multi-item, alamat tersimpan, poin loyalti, dan checkout user login tidak berubah perilakunya dari sebelum fitur ini dibuat.

## Edge Cases

- Guest checkout dengan email yang ternyata sudah terdaftar sebagai akun — diblokir dan diarahkan login (lihat Scope Functional §2).
- Guest mengisi alamat tidak lengkap/format nomor HP tidak valid.
- Guest menutup browser sebelum bayar — transaksi berstatus pending, apakah expired otomatis (kolom `expires_at` sudah ada di `transactions`).
- Guest checkout produk yang stoknya habis di antara klik "Beli Sekarang" dan submit checkout.
- Guest mencoba akses `GET /checkout` langsung tanpa melalui `buyNow` (session `checkout` kosong).
- Guest memasukkan email/no. order yang salah di halaman Lacak Pesanan, atau sudah melebihi batas 3x percobaan — pesan error harus general (tidak membocorkan apakah email terdaftar atau tidak) dan jelas soal status lockout.
- Guest checkout lalu buat akun, tapi email yang dipakai ternyata sudah dipakai user lain saat proses buat akun berjalan.
- Kupon/voucher yang ditandai `is_member_only` dicoba dipakai guest — harus ditolak di validasi backend, bukan cuma disembunyikan di UI.
- Guest upload bukti transfer di sesi browser yang beda dari saat checkout (mis. ganti device) tanpa lewat Lacak Pesanan dulu — harus ditolak sampai lolos verifikasi email di jalur cadangan.
- Guest mencoba akses `uploadProof()` untuk `order_id` milik orang lain dengan menebak-nebak — harus ditolak karena tidak ada di `session('guest_owned_orders')`/`session('verified_orders')` miliknya.
- Guest checkout dua kali dalam satu sesi browser (dua produk berbeda, dua kali "Beli Langsung") — kedua `order_id` harus tetap bisa diakses lewat jalur utama, bukan cuma yang terakhir.

## Implementation Steps

### Phase 1: Buka Route & Guard

1. Pisahkan route `GET /checkout`, `POST /checkout/buy-now`, `POST /checkout/complete`, `POST /checkout/manual-payment`, `POST /checkout/midtrans/charge`, `GET/POST /checkout/waiting|status|cancel` dari grup `auth`.
2. Tambahkan guard di `FrontendController::checkout()` dan controller terkait: guest hanya boleh lanjut jika `session('checkout.source') === 'buy_now'`.
3. Pastikan `/cart`, `/checkout/orders`, `/profil`, `/redeem-point/checkout`, `/wishlist/*`, `/notifications/*` tetap di grup `auth`.

### Phase 2: Form & Validasi Guest

1. Tambah field guest (`guest_name`, `guest_email`, `guest_phone`, alamat manual) di halaman checkout untuk pengunjung tanpa login.
2. Tambah validasi field guest di `ManualPaymentController::checkout()` dan `MidtransController::createCharge()`.
3. Tambah pengecekan email guest terhadap tabel `users`; kalau sudah terdaftar, blokir dan arahkan ke login (state checkout dipertahankan).
4. Simpan data guest ke `manual_customer_*` dan kolom `shipping_*` di `transactions`.
5. Tambah kolom `is_member_only` di tabel `coupons` (migrasi baru) dan filter kupon ini dari guest checkout, di frontend maupun validasi backend.

### Phase 3: Null-Safety & Upload Bukti Transfer Manual untuk Guest

1. Perbaiki `ManualPaymentController.php:177,180,198` agar null-safe.
2. Audit penuh `MidtransController.php` untuk pola serupa.
3. Simpan `order_id` transaksi guest ke `session('guest_owned_orders')` setelah checkout berhasil (baik Transfer Manual maupun Midtrans).
4. Modifikasi guard `uploadProof()` (`ManualPaymentController.php:215`) untuk menerima guest yang order_id-nya ada di session.
5. Tambah tombol upload bukti langsung di halaman `checkout.waiting` untuk transaksi guest.
6. Tambah tombol "Lihat Status Pesanan" di email konfirmasi pesanan guest, mengarah ke `GET /lacak-pesanan?order_id=...`.
7. Tingkatkan entropi `order_id` di `MidtransController.php:136` dan `ManualPaymentController.php:55`.
8. Sembunyikan metode pembayaran berbasis saldo/poin dari guest.
9. Kirim email invoice/notifikasi ke `guest_email` kalau `$request->user()` null.

### Phase 4: Lacak Pesanan

1. Buat route + controller + view `GET /lacak-pesanan`, mendukung `order_id` terisi otomatis lewat query string.
2. Setelah verifikasi email berhasil, simpan flag ke `session('verified_orders')` supaya guard `uploadProof()` di Phase 3 langkah 4 juga menerima jalur ini.
3. Tambahkan rate limiting: maksimal 3 percobaan per 15 menit, dengan pesan lockout yang jelas.
4. Reuse tampilan status pesanan dari `frontend.checkout-orders`; tampilkan form upload bukti kalau transaksi masih `menunggu_verifikasi` dan metodenya Transfer Manual.

### Phase 5: Buat Akun dari Guest Checkout

1. Tambah CTA & form buat akun di halaman sukses checkout.
2. Tambah logic hubungkan `transactions.user_id` ke akun baru berdasarkan email yang cocok.

### Phase 6: Testing

1. Test checkout user login existing tetap berjalan normal (cart, redeem point, alamat tersimpan, Transfer Manual, Midtrans).
2. Test guest checkout end-to-end via Transfer Manual: Beli Langsung → isi data guest → upload bukti di halaman yang sama → status berubah jadi menunggu verifikasi admin.
3. Test guest checkout end-to-end via Midtrans → terima invoice email.
4. Test guest tidak melihat metode pembayaran saldo/poin, baik di UI maupun lewat request langsung ke backend.
5. Test guest checkout dengan email yang sudah terdaftar → diblokir dan diarahkan login, data checkout tidak hilang.
6. Test upload bukti transfer guest di sesi baru (tanpa lewat Lacak Pesanan) → ditolak.
7. Test upload bukti transfer guest lewat tombol email → Lacak Pesanan → verifikasi email → berhasil upload.
8. Test Lacak Pesanan dengan data benar, data salah, dan setelah melewati batas 3x percobaan.
9. Test buat akun dari guest checkout, transaksi lama muncul di riwayat akun baru.
10. Test guest mencoba akses cart/retur/profil tetap diarahkan ke login.
11. Test stok habis saat guest submit checkout.
12. Test transaksi guest expired sesuai `expires_at`.
13. Test kupon `is_member_only` ditolak untuk guest.

## Open Questions

- **Voucher yang ditandai `is_member_only`** — kolomnya sudah direncanakan (lihat Data Requirements), tapi klien belum menentukan voucher spesifik mana yang perlu ditandai. Ini bisa diisi belakangan oleh admin lewat panel, tidak menghambat development.
- **Definisi "IP + email" vs "session" untuk rate limiting Lacak Pesanan** — perlu diputuskan saat implementasi apakah pembatasan 3x/15 menit dihitung per IP, per session, atau kombinasi keduanya (per IP lebih aman dari percobaan berulang pakai banyak tab/incognito, tapi berisiko mengunci pengguna di jaringan kantor/WiFi bersama yang sama).
- **Berapa lama `session('guest_owned_orders')` bertahan** — mengikuti default session lifetime Laravel (`config/session.php`), atau perlu durasi khusus yang lebih panjang supaya guest yang habis checkout tidak "kehilangan akses" upload bukti kalau baru transfer beberapa jam kemudian?
