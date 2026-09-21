# PRD: Spesifikasi Produk Dinamis per Kategori

Status: Implemented core v1; rollout mapping kategori menunggu konfigurasi admin  
Tanggal: 21 September 2026  
Area: Katalog produk, varian, admin produk, storefront, import Excel, dan Open Catalog API  
Pendekatan delivery: migrasi backward-compatible dengan quality gate pada setiap fase

## Status Implementasi (21 September 2026)

Core v1 sudah diimplementasikan dan diverifikasi:

- Schema template, field, option, relasi Category Detail, serta fallback Main Category.
- Lima template baseline: Bolt, Pipe, Valve, Flange, dan Nut.
- UI admin untuk membuat/mengubah template, field, option, serta pemetaan kategori.
- Form create/edit produk menampilkan field berdasarkan template kategori.
- Validasi server untuk required field, option, atribut lintas-template, dan kombinasi duplikat.
- Label varian, SKU, ringkasan, pilihan customer, filter katalog, dan API tidak lagi dibatasi ke
  daftar atribut Bolt.
- Template dan import Excel dinamis per template; format Bolt lama tetap didukung.
- Migrasi database bersifat backward-compatible dan tidak mengubah nilai atribut produk lama.
- Automated regression: seluruh suite lulus, 201 test dengan 1.192 assertion.
- Build frontend production dan kompilasi Blade lulus.

Rollout data sengaja tidak memetakan kategori berdasarkan nama secara otomatis. Admin perlu memilih
template pada Category Detail (atau fallback Main Category) setelah klasifikasi katalog dikonfirmasi.

## Ringkasan

Sistem harus menampilkan dan memvalidasi spesifikasi teknis yang berbeda sesuai jenis produk.
Produk Bolt, Pipe, Valve, Flange, dan Nut tidak lagi dipaksa memakai kumpulan field yang sama.
Admin memilih kategori produk, kemudian form spesifikasi menyesuaikan secara otomatis. Customer hanya
melihat pilihan spesifikasi yang relevan dan hanya dapat memilih kombinasi varian yang benar-benar
tersedia.

Fondasi varian yang ada tetap digunakan: satu `Product` memiliki banyak `ProductVariant`, sedangkan
harga, stok, SKU, gambar, berat, dan dimensi tetap berada pada varian. Perubahan utama adalah
menambahkan konfigurasi **template spesifikasi** yang menghubungkan kategori dengan definisi atribut,
pilihan nilai, aturan validasi, urutan tampilan, dan perilaku pembentukan varian.

Implementasi tidak boleh mendeteksi jenis produk dari nama kategori. Relasi kategori-template wajib
disimpan secara eksplisit di database agar perubahan nama kategori tidak mengubah perilaku form.

## Latar Belakang dan Temuan Audit

### Hal yang sudah tersedia

- `attribute_definitions` sudah menyimpan kode, nama, tipe data, unit, status filter, dan urutan atribut.
- `product_variant_attributes` sudah menyimpan nilai atribut per varian.
- `product_variants` sudah menyimpan SKU, harga, stok, gambar, berat, dan dimensi pengiriman.
- Detail produk storefront sudah dapat membentuk pilihan varian dari atribut yang tersimpan.
- Open Catalog API sudah mengembalikan atribut sebagai pasangan `code`, `name`, dan `value`.
- Keranjang dan checkout sudah menggunakan `product_variant_id`, sehingga harga dan stok tetap dapat
  mengikuti kombinasi varian terpilih.

### Kesenjangan saat ini

1. Seluruh kategori memakai lima atribut global yang sama: `diameter`, `length_mm`, `thread_type`,
   `grade`, dan `material`.
2. Form create/edit produk menampilkan seluruh definisi atribut tanpa memeriksa kategori.
3. Urutan dan label atribut pada nama varian, SKU, filter storefront, serta ringkasan produk masih
   hard-coded untuk atribut Bolt.
4. Server belum memvalidasi apakah atribut yang dikirim memang diizinkan atau diwajibkan oleh
   kategori produk.
5. Pilihan nilai atribut berasal dari nilai yang pernah dipakai secara global. Nilai Pipe dapat
   tercampur dengan pilihan Bolt apabila nama atributnya sama.
6. Template import Excel memiliki kolom atribut tetap sehingga belum dapat menangani jenis produk
   dengan struktur spesifikasi berbeda.
7. Belum ada UI admin untuk mengelola template, field, pilihan nilai, required state, atau urutan.
8. Test produk yang ada baru mencakup struktur atribut lama dan belum menguji isolasi antar-template.

## Masalah yang Ingin Diselesaikan

- Admin berisiko mengisi atribut yang tidak relevan atau melewatkan spesifikasi wajib.
- Customer sulit memastikan varian teknis yang dipilih benar untuk jenis barangnya.
- Penambahan jenis produk baru membutuhkan perubahan kode di banyak tempat.
- Pilihan atribut tidak memiliki sumber master yang terkontrol per template.
- SKU, ringkasan varian, filter, import, dan API dapat menghasilkan perilaku berbeda karena belum
  memakai konfigurasi yang sama.
- Kombinasi varian tidak valid atau duplikat dapat tersimpan.

## Tujuan

- Field spesifikasi admin berubah otomatis berdasarkan kategori yang dipilih.
- Setiap kategori detail dapat dihubungkan ke satu template spesifikasi aktif.
- Satu template dapat dipakai oleh beberapa kategori detail.
- Definisi field mendukung teks, angka, pilihan tunggal, dan desimal beserta satuan.
- Field dapat ditandai wajib, filterable, memengaruhi varian, dan memiliki urutan tampilan.
- Daftar pilihan dapat dibatasi per field/template dan dikelola tanpa perubahan kode.
- Setiap kombinasi varian memiliki harga, stok, SKU, gambar, dan data logistik sendiri.
- Customer hanya dapat memilih kombinasi varian yang tersedia; pilihan mustahil dinonaktifkan.
- Ringkasan spesifikasi, SKU, storefront, API, dan import menggunakan sumber konfigurasi yang sama.
- Data produk lama tetap terbaca selama dan setelah migrasi.
- Jenis produk baru dapat ditambahkan melalui konfigurasi admin tanpa menambah conditional baru pada
  controller atau view produk.

## Non-Tujuan

- Tidak mengganti Laravel Blade/Alpine dengan framework frontend lain.
- Tidak mengubah logika dasar cart, checkout, pembayaran, pajak, kupon, point, atau reservasi stok.
- Tidak membuat configurator engineering yang menghitung kompatibilitas standar secara otomatis.
- Tidak menghitung Thickness Pipe dari kombinasi Nominal Size dan Schedule pada rilis pertama.
- Tidak membuat harga berbasis formula; harga tetap ditentukan per varian.
- Tidak menghapus tabel atribut dan varian lama sebelum migrasi terverifikasi.
- Tidak menyatukan tiga jalur taksonomi (`main_categories`, `category_details`, dan legacy
  `categories`) dalam PRD ini.
- Tidak menjadikan nilai stok pasti tersedia pada Open Catalog API publik.

## Persona dan User Stories

### Admin katalog

- Sebagai admin, ketika memilih kategori Pipe, saya hanya melihat field spesifikasi Pipe.
- Sebagai admin, saya dapat menambah beberapa kombinasi varian dan mengisi harga/stok masing-masing.
- Sebagai admin, saya mendapat peringatan jika field wajib kosong atau kombinasi varian duplikat.
- Sebagai admin, saya dapat mengganti kategori dan melihat dampaknya sebelum atribut lama dihapus.
- Sebagai admin, saya dapat mengimpor produk sesuai template spesifikasi kategorinya.

### Pengelola katalog / super admin

- Sebagai pengelola katalog, saya dapat membuat template spesifikasi dan menghubungkannya ke kategori.
- Sebagai pengelola katalog, saya dapat mengatur nama, kode, tipe input, satuan, pilihan, required,
  filterable, variant-driving, dan urutan setiap field.
- Sebagai pengelola katalog, saya dapat menonaktifkan field tanpa merusak produk lama.

### Customer

- Sebagai customer, saya melihat pilihan teknis yang sesuai dengan jenis produk.
- Sebagai customer, perubahan spesifikasi memperbarui varian, harga, stok, SKU, dan gambar.
- Sebagai customer, saya tidak dapat memilih kombinasi spesifikasi yang tidak dijual.
- Sebagai customer teknis, saya dapat membaca tabel spesifikasi lengkap dari varian terpilih.

### Konsumen API

- Sebagai konsumen API, saya tetap menerima atribut varian dalam bentuk generik.
- Sebagai konsumen API, saya dapat membaca metadata unit dan urutan tanpa bergantung pada kode Bolt.
- Sebagai konsumen API, saya tidak menerima angka stok pasti atau konfigurasi admin internal.

## Keputusan Produk dan Arsitektur

### 1. Template adalah sumber konfigurasi utama

Gunakan entitas `specification_templates` sebagai kumpulan field untuk suatu jenis produk. Contoh
template awal: Bolt, Pipe, Valve, Flange, dan Nut.

Template tidak sama dengan kategori pemasaran. Satu template Pipe dapat dipakai oleh beberapa
`category_details`, misalnya Seamless Pipe dan Welded Pipe. Karena itu relasi kategori-template
disimpan eksplisit dan tidak diturunkan dari nama atau slug.

### 2. Pengikatan utama menggunakan Category Detail

- Target akhir: setiap produk teknis memilih `category_detail_id` yang memiliki template.
- `category_details` menjadi pengikat utama karena paling spesifik.
- `main_categories` boleh memiliki template default sebagai fallback masa transisi.
- Jalur legacy `categories` tetap terbaca, tetapi produk baru yang membutuhkan spesifikasi dinamis
  diarahkan ke `category_details`.
- Jika Category Detail dan Main Category sama-sama memiliki template, konfigurasi Category Detail
  menang.

### 3. Nilai spesifikasi tetap disimpan per varian

Pada rilis pertama, nilai spesifikasi tetap menggunakan `product_variant_attributes`. Pendekatan ini
sesuai dengan kebutuhan pemilihan customer: setiap kombinasi atribut dapat mempunyai harga, stok,
SKU, gambar, dan data logistik berbeda.

Field diberi flag `affects_variant`:

- `true`: menjadi bagian identitas kombinasi, label varian, dan pembentukan SKU.
- `false`: tetap ditampilkan sebagai spesifikasi tetapi tidak dipakai membedakan kombinasi.

Untuk menghindari perubahan skema nilai yang terlalu besar pada rilis pertama, field non-variant
boleh disalin ke setiap varian oleh service aplikasi. Normalisasi spesifikasi tingkat produk dapat
menjadi fase lanjutan jika volume data membutuhkannya.

### 4. Konfigurasi tidak boleh hard-coded di UI/controller

Urutan, label, unit, kewajiban, dan pembentukan label varian harus dibaca dari template aktif.
Kode khusus seperti daftar tetap `diameter`, `length_mm`, dan seterusnya harus dihapus dari alur
generik create/edit/store/update/storefront.

### 5. Perubahan template tidak boleh merusak data historis

- Field/template menggunakan soft state (`is_active`), bukan langsung dihapus jika sudah dipakai.
- Nilai atribut lama tetap dapat ditampilkan walaupun field dinonaktifkan.
- Menghapus field yang sudah dipakai ditolak; admin hanya dapat menonaktifkannya.
- Pergantian template kategori harus menampilkan analisis dampak terhadap produk yang sudah ada.

## Template Awal

Template awal berikut menjadi baseline konfigurasi. Daftar pilihan final dapat dilengkapi oleh tim
katalog melalui UI master.

| Template | Field | Tipe awal | Unit | Wajib | Memengaruhi varian |
| --- | --- | --- | --- | --- | --- |
| Bolt | Diameter | Select | - | Ya | Ya |
| Bolt | Panjang | Number/select | mm | Ya | Ya |
| Bolt | Tipe Drat | Select | - | Ya | Ya |
| Bolt | Grade | Select | - | Ya | Ya |
| Bolt | Material | Select | - | Ya | Ya |
| Pipe | Nominal Size | Select | inch | Ya | Ya |
| Pipe | Schedule / Class | Select | - | Ya | Ya |
| Pipe | Material | Select | - | Ya | Ya |
| Pipe | Thickness | Decimal/select | mm | Ya | Ya |
| Pipe | Length | Decimal/select | m | Ya | Ya |
| Pipe | Standard | Select | - | Ya | Ya |
| Valve | Valve Type | Select | - | Ya | Ya |
| Valve | Size | Select | inch | Ya | Ya |
| Valve | Pressure Rating | Select | - | Ya | Ya |
| Valve | Material | Select | - | Ya | Ya |
| Valve | Connection Type | Select | - | Ya | Ya |
| Valve | Standard | Select | - | Ya | Ya |
| Flange | Standard | Select | - | Ya | Ya |
| Flange | Class / Rating | Select | - | Ya | Ya |
| Flange | Facing | Select | - | Ya | Ya |
| Flange | Size | Select | inch | Ya | Ya |
| Flange | Material | Select | - | Ya | Ya |
| Nut | Thread Size | Select | - | Ya | Ya |
| Nut | Pitch | Decimal/select | mm | Ya | Ya |
| Nut | Grade | Select | - | Ya | Ya |
| Nut | Material | Select | - | Ya | Ya |
| Nut | Finish / Coating | Select | - | Ya | Ya |

Status required dan `affects_variant` di atas adalah baseline, bukan nilai yang di-hard-code.
Pengelola katalog dapat mengubahnya melalui konfigurasi dengan validasi dampak.

## Scope Fungsional

### 1. Master Template Spesifikasi

Tambahkan halaman admin untuk:

- Melihat daftar template, jumlah field, kategori terhubung, dan status.
- Membuat dan mengubah nama/kode template.
- Menambah field dari definisi atribut yang sudah ada atau membuat definisi baru.
- Mengatur label khusus template bila diperlukan.
- Mengatur tipe input, unit, required, filterable, affects variant, dan sort order.
- Mengelola pilihan nilai beserta urutannya.
- Menonaktifkan template, field, atau pilihan yang tidak lagi dipakai.
- Melihat usage count sebelum perubahan berisiko.

Hak akses harus mengikuti permission admin. Jika modul permission baru ditambahkan, minimum action
adalah `specification-templates.index`, `create`, `edit`, dan `delete/deactivate`.

### 2. Relasi Template dengan Kategori

- Satu Category Detail memiliki maksimal satu template aktif.
- Satu template dapat digunakan banyak Category Detail.
- Main Category dapat memiliki satu template default opsional.
- UI kategori menampilkan template yang sedang dipakai.
- Perubahan relasi yang memengaruhi produk existing membutuhkan konfirmasi dampak.
- Produk tanpa template tetap dapat dibuka menggunakan mode kompatibilitas atribut lama.

### 3. Form Create/Edit Produk Admin

Alur yang diharapkan:

1. Admin mengisi nama dan memilih kategori.
2. Sistem memuat template efektif kategori.
3. Bagian Spesifikasi Teknis dirender sesuai field template.
4. Admin mengisi satu atau lebih kombinasi varian.
5. Label ringkas dan preview SKU terbentuk dari field `affects_variant` sesuai urutan.
6. Harga, stok, gambar, berat, dan dimensi diisi per kombinasi.
7. Server memvalidasi payload terhadap template terbaru sebelum menyimpan.

Perilaku penting:

- Form create dan edit harus memiliki perilaku identik.
- Perubahan kategori saat form berisi data menampilkan dialog: pertahankan field yang kompatibel,
  buang field tak kompatibel, atau batalkan perubahan kategori.
- Field select mendukung pencarian, keyboard, dan state kosong.
- Nilai custom hanya diizinkan apabila konfigurasi field `allow_custom_value = true`.
- Field angka menyimpan nilai numerik canonical; unit hanya presentasi.
- Tombol simpan dinonaktifkan selama submit untuk mencegah duplikasi.
- Error ditampilkan pada baris varian dan field yang tepat.

### 4. Kombinasi Varian dan SKU

- Identitas kombinasi dibentuk dari nilai field aktif dengan `affects_variant = true`.
- Dua varian dalam produk yang sama tidak boleh mempunyai kombinasi canonical identik.
- Perbandingan select/text bersifat trim dan case-insensitive.
- Perbandingan angka memakai nilai canonical, bukan teks tampilan.
- SKU dibentuk dari nama produk dan nilai varian sesuai urutan template, lalu dijamin unik.
- SKU yang sudah dipakai transaksi tidak boleh berubah diam-diam. Edit atribut menampilkan SKU baru
  dan memerlukan konfirmasi sebelum disimpan.
- Bila tidak ada field yang memengaruhi varian, produk tetap memiliki satu varian standar.
- Varian yang sudah dipakai transaksi mengikuti aturan penghapusan existing dan tidak boleh dihapus
  tanpa pemeriksaan usage.

### 5. Detail Produk Customer

- Grup pilihan dibentuk dari template/atribut produk, bukan daftar kode tetap.
- Urutan field mengikuti template.
- Memilih suatu nilai mempersempit opsi pada field lain ke kombinasi yang tersedia.
- Nilai yang tidak menghasilkan kombinasi valid ditampilkan disabled, bukan mengarah ke varian acak.
- Setelah kombinasi lengkap ditemukan, sistem memperbarui harga, harga promo, stok, status stok,
  gambar, SKU, ringkasan, dan `product_variant_id`.
- Tombol cart/checkout tidak aktif sampai kombinasi valid dipilih.
- Tabel Spesifikasi menampilkan nilai varian terpilih lengkap dengan unit.
- State habis, terbatas, dan tersedia tetap mengikuti stok varian terpilih.
- UI desktop dan mobile menggunakan aturan pemilihan yang sama.

### 6. Listing, Search, dan Filter

- Filter kategori hanya menampilkan atribut `is_filterable` yang terhubung ke template produk pada
  kategori tersebut.
- Nilai filter diambil dari varian produk aktif dalam scope kategori/perusahaan.
- Search dapat menemukan nama produk, SKU, dan nilai atribut yang relevan.
- Product card tetap ringkas; maksimal dua atau tiga atribut prioritas dapat ditampilkan berdasarkan
  konfigurasi, bukan seluruh spesifikasi.
- Filter tidak boleh mencampurkan unit atau nilai dari definisi atribut yang berbeda.

### 7. Import dan Export Excel

Format import atribut tetap saat ini tidak cukup untuk seluruh jenis produk. Rilis dinamis memakai
strategi berikut:

- Admin memilih Category Detail/template sebelum mengunduh template import.
- File yang diunduh berisi kolom dasar produk + field dari template terpilih.
- Metadata versi template dan ID/kode template disimpan pada sheet metadata tersembunyi atau header
  terkontrol.
- Import memvalidasi bahwa template file masih cocok dengan kategori target.
- Header boleh mengikuti konfigurasi template tetapi dipetakan menggunakan kode atribut yang stabil,
  bukan label yang dapat berubah.
- Error import mencantumkan nomor baris, kode field, dan penyebab.
- Satu baris mewakili satu kombinasi varian; baris dengan produk dan kategori sama dikelompokkan.
- Template import lama tetap diterima dalam masa transisi untuk template Bolt legacy.
- Export produk menyertakan template dan spesifikasi agar file dapat diaudit.

### 8. Open Catalog API

- Endpoint dan version prefix `/api/v1` tetap backward-compatible.
- `attributes` per varian tetap berupa daftar generik.
- Tambahkan `unit` dan `sort_order` bila diperlukan tanpa menghapus field lama.
- `attribute_summary` dibentuk dari urutan template efektif.
- Angka stok tetap tidak diekspos; hanya `in_stock`.
- Konfigurasi internal seperti permission, usage count, dan flag administrasi tidak diekspos.
- Cache katalog di-invalidasi ketika template, mapping kategori, option, produk, atau varian berubah.
- Perubahan breaking pada bentuk response memerlukan `/api/v2`, bukan mengubah kontrak v1 diam-diam.

## Kebutuhan Data

Nama tabel final dapat disesuaikan dengan konvensi migration, tetapi capability berikut wajib ada.

### `specification_templates`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key |
| `name` | Nama tampilan, misalnya Pipe |
| `code` | Kode stabil dan unik, misalnya `pipe` |
| `description` | Catatan penggunaan opsional |
| `is_active` | Status pemakaian |
| timestamps | Audit waktu |

### `specification_template_fields`

Pivot berkonfigurasi antara template dan `attribute_definitions`.

| Kolom | Keterangan |
| --- | --- |
| `specification_template_id` | Template pemilik |
| `attribute_definition_id` | Definisi atribut |
| `label_override` | Label khusus opsional |
| `input_type` | `text`, `number`, `decimal`, atau `select` |
| `is_required` | Wajib diisi |
| `is_filterable` | Boleh menjadi filter storefront |
| `affects_variant` | Menjadi identitas kombinasi/SKU |
| `allow_custom_value` | Select boleh menerima nilai baru |
| `sort_order` | Urutan form, ringkasan, dan storefront |
| `is_active` | Status field di template |

Unique constraint: pasangan template dan definisi atribut harus unik.

### `attribute_options`

| Kolom | Keterangan |
| --- | --- |
| `attribute_definition_id` | Definisi atribut |
| `specification_template_id` | Scope template; nullable hanya bila pilihan global memang diinginkan |
| `value` | Nilai canonical |
| `label` | Label tampilan opsional |
| `sort_order` | Urutan pilihan |
| `is_active` | Status pilihan |

Unique constraint harus mencegah nilai canonical duplikat dalam field/template yang sama.

### Relasi kategori-template

Pilihan implementasi yang direkomendasikan:

- Tambahkan `specification_template_id` nullable pada `category_details`.
- Tambahkan `default_specification_template_id` nullable pada `main_categories` untuk fallback.

Pendekatan kolom langsung dipilih karena cardinality-nya satu kategori ke maksimal satu template dan
lebih sederhana untuk validasi serta eager loading dibanding polymorphic mapping.

### Penyesuaian `attribute_definitions`

- `code` tetap menjadi identifier stabil dan unik.
- `data_type` diselaraskan dengan input/value normalization.
- `unit` menjadi default; field template boleh memiliki override pada fase lanjutan.
- `is_filterable` lama menjadi default saat field dimasukkan ke template, sedangkan nilai efektif
  berada pada `specification_template_fields`.
- Definisi yang sudah digunakan tidak boleh dihapus secara fisik.

## Resolusi Template Efektif

Urutan resolusi saat produk dibaca atau disimpan:

1. Template aktif dari `category_detail_id`.
2. Jika tidak ada, template default aktif dari `main_category_id`.
3. Jika tidak ada, mode kompatibilitas berdasarkan atribut yang sudah tersimpan pada varian.
4. Produk baru tanpa template dan tanpa atribut menggunakan satu varian standar, sepanjang kategori
   memang dikonfigurasi tidak membutuhkan spesifikasi.

Resolusi yang sama wajib dipakai oleh form admin, validator, label/SKU service, storefront, filter,
import, export, dan API resource.

## Aturan Validasi Server

- Kategori yang dikirim harus valid dan relasi Main Category/Category Detail harus konsisten.
- Definisi atribut harus menjadi field aktif dalam template efektif.
- Semua field `is_required` harus memiliki nilai pada setiap varian.
- Field angka harus numeric dan mengikuti batas min/max jika kelak dikonfigurasi.
- Select tanpa `allow_custom_value` hanya menerima option aktif dari template tersebut.
- Unit tidak boleh dikirim sebagai bagian nilai canonical.
- Kombinasi field `affects_variant` harus unik dalam satu produk.
- Minimal satu varian diperlukan.
- Harga, stok, dan berat tetap mengikuti validasi existing.
- SKU hasil akhir harus unik.
- Payload berisi atribut dari template lain ditolak dengan error `422`.
- Validasi tidak hanya dilakukan di JavaScript; server adalah sumber kebenaran.

## Migrasi dan Backward Compatibility

### Strategi migrasi

1. Buat lima template awal dan seluruh definisi/option yang diperlukan.
2. Hubungkan kategori yang dapat dikenali setelah verifikasi manual; jangan memetakan hanya dengan
   pencocokan nama otomatis tanpa laporan preview.
3. Petakan lima atribut existing ke template Bolt.
4. Produk lama mempertahankan record `product_variant_attributes` tanpa rewrite destruktif.
5. Produk lama tanpa mapping kategori memakai mode kompatibilitas sehingga masih dapat diedit dan
   ditampilkan.
6. Sediakan report produk yang belum memiliki template atau mempunyai atribut yang tidak cocok.
7. Setelah semua produk terpetakan dan test lulus, nonaktifkan jalur hard-coded lama.

### Rollback

- Migration down hanya menghapus tabel/kolom konfigurasi baru, tidak menghapus nilai atribut varian.
- Deployment aplikasi harus tetap dapat membaca data legacy selama masa transisi.
- Sebelum aktivasi massal, backup database dan hasil report mapping wajib tersedia.

## UX dan Accessibility

- Bagian spesifikasi menggunakan layout padat dan terstruktur sesuai karakter katalog industrial.
- Pergantian kategori menampilkan loading dan empty/error state yang jelas.
- Label selalu terhubung ke input; error dapat dibaca screen reader.
- Select dapat digunakan dengan keyboard dan tidak hanya mengandalkan warna untuk disabled state.
- Unit tampil berdekatan dengan nilai tetapi tidak mengubah nilai canonical.
- Target sentuh minimum 44px pada mobile untuk pemilih varian utama.
- Focus tidak hilang saat pilihan lain berubah/disabled.
- Perubahan harga, stok, dan SKU diumumkan melalui live region yang tidak mengganggu.
- Tidak ada horizontal overflow pada kombinasi field panjang.

## Keamanan, Integritas, dan Multi-Company

- Template dan master atribut bersifat katalog global kecuali diputuskan lain pada fase mendatang.
- Produk dan varian tetap terisolasi berdasarkan `company_id` melalui scope existing.
- Admin hanya dapat mengubah produk milik perusahaan aktif yang diizinkan.
- Pengelolaan template dibatasi permission khusus karena dampaknya lintas perusahaan.
- Semua ID template, field, option, produk, dan varian dari request divalidasi ulang di server.
- Jangan percaya harga, stok, SKU, label, atau ringkasan yang dibentuk browser.
- Audit log direkomendasikan untuk perubahan template, mapping kategori, dan option.
- Output label dan nilai tetap melalui escaping Blade/API serialization yang aman.

## Observability dan Audit

- Log perubahan template dan kategori yang memengaruhi produk existing.
- Sediakan report: kategori tanpa template, produk tanpa template, produk dengan field wajib kosong,
  kombinasi duplikat, dan atribut orphan.
- Catat kegagalan import per file/baris tanpa menyimpan isi sensitif yang tidak diperlukan.
- Pantau error validasi `422` setelah aktivasi untuk menemukan integrasi/import lama.
- Ukur query count detail produk agar konfigurasi template tidak menimbulkan N+1.

## Acceptance Criteria

### Konfigurasi

- [ ] Admin berizin dapat membuat template dan mengatur field beserta option-nya.
- [ ] Satu Category Detail dapat dihubungkan ke satu template dan satu template dapat dipakai banyak
      Category Detail.
- [ ] Mengubah nama kategori tidak mengubah template yang terhubung.
- [ ] Field atau template yang sudah dipakai tidak dapat dihapus secara destruktif.

### Admin produk

- [ ] Memilih Bolt, Pipe, Valve, Flange, atau Nut menampilkan field yang sesuai tabel baseline.
- [ ] Create dan edit memakai template dan aturan validasi yang sama.
- [ ] Field wajib kosong ditolak pada field/baris yang tepat.
- [ ] Atribut dari template lain ditolak server.
- [ ] Dua kombinasi varian identik dalam satu produk ditolak.
- [ ] Harga, stok, gambar, dan data logistik tetap tersimpan per varian.
- [ ] Pergantian kategori dengan data terisi meminta konfirmasi dan tidak menghapus data diam-diam.
- [ ] Produk legacy tanpa mapping tetap dapat dibuka dan disimpan melalui mode kompatibilitas.

### Customer

- [ ] Detail produk membentuk grup pilihan dari template, tanpa daftar kode atribut hard-coded.
- [ ] Kombinasi yang tidak tersedia tampil disabled dan tidak dapat masuk cart.
- [ ] Kombinasi valid memperbarui varian ID, harga, stok, gambar, SKU, dan tabel spesifikasi.
- [ ] Perilaku desktop dan mobile konsisten.
- [ ] Unit dan urutan atribut tampil sesuai konfigurasi.

### Import, API, dan regresi

- [ ] Template Excel dapat diunduh per Category Detail/template.
- [ ] Import menolak file dengan versi/template yang tidak cocok dan memberikan error per baris.
- [ ] Format Bolt legacy tetap didukung selama masa transisi yang ditetapkan.
- [ ] Open Catalog API tetap backward-compatible dan mengembalikan atribut generik.
- [ ] API tidak mengekspos angka stok atau konfigurasi admin internal.
- [ ] Cart, checkout, wishlist, flash sale, redeem point, laporan stok, dan transaksi tetap lulus test.
- [ ] Tidak ada N+1 query baru pada halaman katalog dan detail produk.

## Edge Cases

- Kategori belum memiliki template: gunakan fallback Main Category atau mode kompatibilitas.
- Template tidak aktif tetapi sudah dipakai produk: storefront tetap membaca snapshot/atribut tersimpan;
  admin mendapat peringatan untuk migrasi.
- Option dinonaktifkan setelah dipakai: nilai lama tetap tampil, tetapi tidak tersedia untuk varian baru.
- Dua option berbeda label tetapi sama nilai canonical: ditolak oleh unique rule.
- Admin membuka form lama lalu template berubah: submit ditolak dengan pesan konfigurasi telah berubah
  dan form perlu dimuat ulang.
- Produk hanya memiliki satu kombinasi: pilihan dapat ditampilkan sebagai spesifikasi read-only tanpa
  memaksa dropdown yang tidak berguna.
- Sebagian varian kehabisan stok: kombinasi tetap terlihat dengan status habis, sesuai kebijakan
  storefront; tidak boleh berpindah otomatis ke varian lain saat masuk cart.
- Flash sale hanya menunjuk varian tertentu: harga promo hanya berlaku pada varian yang terhubung.
- Atribut angka `2`, `2.0`, dan `2.000`: dianggap nilai canonical yang sama.
- Satuan berubah: data canonical tidak diubah otomatis; perubahan unit membutuhkan migrasi nilai
  terpisah bila maknanya berubah.
- Kategori produk diubah setelah ada transaksi: snapshot detail transaksi tidak ikut berubah.
- Field yang sama dipakai beberapa template dengan option berbeda: pilihan harus ter-scope template.

## Testing Strategy

### Unit test

- Resolusi template efektif.
- Normalisasi nilai teks dan angka.
- Pembentukan label varian dan SKU berdasarkan urutan template.
- Deteksi kombinasi duplikat.
- Validasi option dan required field.

### Feature test

- CRUD template dan permission.
- Mapping kategori-template.
- Create/update produk untuk kelima template baseline.
- Penolakan atribut lintas-template.
- Perlindungan varian yang sudah dipakai transaksi.
- Import per template dan kompatibilitas Bolt legacy.
- API serialization dan larangan angka stok.
- Isolasi produk antarperusahaan.

### Browser/E2E test

- Admin memilih kategori lalu field berubah.
- Admin membuat beberapa kombinasi Pipe dan menyimpannya.
- Customer memilih kombinasi dan melihat harga/stok/SKU/gambar berubah.
- Opsi mustahil disabled.
- Add to cart membawa `product_variant_id` yang tepat.
- Skenario yang sama diverifikasi pada desktop dan mobile.

### Quality gate

- `php artisan test` lulus.
- Build asset frontend lulus.
- Migration diuji pada database kosong dan salinan data existing.
- Report mapping legacy direview sebelum aktivasi massal.
- Tidak ada perubahan breaking pada route publik dan API v1.

## Rekomendasi Tahapan Implementasi

| Fase | Fokus | Dependensi | Bukti selesai |
| --- | --- | --- | --- |
| 0 | Inventory kategori, atribut, dan data legacy | Tidak ada | Report mapping dan keputusan kategori-template |
| 1 | Skema template, field, option, relasi, dan model | Fase 0 | Migration + unit test + seed baseline |
| 2 | Service resolusi, normalisasi, label, SKU, dan validator | Fase 1 | Unit/feature test domain |
| 3 | CRUD template dan mapping kategori di admin | Fase 2 | Permission test + browser smoke test |
| 4 | Form create/edit produk dinamis | Fase 2-3 | Test kelima template + update legacy |
| 5 | Detail produk dan pemilih kombinasi customer | Fase 4 | E2E select-to-cart desktop/mobile |
| 6 | Filter, search, import/export, dan API | Fase 4-5 | Contract/import/filter tests |
| 7 | Migrasi data, observability, regresi, dan rollout | Fase 0-6 | Report bersih + full regression + sign-off |

Setiap fase harus dapat diverifikasi sebelum fase berikutnya diaktifkan. Schema dan service domain
didahulukan agar UI admin/customer tidak membuat aturan bisnisnya sendiri-sendiri.

## Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Mapping kategori lama tidak akurat | Field salah muncul | Preview report + verifikasi manual sebelum aktivasi |
| Konfigurasi berubah saat form terbuka | Payload tidak konsisten | Version/check updated timestamp dan tolak stale submit |
| Option global tercampur | Pilihan teknis salah | Scope option per template + unique constraint |
| Kombinasi varian meledak | Form lambat/sulit dikelola | Tidak membuat Cartesian product otomatis; admin menambah kombinasi nyata saja |
| SKU berubah setelah transaksi | Audit dan integrasi terganggu | Preview + konfirmasi + aturan immutable/alias bila sudah dipakai |
| Import lama berhenti | Operasional terganggu | Adapter Bolt legacy dan masa transisi terdokumentasi |
| Query template menambah N+1 | Storefront lambat | Eager load + cache metadata template + performance test |
| Field non-variant berulang | Duplikasi data | Diterima pada rilis pertama; evaluasi normalisasi fase lanjut |

## Definition of Done

Fitur dianggap selesai hanya jika:

1. Kelima template baseline dapat dikonfigurasi dan digunakan end-to-end.
2. Tidak ada daftar atribut produk yang hard-coded pada form, controller, storefront, atau SKU builder.
3. Validasi server menjamin template, required field, option, dan kombinasi varian.
4. Produk existing tetap dapat dibaca, diedit, dibeli, dan muncul di API.
5. Import, filter, search, API, cart, checkout, flash sale, dan laporan stok telah diuji regresi.
6. Mapping legacy dan rollback plan terdokumentasi.
7. Seluruh acceptance criteria memiliki bukti test atau hasil review yang dapat ditelusuri.

## Open Questions Sebelum Implementasi

Pertanyaan berikut tidak menghalangi pembuatan fondasi, tetapi perlu dikonfirmasi sebelum seed dan UI
final:

1. Category Detail mana yang masing-masing menggunakan template Bolt, Pipe, Valve, Flange, dan Nut?
2. Apakah pengelolaan template hanya untuk super admin atau admin perusahaan tertentu juga boleh?
3. Apakah SKU existing boleh berubah ketika atribut diperbarui, atau harus immutable setelah dibuat?
4. Apakah produk habis tetap dapat dipilih dengan label habis, atau option-nya harus disabled penuh?
5. Daftar option master final untuk setiap field dan standar penulisan nilainya (misalnya `SS316`
   versus `Stainless Steel (SS316)`).
6. Berapa lama adapter import Bolt legacy harus dipertahankan setelah format baru aktif?
