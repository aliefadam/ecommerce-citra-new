# PRD: Alur Dokumen Penjualan B2B (Quotation - Sales Order - Proforma Invoice - Surat Jalan - Packing List - Invoice)

> Status: Siap dieksekusi — company-aware & urutan implementasi sudah lengkap (v2, 2026-07-20). Ini adalah Fase 3 dari `prd-multi-company-foundation.md`, dieksekusi setelah Fase 1 & Fase 2 (fondasi multi-perusahaan + marketplace/checkout) selesai dan sudah diverifikasi.

## Ringkasan

Fitur ini menambahkan alur dokumen penjualan bergaya B2B/distributor yang terpisah dari alur `Transaction` (checkout online & transaksi manual/POS) yang sudah ada. Alur ini dimulai dari **Quotation** (penawaran harga), dikonfirmasi menjadi **Sales Order** (order internal yang menggerakkan fulfillment), opsional dilengkapi **Proforma Invoice** (tagihan sementara untuk minta DP/pembayaran di muka jika diperlukan customer tertentu), lalu proses pengiriman barang dicatat lewat **Surat Jalan** dan **Packing List** (bisa lebih dari satu, untuk pengiriman bertahap), dan diakhiri dengan **Invoice** (tagihan final resmi).

Keputusan utama:

- Alur ini adalah entitas baru yang berdiri sendiri, tidak menggantikan atau mengubah `Transaction`, `TransactionDetail`, atau logic checkout/manual-sales yang sudah berjalan.
- Satu Quotation dapat dipakai berkali-kali menjadi beberapa Sales Order (pembelian dicicil/parsial dalam beberapa PO terpisah selama quotation masih berlaku), bukan hanya sekali konversi.
- **Sales Order adalah dokumen internal yang selalu dibuat** setiap kali Quotation dikonversi — merepresentasikan komitmen order yang menggerakkan fulfillment (Surat Jalan/Packing List), lepas dari apakah ada kebutuhan DP atau tidak.
- **Proforma Invoice bersifat opsional**, hanya diterbitkan untuk customer yang butuh dokumen tagihan sementara/DP sebelum barang dikirim. Fulfillment (Surat Jalan) tidak bergantung pada keberadaan Proforma Invoice — keduanya ditarik langsung dari Sales Order.
- Satu Sales Order dapat dipecah menjadi beberapa Surat Jalan + Packing List (pengiriman parsial/bertahap), dan hasil pengirimannya digabung menjadi satu atau lebih Invoice final.
- **Invoice B2B dan Proforma Invoice merepresentasikan piutang (receivable)**, bukan sekadar status lunas/belum — pelunasannya dicatat lewat ledger pembayaran (`document_payments`) yang mendukung pembayaran bertahap/cicilan, dan DP yang sudah dibayar di Proforma Invoice otomatis jadi kredit pengurang piutang saat Invoice B2B diterbitkan.
- Stok baru dipotong ketika Surat Jalan dibuat (barang benar-benar keluar gudang), bukan saat Quotation/Sales Order/Proforma Invoice/Invoice dibuat.
- Setiap dokumen memakai pola snapshot (nama produk, harga, data customer disalin saat dokumen dibuat) mengikuti pola `TransactionDetail` yang sudah ada di codebase.
- Setiap dokumen punya nomor dan status sendiri, dengan histori status tercatat mengikuti pola `TransactionStatusHistory`/`TaxInvoiceRequestService`.
- Aktor utama: Admin/Sales untuk dokumen komersial (Quotation, Sales Order, Proforma Invoice, Invoice), Staff Gudang untuk dokumen logistik (Surat Jalan, Packing List).

## Tujuan

- Admin/Sales bisa membuat Quotation untuk customer B2B, termasuk override harga per item hasil negosiasi.
- Quotation punya masa berlaku (`valid_until`) dan otomatis kedaluwarsa jika lewat tanggal tanpa tindak lanjut.
- Quotation yang disetujui customer (ditandai manual oleh admin) bisa dikonversi menjadi Sales Order, dan dapat dikonversi berkali-kali (per item, sebagian qty setiap kali) selama sisa qty & masa berlaku masih ada.
- Admin bisa menutup manual sebuah Quotation kapan saja setelah minimal satu Sales Order dibuat, untuk menandai bahwa customer tidak akan melanjutkan pembelian sisa qty yang belum terpakai.
- Sales Order merepresentasikan komitmen order yang sudah dikonfirmasi, menjadi acuan utama untuk perencanaan pengiriman (fulfillment), lepas dari kebutuhan DP.
- Admin/Sales bisa menerbitkan Proforma Invoice dari sebuah Sales Order jika customer memerlukan dokumen tagihan sementara/DP sebelum barang dikirim — bersifat opsional, tidak semua Sales Order butuh ini.
- Satu Sales Order bisa dipecah menjadi beberapa pengiriman, masing-masing dicatat sebagai Surat Jalan + Packing List, tanpa perlu menunggu Proforma Invoice.
- Staff Gudang bisa membuat Surat Jalan & Packing List berdasarkan Sales Order, memilih item & qty yang benar-benar dikirim (mendukung pengiriman parsial).
- Stok varian produk otomatis berkurang saat Surat Jalan dibuat, tercatat di `stock_movements` seperti pola existing.
- Setelah barang selesai dikirim (semua atau sebagian), admin/sales bisa menerbitkan Invoice final berdasarkan Surat Jalan yang sudah terkirim.
- Admin/finance bisa mencatat pembayaran (nominal, tanggal, catatan, bukti opsional) terhadap Proforma Invoice maupun Invoice B2B, termasuk pembayaran bertahap/cicilan, dan sistem menghitung sisa piutang otomatis.
- Invoice B2B punya tanggal jatuh tempo (`due_date`) sehingga admin/owner bisa memantau piutang yang sudah lewat jatuh tempo.
- Setiap dokumen bisa dilihat riwayat statusnya dan dicetak/diunduh dalam format yang layak dibagikan ke customer atau dibawa kurir/gudang.
- Sistem menyediakan penomoran dokumen yang konsisten dan unik per jenis dokumen.

## Company-Aware (Fase 3 dari Fondasi Multi-Perusahaan)

Seluruh entitas baru di PRD ini **wajib ber-`company_id`**, mengikuti pola yang sudah dipakai `products`, `coupons`, `store_locations`, `transactions` di Fase 1 & 2:

- Tabel yang mendapat kolom `company_id` (FK `companies`, NOT NULL): `quotations`, `sales_orders`, `proforma_invoices`, `delivery_notes`, `packing_lists`, `b2b_invoices`. Tabel detail/histori (`quotation_details`, `sales_order_details`, dst.) tidak perlu `company_id` sendiri — ikut induknya.
- `document_payments` juga tidak perlu `company_id` sendiri — scoping-nya ikut `payable` (`ProformaInvoice`/`B2bInvoice`) yang dituju.
- **Satu Quotation hanya boleh berisi produk dari satu perusahaan** (konsisten dengan pola `hasMixedCompanyItems` yang sudah dipakai di checkout retail). Saat admin menambah item ke Quotation, item pertama menentukan `company_id` Quotation; item berikutnya divalidasi harus dari perusahaan yang sama. Sales Order/Proforma Invoice/Surat Jalan/Invoice B2B mewarisi `company_id` dari Quotation/Sales Order asalnya (tidak perlu dipilih ulang).
- Semua controller list/detail/create menerapkan `ScopesToActiveCompany` (trait yang sama dipakai `ProductController`, dkk.) — admin hanya melihat & bisa membuat dokumen untuk perusahaan yang sedang aktif di company switcher. Akses ke dokumen milik perusahaan lain via URL langsung → 404 (`guardCompanyOwnership`), sama seperti produk/kupon.
- **Penomoran dokumen memakai `company.invoice_prefix`** (kolom yang sudah ada sejak Fase 1 tapi belum pernah dipakai): `{invoice_prefix}-QUO-{YmdHis}-{seq}`, `{invoice_prefix}-SO-...`, `{invoice_prefix}-PI-...`, `{invoice_prefix}-SJ-...`, `{invoice_prefix}-PL-...`, `{invoice_prefix}-INVB-...`. Sequence 4-digit di-generate lewat `DocumentNumberGenerator` (service baru, lihat §7 Scope Functional) yang menghitung urutan **per hari per perusahaan** (bukan global lintas perusahaan).
  - **Sekalian retrofit**: `Transaction::invoice_no` (retail/checkout) yang saat ini hardcode `INV-{YmdHis}-{seq}` di 4 tempat berbeda (`AdminManualTransactionController`, `CartController`, `ManualPaymentController`, `MidtransController`) turut dipindah ke `DocumentNumberGenerator` dengan format `{invoice_prefix}-INV-{YmdHis}-{seq}`. Berlaku untuk transaksi baru saja — `invoice_no` yang sudah terbit sebelumnya tidak diubah retroaktif.
- **Role "Staff Gudang"** memakai mekanisme `admin_company_assignments` yang sudah ada apa adanya (tidak perlu logic baru): saat admin meng-assign role ini ke seorang staff, admin memilih `company_id` tertentu (staff hanya lihat Sales Order/Surat Jalan/Packing List perusahaan itu) atau membiarkan `company_id` kosong/NULL (akses ke semua perusahaan) — persis pola assignment role lain yang sudah berjalan.
- **Piutang (Invoice B2B overdue/outstanding)** ditampilkan per-perusahaan aktif (ikut company switcher), konsisten dengan keputusan "Reports di-skip dulu" pada Fase 2. Dashboard piutang konsolidasi lintas-perusahaan ditunda ke fase berikutnya bersamaan dengan `reports.consolidated`.
- Baru setelah seluruh alur ini diuji dengan 1 perusahaan (perilaku harus identik dengan asumsi single-company), perusahaan kedua diaktifkan untuk transaksi B2B riil — mengikuti pola rollout yang sama dengan Fase 1 & 2.

## Keputusan Terkonfirmasi (2026-07-20)

- **Pembatalan Invoice B2B/Proforma Invoice yang sudah ada Pembayaran tercatat: diblokir total.** Admin harus menghapus/mengoreksi baris Pembayaran (fitur koreksi sudah ada di Acceptance Criteria) sebelum bisa membatalkan dokumen. Tombol batal disembunyikan/ditolak selama masih ada Pembayaran aktif.
- **Quotation yang sudah `expired`: tidak bisa di-extend.** Bersifat read-only permanen (konsisten dengan aturan `closed`/`rejected`/`expired` yang sudah ada). Admin membuat Quotation baru (bisa duplikasi item dari Quotation lama) jika customer baru konfirmasi setelah lewat `valid_until`.
- **Sales Order tidak ikut batal otomatis saat Quotation asalnya ditutup/expired.** Sales Order yang sudah dibuat adalah snapshot komitmen order yang berdiri sendiri, independen dari status Quotation asal.
- **Tidak ada pembatasan jumlah Quotation aktif per customer** — satu customer boleh punya banyak Quotation aktif paralel (mis. nego beberapa proyek berbeda bersamaan).
- **Sales Order dibatalkan padahal ada Proforma Invoice `issued`/`paid` terkait: Proforma Invoice tidak ikut otomatis dibatalkan.** Tetap tercatat sebagai piutang/DP yang sudah diterima; jika ada uang yang sudah masuk, direkonsiliasi/refund manual di luar sistem. Konsisten dengan aturan "dokumen yang sudah ada Pembayaran tidak boleh hilang begitu saja".

## Non-Tujuan

- Tidak mengubah atau menggantikan `Transaction`, `TransactionDetail`, alur checkout online, maupun alur transaksi manual/POS yang sudah ada.
- Tidak membangun approval customer self-service (link publik atau login customer) pada fase pertama — persetujuan Quotation dilakukan manual oleh admin berdasarkan konfirmasi customer di luar sistem.
- Tidak membangun integrasi akuntansi/pajak resmi (mis. e-Faktur) pada fase pertama — Invoice B2B ini adalah dokumen internal, bukan faktur pajak resmi (berbeda dari fitur `transaction_tax_invoices` yang sudah ada).
- Tidak membangun sistem retur/komplain untuk alur B2B ini pada fase pertama.
- Tidak membangun pelacakan pembayaran granular (cicilan/termin) di luar status dasar Proforma Invoice/Invoice pada fase pertama.
- Tidak membangun unit of measure (UOM) baru seperti pcs/box/kg secara ekstensif pada fase pertama — asumsi satuan tetap generik seperti pola produk yang sudah ada.
- Tidak mewajibkan Proforma Invoice pada setiap Sales Order — penerbitannya murni opsional berdasarkan kebutuhan customer.

## Definisi

### Quotation

Dokumen penawaran harga ke customer B2B, dibuat oleh admin/sales. Berisi daftar produk/varian, qty, dan harga yang bisa dinegosiasikan (berbeda dari harga katalog). Punya masa berlaku dan status persetujuan. Berfungsi seperti "kontrak harga": setelah disetujui, qty per item pada Quotation bisa ditarik (di-convert) menjadi Sales Order secara bertahap dalam beberapa kali transaksi, tidak harus sekali habis.

### Sales Order

Dokumen order internal yang mengonfirmasi komitmen pembelian customer atas sebagian/seluruh qty pada sebuah Quotation. Sales Order adalah *master record* yang menggerakkan fulfillment — Surat Jalan dan Packing List selalu diturunkan dari Sales Order, bukan dari Quotation atau Proforma Invoice secara langsung. Satu Quotation bisa menghasilkan banyak Sales Order (pembelian dicicil).

### Proforma Invoice

Dokumen tagihan sementara yang bersifat **opsional**, diterbitkan dari sebuah Sales Order ketika customer memerlukan dokumen resmi untuk membayar DP/pembayaran di muka sebelum barang dikirim. Tidak memengaruhi maupun menggerakkan proses fulfillment — murni dokumen penagihan pendukung. Sales Order yang tidak butuh DP dapat langsung lanjut ke Surat Jalan tanpa melalui Proforma Invoice sama sekali. Merepresentasikan piutang DP yang dilunasi lewat satu atau lebih catatan Pembayaran.

### Pembayaran (Payment)

Catatan pelunasan piutang yang dikaitkan ke Proforma Invoice atau Invoice B2B (satu dokumen bisa punya banyak catatan pembayaran untuk pelunasan bertahap/cicilan). Berisi nominal, tanggal bayar, catatan, dan bukti pembayaran opsional. Dicatat oleh admin/finance berdasarkan konfirmasi pembayaran dari customer di luar sistem, mirip pola `ManualPaymentController` yang sudah ada untuk `Transaction` retail, tapi tanpa kewajiban upload bukti.

### Surat Jalan

Dokumen bukti pengiriman barang dari gudang ke customer, dibuat oleh Staff Gudang berdasarkan Sales Order. Satu Sales Order bisa punya banyak Surat Jalan (pengiriman bertahap). Pembuatan Surat Jalan memicu pemotongan stok.

### Packing List

Dokumen rincian isi kemasan/paket untuk satu pengiriman, dibuat berpasangan dengan Surat Jalan (1:1 per pengiriman), menampilkan rincian qty per item dan opsional berat/dimensi total untuk kebutuhan gudang/ekspedisi.

### Invoice (B2B)

Dokumen tagihan final/resmi secara internal untuk satu atau lebih Surat Jalan yang sudah terkirim dari satu Sales Order. Merepresentasikan piutang (receivable) ke customer, punya tanggal jatuh tempo, dan dilunasi lewat satu atau lebih catatan Pembayaran (termasuk kredit otomatis dari DP yang sudah dibayar di Proforma Invoice terkait, jika ada). Entitas ini terpisah dari `Transaction`/invoice retail yang sudah ada di sistem.

## Alur Dokumen (Ringkasan)

```text
Quotation (draft/sent/accepted/rejected/expired/partially_converted/closed)
   |
   | convert (status accepted/partially_converted, belum expired/closed, bisa berkali-kali per sisa qty)
   v
Sales Order (draft/confirmed/partially_fulfilled/fulfilled/cancelled/closed)  x N per Quotation
   |
   |-- (opsional, hanya jika customer perlu DP) issue
   |      v
   |   Proforma Invoice (draft/issued/partially_paid/paid/cancelled)  -- piutang DP,
   |      |                                                              tidak menggerakkan fulfillment/stok
   |      | catat Pembayaran (nominal/tanggal/catatan/bukti opsional), bisa berkali-kali
   |      v
   |   Pembayaran DP (ledger, N per Proforma Invoice)
   |
   | convert (bisa berkali-kali, per pengiriman -- tidak menunggu Proforma Invoice)
   v
Surat Jalan (draft/shipped/delivered/cancelled) -- memotong stok saat dibuat
   |
   | dibuat berpasangan 1:1
   v
Packing List (mengikuti status Surat Jalan pasangannya)
   |
   | convert (satu atau gabungan beberapa Surat Jalan per Sales Order)
   | -- DP dari Proforma Invoice (jika ada & belum terpakai) otomatis jadi kredit awal
   v
Invoice B2B (draft/issued/partially_paid/paid/cancelled, due_date untuk piutang jatuh tempo)
   |
   | catat Pembayaran (nominal/tanggal/catatan/bukti opsional), bisa berkali-kali
   v
Pembayaran (ledger, N per Invoice B2B)
```

Catatan alur:

- Quotation -> Sales Order: **1:N** (satu Quotation yang sama bisa ditarik berkali-kali menjadi beberapa Sales Order terpisah, masing-masing menarik sebagian qty per item, selama sisa qty & masa berlaku masih ada). Jika perlu revisi harga/item besar, tetap buat Quotation baru.
- Sales Order -> Proforma Invoice: **0..N, opsional**. Sales Order tidak wajib punya Proforma Invoice. Jika diterbitkan, Proforma Invoice hanya berfungsi sebagai dokumen tagihan/DP, tidak menjadi prasyarat untuk membuat Surat Jalan — tidak ada flag "DP wajib" pada fase pertama (lihat Rekomendasi MVP).
- Sales Order -> Surat Jalan: 1:N (pengiriman bertahap), dengan validasi qty per item tidak boleh melebihi sisa qty yang belum dikirim di Sales Order.
- Surat Jalan -> Packing List: 1:1, dibuat dalam satu aksi yang sama.
- Sales Order -> Invoice B2B: 1:N (via Surat Jalan), tetapi setiap Invoice hanya boleh mencakup Surat Jalan yang sudah berstatus terkirim dan belum pernah ditagih di Invoice lain.
- Proforma Invoice -> Pembayaran: 1:N (pelunasan DP bisa dicicil beberapa kali pembayaran).
- Invoice B2B -> Pembayaran: 1:N (pelunasan piutang bisa dicicil beberapa kali pembayaran), termasuk baris kredit otomatis dari DP Proforma Invoice jika ada.

## User Stories

### Admin / Sales

- Sebagai admin/sales, saya bisa membuat Quotation untuk customer, memilih produk/varian, menentukan qty, dan mengubah harga per item secara manual.
- Sebagai admin/sales, saya bisa menentukan tanggal berlaku Quotation.
- Sebagai admin/sales, saya bisa mengubah status Quotation (sent/accepted/rejected) berdasarkan respons customer di luar sistem.
- Sebagai admin/sales, saya bisa mengonversi Quotation yang accepted dan belum kedaluwarsa menjadi Sales Order.
- Sebagai admin/sales, saya bisa menerbitkan Proforma Invoice dari Sales Order jika customer memerlukan tagihan DP, dan melewatinya jika tidak diperlukan.
- Sebagai admin/sales, saya bisa melihat ringkasan progres pengiriman pada satu Sales Order (berapa qty sudah dikirim, berapa sisa).
- Sebagai admin/sales, saya bisa menerbitkan Invoice dari satu atau lebih Surat Jalan yang sudah terkirim.
- Sebagai admin/sales, saya bisa mencetak/mengunduh Quotation, Sales Order, Proforma Invoice, dan Invoice untuk dibagikan ke customer.
- Sebagai admin/sales, saya bisa melihat histori status setiap dokumen.

### Staff Gudang

- Sebagai staff gudang, saya bisa melihat daftar Sales Order yang siap dikirim (belum terpenuhi penuh), tanpa perlu tahu status Proforma Invoice-nya.
- Sebagai staff gudang, saya bisa membuat Surat Jalan dari sebuah Sales Order, memilih item & qty yang akan dikirim saat itu (bisa sebagian).
- Sebagai staff gudang, saya bisa melihat Packing List yang dihasilkan otomatis bersama Surat Jalan.
- Sebagai staff gudang, saya bisa mencetak Surat Jalan dan Packing List untuk dibawa kurir/ekspedisi.
- Sebagai staff gudang, saya diberi peringatan jika stok tidak mencukupi saat membuat Surat Jalan.
- Sebagai staff gudang, saya tidak bisa mengubah harga atau data komersial dokumen (hanya akses ke Surat Jalan & Packing List).

### Owner / Supervisor

- Sebagai owner, saya bisa memantau Quotation yang akan kedaluwarsa.
- Sebagai owner, saya bisa memantau Sales Order yang belum terkirim penuh dalam jangka waktu lama.
- Sebagai owner, saya bisa melihat Invoice yang sudah diterbitkan per periode.
- Sebagai owner, saya bisa memastikan staff gudang tidak bisa mengakses/mengubah harga.

## Scope Functional

### 1. Quotation

Field:

- Nomor Quotation (`quotation_no`, auto-generate).
- Customer: user terdaftar (`user_id`) atau input manual (`manual_customer_name`, `manual_customer_phone`, `manual_customer_email`).
- Daftar item: produk/varian, qty, harga katalog (`original_price`, referensi), harga final (`price`, bisa diedit manual), catatan per item, serta qty yang sudah ditarik ke Sales Order (`quantity_converted`, kolom turunan).
- `valid_until` (tanggal kedaluwarsa).
- Catatan umum, syarat & ketentuan (opsional, teks bebas).
- Status: `draft`, `sent`, `accepted`, `partially_converted`, `rejected`, `expired`, `closed`.
- Dibuat oleh admin (`created_by_admin_id`).

Behavior:

- Total dihitung otomatis dari item (subtotal, diskon jika ada, grand total), mengikuti pola perhitungan `Transaction`.
- Harga per item bisa diedit manual oleh admin (tidak wajib sama dengan harga produk saat itu).
- Perubahan status dicatat ke `quotation_status_histories`.
- Status berubah otomatis menjadi `expired` melalui scheduled job jika `valid_until` terlewati dan status masih `draft`/`sent`/`accepted`/`partially_converted` (belum semua qty terpakai/`closed`).
- Status berubah otomatis menjadi `partially_converted` setelah Sales Order pertama dibuat dari Quotation ini, selama masih ada sisa qty pada minimal satu item.
- Status berubah otomatis menjadi `closed` ketika seluruh qty di semua item sudah habis ditarik ke Sales Order (tidak ada sisa).
- Admin dapat menutup Quotation secara manual (`closed`) kapan saja setelah minimal satu Sales Order dibuat, untuk menandai sisa qty yang belum terpakai tidak akan dibeli customer (mis. rencana beli 2 pcs, realisasi hanya 1 pcs).
- Quotation yang `closed`, `rejected`, atau `expired` tidak bisa diedit lagi dan tidak bisa dipakai untuk convert baru (read-only, hanya referensi histori). Sales Order yang sudah terlanjur dibuat sebelumnya tidak ikut batal ketika Quotation asalnya `expired`/`closed`.
- Tombol "Convert to Sales Order" aktif selama status `accepted`/`partially_converted`, belum melewati `valid_until`, dan masih ada sisa qty pada minimal satu item. Admin memilih item & qty (bisa sebagian dari sisa qty) setiap kali convert.

### 2. Sales Order

Field:

- Nomor Sales Order (`sales_order_no`, auto-generate).
- Referensi ke Quotation asal (`quotation_id`).
- Snapshot data customer, item, qty, harga dari Quotation saat convert.
- Status: `draft`, `confirmed`, `partially_fulfilled`, `fulfilled`, `cancelled`.
- Kolom qty terkirim per item (turunan dari Surat Jalan terkait, untuk tracking sisa qty).

Behavior:

- Convert dari Quotation menyalin item & qty yang dipilih admin saat itu (bisa sebagian dari sisa qty Quotation), dengan harga hasil nego tetap dipakai apa adanya (tidak di-refresh ke harga katalog terbaru).
- Setiap convert memvalidasi qty per item terhadap sisa qty Quotation (`quantity` dikurangi `quantity_converted` berjalan), dengan row lock untuk mencegah race condition jika ada dua Sales Order dibuat bersamaan dari Quotation yang sama.
- Sales Order berstatus `confirmed` begitu dibuat (dianggap komitmen order yang sah), siap dijadikan acuan Surat Jalan kapan saja tanpa menunggu dokumen lain.
- Status berubah otomatis menjadi `partially_fulfilled` ketika ada Surat Jalan pertama dibuat, dan `fulfilled` ketika seluruh qty di semua item sudah tercakup oleh Surat Jalan yang tidak dibatalkan.
- Admin bisa membatalkan Sales Order selama belum ada Surat Jalan aktif terkait (status `draft`/`confirmed` tanpa Surat Jalan). Qty yang dibatalkan dikembalikan sebagai sisa qty yang bisa ditarik ulang dari Quotation asal.
- Sales Order dapat memiliki nol, satu, atau lebih Proforma Invoice terkait — keberadaan/status Proforma Invoice tidak memengaruhi kemampuan Sales Order untuk lanjut ke Surat Jalan pada fase pertama (tidak ada flag "DP wajib", lihat Rekomendasi MVP).

### 3. Proforma Invoice (Opsional)

Field:

- Nomor Proforma Invoice (`proforma_invoice_no`, auto-generate).
- Referensi ke Sales Order asal (`sales_order_id`).
- Snapshot data customer, item/qty yang ditagihkan (bisa mencakup seluruh atau sebagian item Sales Order, sesuai kebutuhan penagihan DP), harga.
- `grand_total` (nominal yang ditagihkan), `paid_amount` (turunan, jumlah seluruh Pembayaran terkait), `outstanding_amount` (turunan, `grand_total` - `paid_amount`).
- Status: `draft`, `issued`, `partially_paid`, `paid`, `cancelled`.

Behavior:

- Admin menerbitkan Proforma Invoice dari halaman detail Sales Order, hanya jika diperlukan (tombol opsional, tidak wajib diklik).
- Proforma Invoice murni dokumen penagihan — tidak memiliki relasi langsung ke Surat Jalan/Packing List dan tidak ikut serta dalam perhitungan sisa qty fulfillment Sales Order.
- Status berubah otomatis menjadi `partially_paid` saat ada Pembayaran pertama tercatat dengan total belum menutupi `grand_total`, dan `paid` saat total Pembayaran sudah menutupi/melebihi `grand_total`.
- Satu Sales Order bisa punya lebih dari satu Proforma Invoice jika DP diminta bertahap (mis. termin pembayaran), tapi **dibatasi jadi satu Proforma Invoice per Sales Order pada MVP** untuk menyederhanakan (lihat Rekomendasi MVP). DP bertahap/termin ditunda ke fase berikutnya.

### 4. Surat Jalan & Packing List

Field Surat Jalan:

- Nomor Surat Jalan (`delivery_note_no`, auto-generate).
- Referensi ke Sales Order (`sales_order_id`).
- Item yang dikirim beserta qty (bisa subset dari item Sales Order, qty <= sisa qty belum terkirim).
- Data pengiriman: nama penerima, alamat tujuan, kurir/ekspedisi (opsional), catatan.
- Status: `draft`, `shipped`, `delivered`, `cancelled`.
- Dibuat oleh staff gudang (`created_by_user_id`).

Field Packing List (1:1 dengan Surat Jalan):

- Nomor Packing List (`packing_list_no`, auto-generate lewat `DocumentNumberGenerator` dengan prefix `PL-` sendiri, bukan reuse nomor Surat Jalan — default sederhana, konsisten dengan dokumen lain).
- Rincian item & qty (sama dengan Surat Jalan pasangannya).
- Ringkasan total berat/dimensi (dihitung dari `weight_grams`/`length_cm`/`width_cm`/`height_cm` per varian x qty, kolom yang sudah tersedia di `product_variants`).
- Jumlah koli/paket (opsional, input manual staff gudang).

Behavior:

- Dibuat dalam satu aksi ("Buat Pengiriman" dari halaman detail Sales Order), menghasilkan Surat Jalan + Packing List sekaligus. Staff gudang tidak perlu membuka/mengetahui Proforma Invoice sama sekali.
- Validasi qty per item terhadap sisa qty Sales Order yang belum dikirim (mencegah over-shipping).
- Saat Surat Jalan disimpan dengan status `shipped`, sistem memotong stok varian terkait dalam `DB::transaction()` + `lockForUpdate()`, mengikuti pola `TransactionController::process()` yang sudah ada, dan mencatat ke `stock_movements` dengan `source` baru (mis. `delivery_note`).
- Jika stok tidak cukup saat submit, transaksi ditolak dan staff gudang diminta menyesuaikan qty.
- Status `delivered` diubah manual oleh staff gudang/admin setelah barang sampai (konfirmasi non-otomatis pada fase pertama).
- Pembatalan Surat Jalan (`cancelled`) hanya bisa dilakukan selama masih berstatus `draft` (belum `shipped`), tidak memerlukan pengembalian stok karena belum dipotong. **Setelah `shipped`, tidak ada aksi pembatalan tersedia pada fase pertama** — alur retur/pengembalian stok pasca-`shipped` ditunda ke fase berikutnya (lihat Rekomendasi MVP).

### 5. Invoice B2B

Field:

- Nomor Invoice (`b2b_invoice_no`, auto-generate, terpisah dari `transactions.invoice_no`).
- Referensi ke Sales Order (`sales_order_id`) dan ke satu atau lebih Surat Jalan (`b2b_invoice_delivery_note` pivot).
- Snapshot item gabungan dari Surat Jalan terkait (bisa berbeda granularitas dari Sales Order asal jika dipecah).
- `due_date` (tanggal jatuh tempo, term pembayaran seperti NET 30/60, diisi admin saat terbit).
- `grand_total`, `paid_amount` (turunan, jumlah seluruh Pembayaran + kredit DP terkait), `outstanding_amount` (turunan).
- Status: `draft`, `issued`, `partially_paid`, `paid`, `cancelled`.

Behavior:

- Admin memilih satu Sales Order, lalu memilih Surat Jalan berstatus `shipped`/`delivered` yang belum pernah ditagih di Invoice lain, untuk digabung menjadi satu Invoice.
- Satu Surat Jalan hanya boleh masuk ke satu Invoice (tidak boleh dobel tagih).
- Jika Sales Order asal punya Proforma Invoice berstatus `partially_paid`/`paid` yang DP-nya belum pernah dikreditkan ke Invoice B2B manapun, sistem otomatis membuat baris Pembayaran kredit (mis. `source = dp_credit`) sebesar total Pembayaran DP tersebut saat Invoice B2B pertama dari Sales Order itu diterbitkan, mengurangi `outstanding_amount` sejak awal.
- Setelah semua Surat Jalan pada satu Sales Order sudah ter-invoice, Sales Order tersebut dianggap selesai secara penagihan (indikator terpisah dari status `fulfilled` pengiriman).
- Invoice dianggap jatuh tempo (`overdue`, indikator tampilan, bukan status tersimpan) ketika `due_date` terlewati dan status masih `issued`/`partially_paid`.

### 6. Pembayaran (Payment Ledger)

Field:

- `payable_type` / `payable_id` (polymorphic — menunjuk ke `ProformaInvoice` atau `B2bInvoice`).
- `amount`, `payment_date`.
- `note` (opsional).
- `proof_path` (opsional, upload bukti transfer/giro/cek — tidak wajib).
- `source` (`manual` untuk input admin biasa, `dp_credit` untuk baris kredit otomatis dari DP Proforma Invoice ke Invoice B2B).
- `recorded_by_admin_id`, `created_at`.

Behavior:

- Admin/finance mencatat Pembayaran dari halaman detail Proforma Invoice atau Invoice B2B — input nominal, tanggal, catatan, dan opsional upload bukti.
- Satu dokumen (Proforma Invoice/Invoice B2B) bisa punya banyak baris Pembayaran (pelunasan bertahap/cicilan).
- Setiap kali Pembayaran dicatat/dihapus, `paid_amount`/`outstanding_amount` dan status dokumen terkait dihitung ulang dalam `DB::transaction()` + `lockForUpdate()` untuk mencegah race condition.
- Baris Pembayaran dengan `source = dp_credit` dibuat otomatis oleh sistem (bukan input manual admin) saat Invoice B2B pertama dari sebuah Sales Order diterbitkan dan Sales Order tersebut punya DP yang sudah dibayar.
- Total nominal Pembayaran tidak boleh melebihi `outstanding_amount` dokumen terkait pada satu waktu pencatatan (mencegah kelebihan input pelunasan).

### 7. Penomoran Dokumen

Format: `{company.invoice_prefix}-{jenis}-{YmdHis}-{sequence 4 digit}`, mis. `BOQ-QUO-20260720-0001`, `PTDUA-INVB-20260720-0003`.

- Quotation: `QUO-...`
- Sales Order: `SO-...`
- Proforma Invoice: `PI-...`
- Surat Jalan: `SJ-...`
- Packing List: `PL-...`
- Invoice B2B: `INVB-...` (dibedakan dari `INV-...` milik `Transaction` retail agar tidak rancu)
- Transaction retail (retrofit): `INV-...` tetap, hanya ditambah prefix perusahaan di depan.

Rekomendasi teknis: penomoran invoice existing sudah diduplikasi identik di 4 controller (`AdminManualTransactionController`, `CartController`, `ManualPaymentController`, `MidtransController`) dan tidak per-perusahaan. Fase ini menarik logic penomoran ke satu service `DocumentNumberGenerator` yang dipakai bersama oleh dokumen lama (`Transaction::invoice_no`) dan seluruh dokumen baru, dengan sequence dihitung **per hari per `company_id`** (bukan global), agar tidak menambah duplikasi keempat/kelima/keenam sekaligus menutup celah `invoice_prefix` yang selama ini tidak terpakai.

### 8. Cetak / Ekspor Dokumen

- Fase pertama mengikuti pola existing (`InvoiceController` + `resources/views/invoices/print.blade.php`): halaman cetak berbasis HTML + `window.print()`, tanpa generator PDF server-side.
- Setiap dokumen (Quotation, Sales Order, Proforma Invoice, Surat Jalan, Packing List, Invoice B2B) punya halaman cetak sendiri dengan layout sesuai kebutuhan (Surat Jalan & Packing List perlu kolom tanda tangan penerima/pengirim).
- Kebutuhan PDF server-side asli (misal untuk lampiran email otomatis) dicatat sebagai Open Question, karena butuh tambahan dependency (`barryvdh/laravel-dompdf`) yang belum terpasang.

## Data Requirements

### Tabel `quotations`

- `id`
- `company_id` (FK `companies`, NOT NULL — lihat §Company-Aware)
- `quotation_no`
- `user_id` (nullable)
- `manual_customer_name`, `manual_customer_phone`, `manual_customer_email`
- `status`
- `subtotal_amount`, `discount_amount`, `grand_total`
- `valid_until`
- `note`
- `created_by_admin_id`
- `closed_at`, `closed_by_admin_id`, `close_reason` (nullable, untuk penutupan manual)
- `created_at`, `updated_at`

### Tabel `quotation_details`

- `id`, `quotation_id`
- `product_id`, `product_variant_id`
- `product_name`, `variant_name`, `sku`, `image` (snapshot)
- `original_price`, `price`, `quantity`, `subtotal`, `item_note`
- `quantity_converted` (kolom turunan/terhitung dari total qty yang sudah ditarik ke seluruh Sales Order terkait, atau dihitung on-the-fly dari `sales_order_details`)

### Tabel `quotation_status_histories`

- `id`, `quotation_id`, `user_id`, `from_status`, `to_status`, `note`, `created_at`

### Tabel `sales_orders`

- `id`, `company_id` (FK `companies`, NOT NULL, diwarisi dari `quotation.company_id` saat convert), `sales_order_no`, `quotation_id` (banyak Sales Order bisa menunjuk ke Quotation yang sama)
- Snapshot customer (sama pola dengan `quotations`)
- `status`
- `subtotal_amount`, `discount_amount`, `grand_total`
- `created_by_admin_id`, `created_at`, `updated_at`

### Tabel `sales_order_details`

- `id`, `sales_order_id`, `quotation_detail_id` (referensi baris Quotation asal, untuk hitung sisa qty)
- `product_id`, `product_variant_id`, `product_name`, `variant_name`, `sku`
- `price`, `quantity`, `quantity_shipped` (kolom turunan/terhitung, atau dihitung on-the-fly dari `delivery_note_details`)

### Tabel `sales_order_status_histories`

- `id`, `sales_order_id`, `user_id`, `from_status`, `to_status`, `note`, `created_at`

### Tabel `proforma_invoices`

- `id`, `company_id` (FK `companies`, NOT NULL, diwarisi dari `sales_order.company_id`), `proforma_invoice_no`, `sales_order_id`
- Snapshot customer (sama pola dengan `sales_orders`)
- `status`
- `subtotal_amount`, `grand_total` (nominal yang ditagihkan, bisa sebagian dari total Sales Order)
- `paid_amount`, `outstanding_amount` (turunan, dihitung ulang setiap ada Pembayaran)
- `issued_at`
- `created_by_admin_id`, `created_at`, `updated_at`

### Tabel `proforma_invoice_details`

- `id`, `proforma_invoice_id`, `sales_order_detail_id`
- `product_name`, `variant_name`, `sku`, `price`, `quantity`

### Tabel `delivery_notes` (Surat Jalan)

- `id`, `company_id` (FK `companies`, NOT NULL, diwarisi dari `sales_order.company_id`), `delivery_note_no`, `sales_order_id`
- `status`
- `recipient_name`, `shipping_address`, `courier_name`, `note`
- `created_by_user_id`, `shipped_at`, `delivered_at`, `created_at`, `updated_at`

### Tabel `delivery_note_details`

- `id`, `delivery_note_id`, `sales_order_detail_id`
- `product_variant_id`, `product_name`, `variant_name`, `sku`
- `quantity`

### Tabel `packing_lists`

- `id`, `company_id` (FK `companies`, NOT NULL, diwarisi dari `delivery_note.company_id`), `packing_list_no`, `delivery_note_id` (unique, 1:1)
- `total_weight_grams`, `total_packages` (opsional input manual)
- `created_at`, `updated_at`

### Tabel `b2b_invoices`

- `id`, `company_id` (FK `companies`, NOT NULL, diwarisi dari `sales_order.company_id`), `b2b_invoice_no`, `sales_order_id`
- `status`
- `subtotal_amount`, `grand_total`
- `paid_amount`, `outstanding_amount` (turunan, dihitung ulang setiap ada Pembayaran)
- `due_date`, `issued_at`
- `created_by_admin_id`, `created_at`, `updated_at`

### Tabel pivot `b2b_invoice_delivery_note`

- `id`, `b2b_invoice_id`, `delivery_note_id`

### Tabel `document_payments` (ledger Pembayaran, polymorphic)

- `id`
- `payable_type`, `payable_id` (polymorphic, menunjuk ke `ProformaInvoice` atau `B2bInvoice`)
- `amount`, `payment_date`
- `note` (nullable)
- `proof_path` (nullable, opsional)
- `source` (`manual`/`dp_credit`)
- `recorded_by_admin_id`, `created_at`, `updated_at`

Relasi:

- `Quotation hasMany QuotationDetail`, `Quotation hasMany QuotationStatusHistory`, `Quotation hasMany SalesOrder`.
- `SalesOrder belongsTo Quotation`, `SalesOrder hasMany SalesOrderDetail`, `SalesOrder hasMany SalesOrderStatusHistory`, `SalesOrder hasMany ProformaInvoice`, `SalesOrder hasMany DeliveryNote`.
- `ProformaInvoice belongsTo SalesOrder`, `ProformaInvoice hasMany ProformaInvoiceDetail`, `ProformaInvoice morphMany DocumentPayment`.
- `DeliveryNote belongsTo SalesOrder`, `DeliveryNote hasMany DeliveryNoteDetail`, `DeliveryNote hasOne PackingList`.
- `B2bInvoice belongsTo SalesOrder`, `B2bInvoice belongsToMany DeliveryNote` (via pivot), `B2bInvoice morphMany DocumentPayment`.

Alasan struktur terpisah dari `Transaction`:

- Model bisnis B2B (nego harga, order internal, DP opsional, pengiriman bertahap, penagihan gabungan) cukup berbeda dari alur retail/checkout sehingga memaksakan reuse `Transaction` akan menambah banyak kolom kondisional yang tidak relevan untuk retail.
- Menjaga `Transaction` tetap stabil untuk alur retail yang sudah berjalan dan teruji.

Alasan Sales Order dipisah dari Proforma Invoice (bukan digabung jadi satu dokumen):

- Sales Order adalah dokumen **internal** yang selalu ada dan menggerakkan fulfillment; Proforma Invoice adalah dokumen **customer-facing** yang sifatnya opsional dan tidak semua customer membutuhkannya.
- Memisahkan keduanya membuat Surat Jalan bisa langsung dibuat dari Sales Order tanpa terhalang status Proforma Invoice untuk customer yang tidak butuh DP, sekaligus tetap menyediakan jalur penagihan DP formal untuk customer yang membutuhkannya.

## Permissions

Module baru di `config/admin_permissions.php`, mengikuti pola existing (`{module}.{action}`):

- `quotations`: `index`, `create`, `show`, `edit`, `send` (ubah status ke sent), `convert` (convert ke sales order), `close` (tutup manual sisa qty)
- `sales_orders`: `index`, `show`, `cancel`
- `proforma_invoices`: `index`, `create`, `show`, `cancel`, `record_payment` (catat pembayaran DP)
- `delivery_notes`: `index`, `create`, `show`, `process` (ubah status shipped/delivered)
- `packing_lists`: `index`, `show`
- `b2b_invoices`: `index`, `create`, `show`, `cancel`, `record_payment` (catat pembayaran piutang)

Kebutuhan role baru:

- Sistem role saat ini (`admin_roles`) belum punya role bawaan "Staff Gudang" — perlu dibuat role baru dengan permission terbatas hanya ke `sales_orders.index`/`sales_orders.show` (read-only, untuk lihat apa yang perlu dikirim), `delivery_notes.*`, dan `packing_lists.*` (plus `stock.index` yang sudah ada untuk melihat stok), tanpa akses ke `quotations`/`proforma_invoices`/`b2b_invoices` yang memuat data harga.
- Role "Staff Gudang" di-assign lewat `admin_company_assignments` yang sudah ada apa adanya — admin memilih `company_id` spesifik (staff hanya lihat Sales Order/Surat Jalan/Packing List perusahaan itu) atau kosongkan `company_id` (akses semua perusahaan), tidak perlu mekanisme assignment baru.
- Role "Admin/Sales" bisa memakai role existing seperti "Store Manager" (full access) atau role baru khusus sales dengan permission `quotations.*`, `sales_orders.*`, `proforma_invoices.*`, `b2b_invoices.*`, di-assign per perusahaan dengan cara yang sama.

## UI Requirements

### Admin / Sales

- Halaman list Quotation dengan filter status, tanggal, keyword customer.
- Form create/edit Quotation: pilih produk/varian (reuse komponen search produk yang sudah ada di `AdminManualTransactionController`), input qty & harga custom per baris.
- Halaman detail Quotation dengan tombol ubah status dan tombol convert (muncul kondisional sesuai status).
- Halaman detail Sales Order menampilkan progres pengiriman per item (qty dipesan vs qty terkirim), daftar Surat Jalan terkait, dan tombol opsional "Terbitkan Proforma Invoice" jika customer memerlukan DP.
- Halaman detail Proforma Invoice & Invoice B2B menampilkan ringkasan piutang (`grand_total`, `paid_amount`, `outstanding_amount`), daftar riwayat Pembayaran, dan form "Catat Pembayaran" (nominal, tanggal, catatan, upload bukti opsional).
- Halaman list Invoice B2B menampilkan indikator piutang jatuh tempo (`overdue`) untuk invoice yang `due_date`-nya terlewati dan belum lunas.
- Halaman list & create Invoice B2B dengan pemilihan Surat Jalan yang belum ditagih.

### Staff Gudang

- Halaman list Sales Order yang masih punya sisa qty untuk dikirim (tanpa kolom harga/Proforma Invoice).
- Form buat Surat Jalan dari Sales Order: menampilkan sisa qty per item, input qty yang dikirim saat ini.
- Halaman cetak Surat Jalan + Packing List (layout terpisah dari halaman komersial, tanpa menampilkan harga).

### Umum

- Setiap halaman detail dokumen menampilkan histori status.
- Badge status dengan warna konsisten mengikuti pola badge status yang sudah dipakai di halaman transaksi.
- Pada halaman detail Sales Order, tampilkan indikator jelas apakah Proforma Invoice ada/tidak dan statusnya, tanpa membuatnya terlihat sebagai prasyarat wajib pengiriman.

## Validasi

- Quotation: item minimal 1, qty > 0, harga >= 0, `valid_until` wajib dan harus tanggal masa depan saat dibuat.
- Convert Quotation -> Sales Order: status harus `accepted`/`partially_converted`, `valid_until` belum terlewati, qty yang ditarik per item <= sisa qty (`quantity` - `quantity_converted`) pada Quotation.
- Tutup manual Quotation: hanya bisa dilakukan setelah minimal satu Sales Order pernah dibuat dari Quotation tersebut, dan status belum `closed`/`rejected`/`expired`.
- Terbitkan Proforma Invoice: Sales Order harus berstatus `confirmed`/`partially_fulfilled` (belum `cancelled`), item/qty yang ditagihkan tidak boleh melebihi qty pada Sales Order.
- Surat Jalan: qty per item wajib > 0 dan <= sisa qty belum terkirim pada Sales Order terkait; validasi ulang stok varian saat submit (row lock). Tidak ada validasi terhadap status Proforma Invoice pada fase pertama.
- Invoice B2B: Surat Jalan yang dipilih wajib berstatus `shipped`/`delivered` dan belum terkait Invoice B2B lain manapun.
- Pembayaran: `amount` wajib > 0 dan tidak boleh melebihi `outstanding_amount` dokumen terkait pada saat pencatatan (row lock saat hitung ulang); `payment_date` wajib diisi; `proof_path` opsional (tidak wajib).
- Batalkan Proforma Invoice/Invoice B2B: ditolak (403/422) jika dokumen tersebut masih memiliki minimal satu baris `document_payments` aktif (termasuk `dp_credit`). Admin harus menghapus/mengoreksi seluruh baris Pembayaran dokumen tsb terlebih dahulu.
- Batalkan Sales Order: ditolak jika masih ada Surat Jalan aktif terkait (status selain `cancelled`); tidak divalidasi terhadap status Proforma Invoice (PI yang sudah `issued`/`paid` tetap ada, tidak ikut dibatalkan — lihat Edge Cases).

## Acceptance Criteria

- Admin bisa membuat Quotation dengan harga custom per item dan tanggal kedaluwarsa.
- Quotation otomatis berstatus `expired` setelah `valid_until` terlewati tanpa seluruh qty terpakai.
- Admin bisa mengubah status Quotation dan hanya bisa convert saat `accepted`/`partially_converted` dan belum expired/closed.
- Admin bisa mengonversi satu Quotation menjadi lebih dari satu Sales Order secara bertahap (qty sebagian setiap kali), sepanjang sisa qty & masa berlaku masih ada.
- Setiap convert menghasilkan satu Sales Order dengan data tersalin (snapshot) sesuai item & qty yang dipilih dari Quotation.
- Admin bisa menutup manual sebuah Quotation yang masih ada sisa qty untuk menandai customer tidak melanjutkan pembelian sisanya.
- Sistem mencegah qty yang ditarik ke Sales Order melebihi sisa qty Quotation, termasuk saat dua permintaan convert terjadi bersamaan.
- Admin bisa menerbitkan Proforma Invoice dari Sales Order secara opsional, dan bisa melewatinya sepenuhnya jika customer tidak butuh DP.
- Staff gudang bisa membuat Surat Jalan langsung dari Sales Order (dengan atau tanpa Proforma Invoice) dengan qty sebagian (parsial), dan sistem mencegah qty melebihi sisa yang belum dikirim.
- Surat Jalan dan Packing List dibuat sekaligus dalam satu aksi.
- Stok varian berkurang otomatis saat Surat Jalan berstatus `shipped`, tercatat di `stock_movements`.
- Sales Order otomatis berubah status sesuai progres pengiriman (`partially_fulfilled`/`fulfilled`).
- Admin bisa membuat Invoice B2B dari satu atau lebih Surat Jalan yang sudah terkirim dan belum ditagih.
- Satu Surat Jalan tidak bisa masuk ke lebih dari satu Invoice B2B.
- Admin/finance bisa mencatat Pembayaran (nominal, tanggal, catatan, bukti opsional) terhadap Proforma Invoice maupun Invoice B2B, termasuk pelunasan bertahap/cicilan.
- Status Proforma Invoice/Invoice B2B otomatis berubah (`partially_paid`/`paid`) mengikuti total Pembayaran yang tercatat dibanding `grand_total`.
- Saat Invoice B2B pertama dari sebuah Sales Order diterbitkan, DP yang sudah dibayar di Proforma Invoice terkait (jika ada) otomatis jadi kredit pengurang `outstanding_amount` Invoice B2B tersebut.
- Invoice B2B yang `due_date`-nya terlewati dan belum lunas ditandai sebagai jatuh tempo (`overdue`) di tampilan admin.
- Setiap dokumen bisa dicetak dan menampilkan histori status.
- Staff gudang tidak bisa mengakses data harga/komersial (Quotation, Sales Order harga, Proforma Invoice, Invoice B2B).
- Fitur ini tidak mengubah data atau logic `Transaction`/`TransactionDetail` yang sudah ada.

## Edge Cases

- Quotation expired tapi customer baru konfirmasi setelah tanggal lewat — **wajib buat Quotation baru**, tidak bisa "extend" `valid_until` (lihat Keputusan Terkonfirmasi).
- Stok cukup saat Quotation/Sales Order dibuat tapi habis saat Surat Jalan dibuat (item lain terjual duluan) — Surat Jalan gagal disimpan, staff gudang harus koordinasi ulang dengan sales.
- Surat Jalan sudah `shipped` (stok terpotong) lalu perlu dibatalkan karena retur/kesalahan — **tidak didukung pada fase pertama** (tidak ada aksi pembatalan setelah `shipped`); alur retur ditunda ke fase berikutnya (lihat Rekomendasi MVP).
- Sales Order dibatalkan padahal sudah ada Surat Jalan aktif — harus dicegah di level validasi.
- Sales Order dibatalkan padahal sudah ada Proforma Invoice yang `issued`/`paid` — **Proforma Invoice tidak ikut dibatalkan otomatis**, tetap ada sebagai catatan DP yang perlu direfund manual di luar sistem (lihat Keputusan Terkonfirmasi).
- Invoice B2B dibuat dari Surat Jalan yang ternyata sebagian barangnya dikembalikan setelah `delivered`.
- Dua staff gudang membuat Surat Jalan bersamaan dari Sales Order yang sama untuk item yang sama (race condition qty sisa) — perlu row lock di level Sales Order/Sales Order Detail saat validasi sisa qty.
- Dua admin/sales membuat Sales Order bersamaan dari Quotation yang sama untuk item yang sama (race condition sisa qty Quotation) — perlu row lock di level Quotation/Quotation Detail saat validasi.
- Customer berubah pikiran soal harga setelah Sales Order dibuat — perlu Quotation baru (bukan edit Sales Order), karena Sales Order dianggap snapshot final dari negosiasi.
- Quotation qty 2 pcs, baru terpakai 1 pcs lewat satu Sales Order, lalu admin menutup manual sisa 1 pcs karena customer tidak melanjutkan — Quotation berstatus `closed` dengan riwayat 1 Sales Order yang tetap valid dan berjalan normal.
- Quotation ditutup manual atau expired padahal ada Sales Order yang masih `draft`/`confirmed` (belum ada Surat Jalan) — **Sales Order tetap berjalan normal**, tidak ikut batal otomatis (lihat Keputusan Terkonfirmasi).
- Customer sudah bayar DP via Proforma Invoice, tapi ternyata batal beli sebagian qty (Sales Order sisa qty ditutup) — nominal DP yang sudah dibayar untuk qty yang batal perlu proses refund manual di luar sistem pada fase pertama.
- Satu Sales Order menghasilkan lebih dari satu Invoice B2B (pengiriman bertahap ditagih terpisah) padahal DP di Proforma Invoice-nya cuma satu — **kredit DP hanya dialokasikan ke Invoice B2B pertama** (aturan MVP paling sederhana), Invoice B2B berikutnya dari Sales Order yang sama tidak dapat kredit DP lagi. DP tidak pernah dikreditkan dobel karena baris `dp_credit` hanya dibuat sekali per Sales Order (lihat Rekomendasi MVP).
- Admin salah input nominal Pembayaran (lebih besar dari yang diterima) — perlu kemampuan koreksi/hapus baris Pembayaran manual (bukan `dp_credit`), dengan status dokumen dihitung ulang otomatis.
- Invoice B2B dibatalkan (`cancelled`) padahal sudah ada Pembayaran tercatat — **pembatalan diblokir total** selama masih ada Pembayaran aktif; admin harus koreksi/hapus baris Pembayaran dulu (lihat Keputusan Terkonfirmasi).

## Open Questions

Sisa pertanyaan berikut bersifat operasional/detail teknis kecil, tidak menghalangi mulainya implementasi (bisa dijawab sambil jalan):

- Siapa user pertama yang akan diberi role "Staff Gudang" di tiap perusahaan? (murni onboarding, dilakukan lewat UI Admin Users yang sudah ada begitu role-nya dibuat)
- Apakah Packing List butuh nomor urut sendiri atau reuse nomor Surat Jalan pasangannya dengan prefix `PL-` berbeda? (default: nomor sendiri lewat `DocumentNumberGenerator`, konsisten dengan dokumen lain — bisa diubah tanpa dampak besar jika ternyata tim lebih suka reuse)

## Rekomendasi MVP

Untuk fase pertama, gunakan pendekatan berikut agar scope tetap terkendali:

- Bangun seluruh 6 dokumen (Quotation, Sales Order, Proforma Invoice, Surat Jalan, Packing List, Invoice B2B) plus ledger Pembayaran (`document_payments`) dengan data model dan status dasar seperti di atas.
- Proforma Invoice benar-benar opsional dan tidak menghalangi Surat Jalan pada fase pertama (tanpa flag "DP wajib" dulu).
- Pencatatan Pembayaran mendukung nominal, tanggal, catatan, dan upload bukti opsional (tidak wajib) — bukan sekadar toggle status lunas/belum.
- Kredit DP otomatis dari Proforma Invoice ke Invoice B2B dibatasi ke Invoice B2B pertama dari Sales Order terkait pada MVP (aturan alokasi paling sederhana). Kasus Invoice B2B lebih dari satu dengan alokasi DP proporsional/manual ditunda ke fase berikutnya.
- Approval Quotation tetap manual oleh admin (tanpa link publik/customer login) sesuai keputusan awal.
- Cetak dokumen memakai halaman HTML + `window.print()`, konsisten dengan pola existing, generator PDF server-side ditunda ke fase berikutnya.
- Tidak membangun UOM baru — pakai qty generik seperti pola produk yang sudah ada.
- Alur retur/pengembalian stok pasca Surat Jalan `shipped` ditunda ke fase berikutnya (fase pertama cukup mendukung pembatalan sebelum `shipped`).
- Batasi satu Proforma Invoice per Sales Order pada MVP (tanpa DP bertahap/termin) untuk menyederhanakan.
- Role "Staff Gudang" dibuat sebagai role baru dengan permission terbatas ke `sales_orders.index`/`sales_orders.show`, `delivery_notes.*`, dan `packing_lists.*`, di-assign per perusahaan (atau global) lewat `admin_company_assignments`.
- Ekstrak logic penomoran dokumen (`INV-`, `QUO-`, dst.) ke satu service bersama (`DocumentNumberGenerator`) berbasis `company.invoice_prefix`, sekaligus merapikan duplikasi 4 controller lama dan me-retrofit `Transaction::invoice_no`, karena momentumnya pas dilakukan bersamaan dengan fitur baru ini.

## Rekomendasi Urutan Implementasi

Mengikuti pola checkpoint bertahap yang sudah terbukti efektif di Fase 1 & 2 (`prd-multi-company-foundation.md`): setiap sub-langkah diimplementasikan, diuji (otomatis + manual/Playwright), baru lanjut ke sub-langkah berikutnya. Urutan berdasarkan dependency alur dokumen (tidak bisa dibalik — Sales Order butuh Quotation lebih dulu, dst.):

1. **Fondasi bersama**: migration 6 tabel dokumen + `company_id` di masing-masing, service `DocumentNumberGenerator` (company-prefix based) sekaligus retrofit `Transaction::invoice_no`, permission baru di `config/admin_permissions.php`, role "Staff Gudang" baru di `admin_roles`. Tidak ada UI baru di langkah ini — murni fondasi teknis, low-risk karena tidak mengubah alur retail yang sudah jalan (hanya menambah, retrofit invoice_no diuji khusus agar format lama tetap valid untuk data existing).
2. **Quotation**: model + migration `quotations`/`quotation_details`/`quotation_status_histories`, CRUD, transisi status manual (`sent`/`accepted`/`rejected`), auto-`expired` via scheduled job, validasi 1-perusahaan-per-Quotation, tutup manual (`closed`). Belum ada Sales Order — checkpoint ini berdiri sendiri dan bisa diuji penuh (create, edit harga per item, status histories, cetak) sebelum lanjut.
3. **Sales Order**: model + migration `sales_orders`/`sales_order_details`/`sales_order_status_histories`, aksi "Convert to Sales Order" dari Quotation (row lock sisa qty), auto-status `partially_converted`/`closed` di Quotation, cancel Sales Order (kembalikan sisa qty ke Quotation). Area risiko: race condition dua convert bersamaan — wajib diuji dengan concurrent request.
4. **Proforma Invoice + Payment ledger dasar**: model + migration `proforma_invoices`/`proforma_invoice_details`/`document_payments` (polymorphic), aksi opsional "Terbitkan Proforma Invoice" dari Sales Order, form "Catat Pembayaran" (nominal/tanggal/catatan/bukti opsional), auto-status `partially_paid`/`paid`, koreksi/hapus baris Pembayaran dengan recalculation. Payment ledger dibangun di sini karena akan dipakai ulang oleh Invoice B2B di langkah 6.
5. **Surat Jalan + Packing List (fulfillment)**: model + migration `delivery_notes`/`delivery_note_details`/`packing_lists`, aksi "Buat Pengiriman" dari Sales Order (partial qty, row lock stok, potong stok + `stock_movements` dalam satu `DB::transaction()`), auto-status `partially_fulfilled`/`fulfilled` di Sales Order, UI Staff Gudang (tanpa kolom harga). Area risiko tertinggi kedua setelah checkout multi-perusahaan Fase 2 — melibatkan potongan stok nyata, wajib diuji dengan stok pas-pasan & race condition dua Surat Jalan bersamaan.
6. **Invoice B2B**: model + migration `b2b_invoices`/`b2b_invoice_delivery_note`, aksi pilih Surat Jalan `shipped`/`delivered` yang belum ditagih → gabung jadi satu Invoice, kredit DP otomatis dari Proforma Invoice (jika ada, ke Invoice B2B pertama), reuse form "Catat Pembayaran" dari langkah 4, indikator `overdue`.
7. **Cetak dokumen & polish UI**: halaman cetak HTML untuk 6 dokumen (Quotation, Sales Order, Proforma Invoice, Surat Jalan + Packing List, Invoice B2B), badge status konsisten, halaman piutang per-perusahaan aktif (list Invoice B2B + indikator overdue).
8. **Uji menyeluruh 1 perusahaan dulu** (perilaku harus identik dengan asumsi single-company, tidak menyentuh `Transaction`/`TransactionDetail` retail sama sekali — regression test suite existing harus tetap hijau), baru role "Staff Gudang" & perusahaan kedua diaktifkan untuk transaksi B2B riil.

Dashboard piutang konsolidasi lintas-perusahaan, alur retur stok pasca-`shipped`, DP bertahap/termin, UOM, dan generator PDF server-side sengaja **tidak** masuk urutan di atas — semuanya sudah diputuskan ditunda ke fase berikutnya (lihat Rekomendasi MVP).
