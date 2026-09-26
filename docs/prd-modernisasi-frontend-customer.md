# PRD: Modernisasi Frontend Customer

Status: Implementasi berjalan; Sprint 0-2 selesai, Sprint 3-7 belum ditutup
Tanggal audit awal: 13 September 2026  
Area: Storefront / B2C customer experience  
Pendekatan delivery: sprint kecil dengan quality gate pada setiap tahap

## Ringkasan

Modernisasi ini memperbaiki seluruh pengalaman customer dari pertama membuka storefront sampai
pesanan selesai. Fokusnya bukan sekadar mengganti warna atau menambah animasi, tetapi membangun
frontend yang lebih cepat, stabil, mudah dipahami, konsisten, accessible, dan lebih membantu
customer membeli produk teknik dengan tepat.

Arah visual yang dipilih adalah **industrial-modern**: tegas, bersih, informatif, dan terpercaya.
Tampilan harus terasa seperti supplier teknik profesional, bukan template marketplace generik.

Implementasi dilakukan bertahap. Fondasi asset dan performa wajib diselesaikan sebelum redesign
besar agar setiap perubahan visual memiliki dasar teknis yang stabil.

## Progress Tracker

| Sprint | Fokus | Status | Dependensi | Bukti selesai |
| --- | --- | --- | --- | --- |
| Sprint 0 | Baseline dan inventory | Selesai | Tidak ada | [Report baseline + screenshot matrix](frontend-baseline/README.md) |
| Sprint 1 | Asset pipeline dan performance foundation | Selesai | Sprint 0 | [Build, query report, test autocomplete](frontend-baseline/sprint-1-report.md) |
| Sprint 2 | Design system dan global shell | Selesai | Sprint 1 | [Component catalog + responsive review](frontend-baseline/sprint-2-report.md) |
| Sprint 3 | Homepage, katalog, search, dan product card | Berjalan | Sprint 2 | Katalog/search/product card/flash sale sudah berubah; bukti screenshot dan seluruh acceptance criteria belum ditutup |
| Sprint 4 | Detail produk dan cart | Berjalan | Sprint 3 | Detail produk, variant state, dan cart sudah diperbarui; browser test product-to-cart khusus sprint belum lengkap |
| Sprint 5 | Checkout dan payment recovery | Berjalan | Sprint 4 | E2E guest, member, negative path, dan multi-company lulus; review seluruh recovery/payment state belum ditutup |
| Sprint 6 | Profil, order, tracking, wishlist, dan content | Berjalan | Sprint 2-5 | Profil, tracking, help center, content, SEO, dan PWA sudah berubah; account lifecycle matrix belum lengkap |
| Sprint 7 | Accessibility, performance, regression, dan release | Berjalan | Sprint 0-6 | Backend 214 test, browser 19 skenario, dan build lulus; audit final, screenshot/manual book, performance budget, dan sign-off belum selesai |

Nilai status yang digunakan: `Belum dimulai`, `Berjalan`, `Blocked`, dan `Selesai`. Status hanya boleh
diubah menjadi `Selesai` jika seluruh acceptance criteria sprint dan quality gate memiliki bukti.

Pemeriksaan ulang 27 September 2026 membuktikan implementasi Sprint 3-6 sudah berjalan sehingga status
lama `Belum dimulai` tidak lagi akurat. Status tetap `Berjalan`, bukan `Selesai`, karena checklist dan
bukti review visual/accessibility/performance per sprint belum seluruhnya tersedia. E2E checkout
lintas perusahaan membuktikan dua perusahaan menghasilkan dua pesanan independen melalui transfer
manual dengan satu akun Midtrans BOQ sebagai keputusan bisnis sementara.

## Latar Belakang dan Temuan Audit

### Temuan P0: asset frontend bergantung pada CDN

Layout customer saat ini memuat Tailwind melalui:

```html
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
```

Font Google dan Flaticon juga dimuat langsung dari layanan eksternal. Pada audit dengan akses CDN
terputus, halaman kehilangan hampir seluruh styling dan icon. Padahal project sudah memiliki pipeline
Vite dan `resources/css/app.css`, tetapi layout customer belum menggunakannya.

Dampak:

- Tampilan dapat rusak jika CDN lambat, diblokir, atau gagal.
- Browser harus memproses Tailwind saat runtime.
- Risiko flash of unstyled content dan layout shift meningkat.
- Cache dan versioning asset tidak sepenuhnya dikendalikan aplikasi.
- Release frontend sulit direproduksi secara konsisten.

### Temuan P0: pekerjaan berat dijalankan dari view navbar

Partial navbar menjalankan query kategori serta mengambil sampai 60 produk beserta kategori dan
variannya pada setiap render halaman. Data itu kemudian dikirim ke JavaScript untuk autocomplete.

Dampak:

- Setiap halaman customer menanggung biaya query dan serialisasi yang besar.
- HTML menjadi lebih berat.
- Waktu respons bertambah seiring pertumbuhan katalog.
- Search suggestion tidak scalable.
- Logic data bercampur dengan presentasi.

### Temuan visual

- Banyak section menggunakan kartu putih, radius besar, shadow, badge, dan aksen biru dengan bobot
  visual yang hampir sama.
- Gradient biru-ungu dipakai di banyak tempat sehingga identitas toko belum khas.
- Homepage panjang dan terlalu banyak section berebut perhatian.
- Istilah bahasa bercampur, misalnya `Popular Categories` di antara label Bahasa Indonesia.
- Product card lebih menonjolkan promo daripada spesifikasi teknis.
- Banyak teks menggunakan ukuran sangat kecil (`9px`, `10px`, `11px`, dan `text-xs`).
- CSS dan JavaScript halaman tersebar sebagai blok inline besar di banyak file Blade.
- Navbar, mobile bottom navigation, sticky purchase action, chat, dan toast berpotensi bertumpuk di
  layar kecil.
- Checkout sudah fungsional tetapi panjang dan memiliki beban kognitif tinggi.
- Footer memuat banyak informasi kebijakan sebagai modal panjang; aksesibilitas keyboard dan focus
  management belum seragam.

### Hal yang sudah baik dan dipertahankan

- Guest checkout.
- Mobile bottom navigation.
- Sticky purchase action pada detail produk mobile.
- Filter katalog.
- Wishlist, notifikasi, tracking, dan riwayat pesanan.
- Dukungan transaksi dan pengiriman multi-perusahaan.
- Pilihan payment gateway dan transfer manual.
- Permintaan faktur pajak customer.
- Promo, flash sale, redeem point, dan newsletter.

Fitur tersebut tidak dihapus. Presentasi, performa, dan hierarkinya yang diperbaiki.

## Masalah yang Ingin Diselesaikan

1. Customer tidak selalu bisa menemukan produk teknis yang tepat dengan cepat.
2. Spesifikasi penting belum cukup terlihat pada listing dan product card.
3. Banyak komponen memiliki bobot visual yang sama sehingga prioritas aksi tidak jelas.
4. Alur cart dan checkout membutuhkan terlalu banyak scroll, khususnya pada mobile.
5. Frontend bergantung pada asset eksternal untuk styling kritis.
6. Query dan payload navbar tidak siap menghadapi katalog yang lebih besar.
7. Pola komponen, typography, spacing, state, dan feedback belum menjadi satu design system.
8. Accessibility, keyboard navigation, reduced motion, dan target sentuh belum konsisten.
9. Perubahan frontend sulit dirawat karena CSS dan JavaScript besar berada langsung di file Blade.

## Tujuan

- Storefront tampil modern, profesional, dan memiliki identitas industrial yang konsisten.
- Customer dapat mencari produk berdasarkan nama, SKU, kategori, ukuran, atau atribut relevan.
- Informasi teknis dan ketersediaan produk terlihat sebelum customer membuka detail.
- Jalur homepage -> produk -> cart -> checkout lebih ringkas dan jelas.
- Frontend tetap terbaca dan berfungsi pada desktop, tablet, dan mobile.
- Styling kritis tidak bergantung pada CDN saat runtime.
- Navbar dan autocomplete tetap cepat saat jumlah produk bertambah.
- Seluruh state utama memiliki feedback yang jelas: loading, kosong, berhasil, gagal, dan disabled.
- Implementasi tidak merusak fitur bisnis, route, SEO, multi-company, payment, pajak, atau tracking.
- Setiap sprint dapat dirilis dan diuji secara independen.

## Non-Tujuan

- Tidak mengganti framework Laravel Blade dengan React/Vue pada program ini.
- Tidak mengubah logika harga, stok, pajak, kupon, point, atau payment tanpa PRD domain terkait.
- Tidak mengubah kontrak Open Catalog API kecuali endpoint autocomplete baru yang backward-compatible.
- Tidak menghapus guest checkout.
- Tidak menggabungkan pembayaran antarperusahaan.
- Tidak membuat aplikasi mobile native.
- Tidak melakukan redesign admin panel dalam scope ini.
- Tidak mengubah struktur URL publik yang sudah dipakai SEO tanpa migration plan terpisah.

## Persona dan Jobs To Be Done

### Customer umum

- Menemukan produk dengan cepat.
- Membandingkan pilihan tanpa harus membuka terlalu banyak halaman.
- Mengetahui harga, stok, dan ongkir sebelum membayar.
- Checkout dengan minimum hambatan.

### Customer teknis / procurement

- Mencari berdasarkan SKU, ukuran, material, finishing, dan satuan.
- Memastikan produk dan varian yang dipilih benar.
- Mengetahui perusahaan penjual dan dokumen transaksi yang tersedia.
- Meminta faktur pajak dan melacak pesanan.

### Customer mobile

- Menggunakan search, filter, cart, dan checkout dengan satu tangan.
- Selalu melihat aksi utama tanpa tertutup navigasi atau chat widget.
- Mengisi form tanpa horizontal overflow atau keyboard menutup CTA.

### Customer lama

- Menemukan kembali wishlist dan pesanan.
- Membeli ulang produk.
- Mengunduh invoice/faktur pajak dan melihat tracking.

## Prinsip Desain

1. **Search-first**: pencarian produk adalah aksi utama storefront.
2. **Specification before decoration**: spesifikasi produk lebih penting daripada badge dekoratif.
3. **One primary action**: setiap viewport memiliki satu CTA utama yang paling jelas.
4. **Controlled density**: informasi teknis padat tetapi tetap memiliki grouping dan whitespace.
5. **Industrial trust**: visual menekankan ketepatan, stok, pengiriman, dan kredibilitas toko.
6. **Progressive disclosure**: detail lanjutan muncul ketika diperlukan, bukan ditampilkan sekaligus.
7. **Mobile is task-focused**: area sentuh besar, action mudah dijangkau, dan tidak ada tumpukan bar.
8. **Motion with purpose**: animasi menjelaskan perubahan state, bukan sekadar dekorasi.
9. **Resilient by default**: UI inti tetap berfungsi tanpa layanan font/icon/CDN pihak ketiga.
10. **Accessible by design**: keyboard, screen reader, contrast, dan reduced motion bukan tambahan akhir.

## Arah Visual: Industrial-Modern

### Karakter

- Profesional, presisi, kokoh, dan cepat.
- Menggunakan garis/grid yang terinspirasi katalog teknik dan label spesifikasi.
- Menghindari kesan playful berlebihan dan gradient marketplace generik.
- Menggunakan foto produk sebagai elemen visual utama.

### Palet awal

- `Graphite`: navigasi, heading, dan permukaan gelap.
- `Steel`: border, divider, muted surface, dan secondary text.
- `Warm white`: background utama agar tidak terasa steril.
- `Cobalt blue`: link, focus, dan aksi informasional.
- `Safety orange`: CTA transaksi dan promo penting.
- `Success green`, `warning amber`, dan `danger red`: hanya untuk semantic state.

Nilai warna final harus lolos contrast test dan dapat disesuaikan dengan logo/brand resmi.

### Typography

- Body: Plus Jakarta Sans dapat dipertahankan jika di-self-host.
- Display/heading: gunakan font condensed berkarakter industrial, kandidat awal `Barlow Condensed`.
- Body default desktop/mobile minimal 14px; field form minimal 16px pada mobile.
- Teks 9-11px hanya boleh untuk metadata non-kritis yang tetap lolos readability review.
- Harga, SKU, dan varian memakai angka dengan alignment dan weight yang konsisten.

### Bentuk dan elevation

- Radius utama 10-14px; radius pill hanya untuk chip/status yang benar-benar membutuhkan.
- Shadow digunakan untuk layer yang mengambang, bukan setiap kartu.
- Section biasa dibedakan melalui spacing, border, atau perubahan surface.
- Satu jenis CTA primary, secondary, tertiary, dan destructive.

### Motion

- Gunakan transisi 150-250ms untuk hover, drawer, tab, dan feedback.
- Maksimal satu entrance sequence penting per halaman.
- Dukung `prefers-reduced-motion`.
- Tidak ada carousel yang bergerak tanpa kontrol pause atau yang mengganggu pembacaan.

## Scope Functional dan UX

### 1. Asset Pipeline dan Frontend Foundation

Requirement:

- Layout customer menggunakan `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- Tailwind Browser CDN dihapus dari seluruh halaman customer.
- Font dan icon kritis dibundle atau di-self-host.
- Asset memiliki hash/version dari Vite.
- Production build dapat dijalankan dengan `npm run build`.
- CSP dan third-party script tetap kompatibel.
- Tawk/chat dimuat deferred dan tidak memblokir render utama.
- Tersedia fallback icon lokal/SVG untuk aksi transaksi penting.
- Tidak ada layout shift besar saat font dan gambar selesai dimuat.

### 2. Design Tokens dan Component System

Buat token terpusat untuk:

- Warna brand dan semantic state.
- Typography scale.
- Spacing scale.
- Radius.
- Border dan elevation.
- Transition/motion.
- Container width dan responsive breakpoint.
- Z-index layer: navbar, dropdown, drawer, sticky CTA, toast, chat, dan modal.

Komponen minimum:

- Button: primary, secondary, outline, text, destructive, loading, dan disabled.
- Form field, select, checkbox, radio, quantity input, validation message.
- Product card.
- Status badge dan specification chip.
- Section heading.
- Empty state, error state, skeleton, toast, dan inline alert.
- Modal, drawer, dropdown, tabs, accordion, dan pagination.
- Price display dan discount display.
- Store/company label.

Ketentuan:

- Hindari menambah CSS inline baru.
- CSS/JS inline existing dipindahkan bertahap saat halaman disentuh.
- State komponen harus sama di seluruh halaman.

### 3. Global Customer Shell

Mencakup navbar, announcement, footer, mobile navigation, chat, toast, dan global modal.

Navbar desktop:

- Logo, search dominan, kategori, cart, notifikasi, wishlist, dan account memiliki hierarki jelas.
- Secondary navigation disederhanakan.
- Mega menu dapat digunakan dengan mouse dan keyboard.
- Navbar sticky tidak memakan ruang vertikal berlebihan.
- Search suggestion tidak menyimpan 60 produk di HTML awal.

Navbar mobile:

- Search bar mudah ditemukan dan dapat langsung digunakan.
- Bottom navigation memiliki label/icon yang konsisten.
- Active state tidak mengubah lebar item secara mengganggu.
- Cart badge tetap terbaca pada jumlah besar.
- Gunakan `env(safe-area-inset-bottom)`.

Footer:

- Informasi kontak, jam operasional, alamat, bantuan, kebijakan, dan social link ringkas.
- Kebijakan privasi dan syarat ketentuan diarahkan ke content page yang memiliki URL, bukan hanya
  modal panjang.
- Link legal dapat dibuka, disalin, dan diindeks sesuai kebutuhan.
- Modal yang tetap dipakai memiliki focus trap, close button, Escape, dan focus restoration.

### 4. Search dan Autocomplete

- Autocomplete memakai endpoint async dan hanya aktif setelah minimal 2 karakter.
- Request memiliki debounce dan cancellation untuk query lama.
- Cari berdasarkan nama, SKU, kategori, serta atribut/varian yang diindeks.
- Maksimal 6-8 suggestion awal.
- Suggestion menampilkan foto, nama, SKU/varian ringkas, harga mulai, dan perusahaan.
- Keyboard mendukung Arrow Up/Down, Enter, dan Escape.
- Loading, tidak ditemukan, dan error state tersedia.
- Query dan hasil dapat dianalisis tanpa menyimpan data sensitif.
- Category tree disiapkan oleh controller/view composer dan menggunakan cache dengan invalidation.

### 5. Homepage

Urutan informasi:

1. Search dan value proposition.
2. Hero utama dengan satu pesan dan CTA yang jelas.
3. Shortcut kategori teknik.
4. Produk unggulan atau terlaris.
5. Promo/flash sale aktif.
6. Keunggulan toko dan layanan konsultasi.
7. Brand atau industri yang dilayani jika datanya tersedia.
8. Artikel/panduan terbaru.
9. Newsletter.

Requirement:

- Hero tidak hanya bergantung pada gambar banner; title, supporting copy, dan CTA tersedia.
- Carousel, jika tetap digunakan, memiliki kontrol, pause, dan tidak auto-rotate terlalu cepat.
- Section produk terbaru dan rekomendasi tidak menampilkan daftar yang terasa duplikat.
- Filter katalog lengkap tidak ditempatkan sebagai beban utama di homepage desktop.
- Semua copy menggunakan Bahasa Indonesia yang konsisten.
- Homepage mobile tidak terlalu panjang sebelum produk pertama terlihat.
- Empty state tersedia ketika katalog, promo, banner, atau blog belum diisi setelah reset production.

### 6. Product Card

Informasi minimum:

- Foto produk dengan aspect ratio konsisten.
- Nama produk maksimal dua baris.
- SKU atau kode produk bila tersedia.
- Ringkasan ukuran/material/atribut utama.
- Harga atau `Mulai dari` jika harga varian berbeda.
- Satuan jual: pcs, box, set, kilogram, atau unit terkait.
- Status stok yang tidak menyesatkan.
- Perusahaan/seller pada katalog multi-company.
- Rating dan jumlah terjual hanya jika datanya bermakna.

Behavior:

- Seluruh kartu dapat menuju detail tanpa nested interactive element yang invalid.
- Quick add hanya tersedia jika tidak membutuhkan pemilihan varian kompleks.
- Produk tanpa gambar memiliki placeholder lokal yang profesional.
- Maksimal satu badge promosi dan satu status penting pada saat yang sama.
- Hover desktop tidak menjadi satu-satunya cara melihat informasi atau aksi.
- Skeleton mengikuti ukuran kartu final untuk mencegah layout shift.

### 7. Halaman Kategori, Search Result, Promo, dan Flash Sale

- Judul, jumlah hasil, sort, active filter, dan reset filter mudah ditemukan.
- Mobile filter menggunakan bottom sheet/drawer dengan CTA `Terapkan` dan `Reset` yang jelas.
- Active filter tampil sebagai removable chips.
- Sort memiliki label yang mudah dipahami.
- Grid/list preference dapat dipertahankan tanpa membuat kontrol berlebihan.
- Pagination atau load-more mempertahankan posisi/filter ketika customer kembali dari detail.
- Empty result memberikan saran koreksi, kategori lain, dan tombol reset.
- Flash sale menunjukkan waktu berdasarkan data server dan state setelah promo berakhir.
- Tidak ada countdown palsu atau default statis yang membingungkan.
- Promo tidak mengorbankan keterbacaan nama dan spesifikasi produk.

### 8. Detail Produk

Desktop:

- Galeri berada di kiri dan purchase panel sticky berada di kanan.
- Judul, seller, SKU, rating, sold, stok, dan harga memiliki urutan visual yang jelas.
- Pilihan varian menggunakan chip atau kartu yang memperlihatkan available/disabled state.
- Harga dan gambar berubah tanpa layout jump saat varian dipilih.
- Quantity mengikuti stok dan minimum order.
- CTA utama `Beli Sekarang`; CTA sekunder `Tambah ke Keranjang`.
- CTA konsultasi `Tanya Spesifikasi` menuju WhatsApp jika tersedia.

Mobile:

- Sticky purchase action digabung dengan sistem bottom navigation agar tidak bertumpuk.
- Variant drawer memperhitungkan safe area dan keyboard.
- Harga, stok, dan CTA utama selalu terbaca.
- Galeri mendukung swipe dan indikator gambar.

Informasi produk:

- Ringkasan spesifikasi penting muncul sebelum deskripsi panjang.
- Deskripsi, spesifikasi/varian, ulasan, pengiriman, dan retur menggunakan section/accordion yang jelas.
- Estimator ongkir tersedia sebelum checkout jika data tujuan dapat diminta dengan aman.
- Trust information bersifat konkret: stok, asal pengiriman, metode pembayaran, dan kebijakan retur.
- Produk terkait dan terakhir dilihat tidak mendominasi produk utama.
- Structured data dan metadata SEO existing tetap valid.

### 9. Cart

- Item dikelompokkan per perusahaan dengan label seller yang jelas.
- Checkbox select-all tersedia global dan per perusahaan bila relevan.
- Varian, satuan, harga, diskon, stok, dan subtotal item terlihat.
- Quantity update memiliki optimistic/loading state dan rollback saat gagal.
- Produk tidak tersedia diberi alasan serta aksi hapus/ganti varian.
- Ringkasan belanja sticky pada desktop.
- Total dan CTA checkout sticky pada mobile.
- CTA disabled harus memiliki penjelasan, bukan hanya opacity.
- Empty cart memiliki CTA kembali ke katalog dan rekomendasi singkat.
- Hapus item menyediakan feedback dan opsi undo jika feasible.

### 10. Checkout

Checkout menggunakan progressive disclosure tanpa mengubah aturan transaksi multi-company.

Tahap logis:

1. Data penerima/alamat.
2. Pengiriman per perusahaan.
3. Voucher dan faktur pajak.
4. Metode pembayaran.
5. Review dan konfirmasi pesanan.

Requirement:

- Customer dapat melihat tahap saat ini dan error pada tahap terkait.
- Guest checkout tetap tersedia dan tidak dipaksa membuat akun.
- Form mobile menggunakan ukuran input minimal 16px dan autocomplete attribute yang benar.
- Ongkir, subtotal, diskon, PPN, biaya lain, dan grand total transparan per perusahaan.
- Opsi Midtrans hanya muncul untuk perusahaan yang telah dikonfigurasi.
- Transfer manual tetap menunjukkan rekening perusahaan yang benar.
- Permintaan faktur pajak mengikuti PRD faktur pajak customer dan tidak mengubah total.
- Ringkasan pesanan sticky desktop dan collapsible sticky summary mobile.
- CTA `Bayar Sekarang` tetap terlihat tanpa menutupi field.
- Double submit dicegah dan loading state menjelaskan proses yang berlangsung.
- Kegagalan salah satu transaksi multi-company tidak menghasilkan state ambigu.
- Customer menerima recovery action jika payment popup ditutup atau provider gagal.
- Consent dan link kebijakan tersedia sebelum pembayaran.

### 11. Checkout Waiting, Success, dan Payment Recovery

- Setiap transaksi menampilkan seller, order ID, nominal, metode bayar, dan status.
- Untuk checkout multi-company, transaksi ditampilkan sebagai kartu terpisah.
- Tombol bayar/unggah bukti hanya muncul sesuai status transaksi.
- Polling status memiliki interval terkendali dan berhenti pada terminal state.
- Status tidak hanya dibedakan berdasarkan warna.
- Customer dapat kembali ke daftar pesanan atau melanjutkan pembayaran lain.
- Error provider tidak menampilkan detail internal atau credential.

### 12. Profile, Wishlist, Notification, dan Order History

- Profile mobile tidak menjadi satu halaman sangat panjang; gunakan navigasi section yang konsisten.
- Ringkasan akun, point, tier, dan aksi penting tampil di awal.
- Riwayat pesanan memiliki filter status, seller, tanggal, dan pencarian order ID.
- Order card menampilkan aksi yang relevan terhadap state: bayar, tracking, invoice, faktur pajak,
  retur, ulasan, atau beli lagi.
- Wishlist memakai product card yang sama dengan katalog.
- Notification memiliki read/unread state yang jelas dan dapat dinavigasi keyboard.
- Data pajak sensitif tetap dimasking sesuai PRD faktur pajak customer.
- Empty/loading/error state tersedia pada setiap tab.

### 13. Order Tracking

- Form tracking meminta pasangan data minimum tanpa membocorkan keberadaan order.
- Setelah verifikasi, timeline status menjadi fokus utama.
- Nomor resi, kurir, seller, estimasi, dan timestamp mudah dibaca.
- Timeline memiliki label selain icon/warna.
- Upload bukti pembayaran tetap tersedia hanya untuk order yang eligible.
- Error dan rate limit menggunakan pesan generik yang actionable.

### 14. Blog, Content Page, Newsletter, dan Footer Content

- Artikel menggunakan layout editorial yang nyaman dibaca.
- Lebar paragraf dibatasi dan hierarchy heading konsisten.
- Gambar memiliki dimensions/aspect ratio dan lazy loading.
- Newsletter memiliki benefit yang jelas dan tidak terasa seperti penghalang.
- Success/error subscription diumumkan ke assistive technology.
- Content page legal memiliki URL permanen dan dapat dibagikan.

### 15. Global UX States

Setiap fitur asynchronous wajib memiliki:

- Initial state.
- Loading/skeleton state.
- Empty state.
- Success feedback.
- Validation error.
- Network/server error.
- Disabled/pending state.
- Retry atau recovery action jika aman.

Toast tidak boleh menjadi satu-satunya tempat menampilkan error form kritis. Pesan harus menggunakan
Bahasa Indonesia, ringkas, dan tidak membocorkan detail internal.

## Requirement Accessibility

- Seluruh fitur inti dapat digunakan dengan keyboard.
- Focus indicator selalu terlihat melalui `:focus-visible`.
- Modal/drawer memiliki focus trap, Escape, accessible name, dan focus restoration.
- Dropdown dan autocomplete menggunakan semantic/ARIA pattern yang sesuai.
- Target sentuh utama minimal 44x44px.
- Input mobile minimal 16px untuk mencegah auto-zoom.
- Warna bukan satu-satunya indikator status.
- Contrast text, icon, border penting, dan CTA lolos pemeriksaan.
- Heading mengikuti urutan logis.
- Semua image memiliki alt yang relevan; decorative image menggunakan alt kosong.
- Form error terhubung ke field dan diumumkan screen reader.
- Live update cart/payment menggunakan live region seperlunya.
- `prefers-reduced-motion` didukung.
- Zoom 200% tidak menyebabkan hilangnya fungsi atau horizontal overflow.

## Requirement Performance dan Reliability

Target internal pada halaman utama, kategori, dan detail produk:

- LCP target <= 2,5 detik pada p75 setelah data production tersedia.
- CLS target <= 0,1.
- INP target <= 200ms.
- Tidak ada critical CSS/font/icon yang bergantung pada third-party runtime CDN.
- Gambar memakai ukuran responsif, dimension/aspect ratio, WebP, dan lazy loading di bawah fold.
- Hero/LCP image tidak di-lazy-load dan dapat diprioritaskan.
- Search suggestion memiliki debounce, cancellation, cache singkat, dan response terukur.
- Query navbar tidak bertambah linear terhadap jumlah produk.
- Third-party chat/script di-defer dan tidak memblokir interaction.
- Error asset atau API tidak membuat seluruh halaman tidak dapat digunakan.

Performance budget awal:

- CSS customer <= 120 KB gzip.
- JavaScript aplikasi customer <= 220 KB gzip, di luar provider payment/chat yang lazy/deferred.
- Tidak mengirim seluruh dataset produk ke HTML untuk autocomplete.
- Tidak ada query database langsung baru di Blade.

Budget dapat disesuaikan berdasarkan hasil baseline Sprint 0, tetapi perubahan harus dicatat di PRD.

## Requirement Arsitektur Frontend

- Blade tetap menjadi rendering utama.
- Style global dan komponen berada di `resources/css`.
- JavaScript dipisahkan per concern di `resources/js` dan dibundle Vite.
- Data halaman diberikan melalui controller/view model, bukan query Eloquent dari Blade.
- Category navigation menggunakan cache dengan invalidation saat admin mengubah kategori.
- Autocomplete memakai endpoint dengan rate limit, validation, result limit, dan company visibility.
- Component Blade digunakan untuk pola berulang seperti product card, button, badge, price, dan state.
- Business rule tetap berada di backend; frontend tidak menjadi sumber kebenaran harga/stok/pajak.
- Hindari HTML yang dibuat seluruhnya melalui string JavaScript jika Blade/component dapat digunakan.
- Semua request mutasi tetap menggunakan CSRF dan menangani non-JSON error dengan aman.

## Analytics dan Ukuran Keberhasilan

Event minimum, tanpa menyimpan data sensitif:

- `search_submitted`
- `search_suggestion_selected`
- `category_opened`
- `product_viewed`
- `variant_selected`
- `add_to_cart`
- `buy_now_started`
- `cart_checkout_started`
- `checkout_step_completed`
- `payment_started`
- `payment_completed`
- `payment_failed`

Metric yang dibandingkan sebelum dan sesudah:

- Search-to-product-view rate.
- Product-view-to-cart rate.
- Cart-to-checkout rate.
- Checkout completion rate.
- Error rate per tahap checkout.
- Waktu median menemukan dan membuka produk.
- Persentase zero-result search.
- Web performance p75 per device class.
- Support issue terkait salah varian, ongkir, atau pembayaran.

Analytics tidak boleh merekam NPWP, alamat, password, token, credential, atau payload pembayaran.

## Compatibility dan Dependensi

Modernisasi wajib mempertahankan kompatibilitas dengan:

- PRD multi-company foundation dan company scoping.
- PRD guest checkout.
- PRD PPN checkout.
- PRD faktur pajak customer.
- Midtrans, manual transfer, RajaOngkir, email, dan WhatsApp.
- SEO metadata, canonical, sitemap, dan structured data.
- Open Catalog API.
- Existing admin management untuk produk, banner, promo, content, dan store settings.

Perubahan kontrak atau business rule harus dipindahkan ke PRD domain terkait dan tidak disisipkan
diam-diam dalam pekerjaan visual.

Dokumen terkait:

- [PRD Multi-Company Foundation](prd-multi-company-foundation.md)
- [PRD Guest Checkout](prd-guest-checkout.md)
- [PRD PPN Checkout](prd-ppn-checkout.md)
- [PRD Faktur Pajak Customer](prd-faktur-pajak-customer.md)
- [PRD Production Readiness Stabilization](prd-production-readiness-stabilization.md)
- [Go-Live Checklist](go-live-checklist.md)

## Edge Cases

- Database production baru masih kosong setelah reset.
- Produk tidak memiliki gambar atau varian.
- Produk memiliki banyak kombinasi varian.
- Produk atau perusahaan dinonaktifkan ketika masih berada di cart.
- Harga/stok berubah setelah halaman dibuka.
- Dua perusahaan memiliki produk dengan nama serupa.
- Salah satu perusahaan belum memiliki Midtrans tetapi memiliki transfer manual.
- Salah satu seller gagal menghitung ongkir saat seller lain berhasil.
- Customer menutup payment popup atau kembali menggunakan browser back.
- CDN pihak ketiga, chat, font, atau icon gagal dimuat.
- Jaringan mobile lambat atau request terputus.
- Nama produk, nama perusahaan, alamat, dan nominal sangat panjang.
- Cart kosong, hasil pencarian kosong, promo berakhir, atau blog belum tersedia.
- Mobile keyboard terbuka saat sticky CTA aktif.
- Device memiliki safe area/notch.
- Customer memakai reduced motion, keyboard-only, zoom 200%, atau screen reader.

## Risiko dan Mitigasi

### Risiko perubahan visual terlalu besar sekaligus

Mitigasi: rilis berdasarkan shell/component/page, gunakan screenshot regression, dan jangan menggabung
redesign checkout dengan perubahan business rule payment.

### Risiko class Tailwind tidak masuk production build

Mitigasi: pastikan seluruh Blade/JS berada dalam `@source`, hindari class dinamis tanpa safelist, dan
verifikasi build artifact pada CI.

### Risiko conversion turun karena CTA berubah

Mitigasi: pertahankan istilah aksi utama, pasang analytics sebelum redesign, dan bandingkan funnel.

### Risiko halaman lambat karena gambar dan third-party

Mitigasi: responsive image, lazy loading, deferred chat, performance budget, dan pengujian jaringan
mobile.

### Risiko tumpang tindih mobile layer

Mitigasi: satu z-index map, satu mobile action coordinator, safe-area testing, dan viewport matrix.

### Risiko aksesibilitas tertunda

Mitigasi: accessibility menjadi acceptance criteria setiap sprint, bukan sprint kosmetik terakhir.

## Rencana Implementasi per Sprint

Setiap sprint harus selesai dan lolos gate sebelum sprint berikutnya dimulai.

### Sprint 0 - Baseline dan Inventory

Scope:

- Catat screenshot desktop/mobile untuk seluruh halaman customer.
- Ukur performance dan payload baseline.
- Catat query count homepage, kategori, detail, cart, checkout, dan profil.
- Inventory CSS inline, JavaScript inline, component duplikat, third-party asset, dan z-index.
- Pastikan analytics funnel minimum dapat diukur.
- Tentukan final brand palette dan typography bersama owner.

Acceptance criteria:

- [x] Baseline screenshot tersedia untuk viewport 360, 390, 768, 1024, dan 1440.
- [x] Baseline performance, request, asset size, dan query count tercatat.
- [x] Daftar route/page customer lengkap tersedia.
- [x] Warna, typography, density, dan tone industrial disetujui.
- [x] Tidak ada perubahan business behavior.

Bukti: [Sprint 0 Frontend Baseline dan Inventory](frontend-baseline/README.md).

### Sprint 1 - Asset Pipeline dan Performance Foundation

Scope:

- Hubungkan layout customer ke Vite.
- Hapus Tailwind Browser CDN.
- Bundle/self-host font dan icon kritis.
- Pindahkan query navbar ke service/view composer.
- Cache category tree.
- Buat endpoint async autocomplete.
- Defer third-party chat.

Acceptance criteria:

- [x] Frontend tetap ter-styling saat internet eksternal diblokir.
- [x] `npm run build` lulus dan asset ter-versioning.
- [x] Navbar tidak query 60 produk/varian pada setiap page load.
- [x] Autocomplete mendukung debounce, cancellation, keyboard, loading, empty, dan error.
- [x] Existing feature dan browser test tetap lulus.
- [x] Tidak ada regression SEO metadata.

Bukti: [Sprint 1 Asset Pipeline dan Performance Foundation](frontend-baseline/sprint-1-report.md).

### Sprint 2 - Design System dan Global Shell

Scope:

- Implementasi token industrial-modern.
- Component button, form, badge, price, state, modal, drawer, dan product card foundation.
- Redesign announcement, navbar desktop/mobile, bottom navigation, footer, toast, dan global modal.
- Definisikan z-index dan safe-area behavior.

Acceptance criteria:

- [x] Semua aksi utama memakai component/state yang konsisten.
- [x] Navbar dan mega menu dapat digunakan dengan keyboard.
- [x] Mobile navigation, chat, toast, dan sticky element tidak bertumpuk.
- [x] Footer legal memiliki URL atau fallback accessible yang jelas.
- [x] Contrast, focus-visible, touch target, dan reduced motion lolos review.

Bukti: [Sprint 2 Design System dan Global Shell](frontend-baseline/sprint-2-report.md).

### Sprint 3 - Homepage, Katalog, Search, dan Product Card

Scope:

- Susun ulang homepage.
- Implementasi product card baru.
- Redesign kategori, search result, promo, dan flash sale.
- Implementasi mobile filter drawer dan active chips.
- Tambahkan semua empty/loading/error states.

Acceptance criteria:

- [ ] Produk utama terlihat lebih cepat pada mobile.
- [ ] Product card menampilkan seller, harga, stok, satuan, dan spesifikasi minimum.
- [ ] Istilah Bahasa Indonesia konsisten.
- [ ] Filter/sort bertahan ketika kembali dari detail.
- [ ] Promo berakhir tidak meninggalkan countdown palsu.
- [ ] Empty catalog setelah reset production tetap terlihat profesional dan actionable.

### Sprint 4 - Detail Produk dan Cart

Scope:

- Redesign galeri dan purchase panel detail produk.
- Redesign variant selector, specification, ongkir, trust, review, dan related products.
- Redesign cart per perusahaan.
- Implementasi sticky cart summary mobile.

Acceptance criteria:

- [ ] Harga, stok, gambar, dan CTA sinkron terhadap varian terpilih.
- [ ] Variant unavailable tidak dapat dibeli.
- [ ] Desktop purchase panel sticky tanpa menutup konten.
- [ ] Mobile purchase action tidak bertumpuk dengan bottom navigation/chat.
- [ ] Cart menjelaskan seller, varian, satuan, subtotal, dan item tidak tersedia.
- [ ] Update quantity dan delete memiliki pending/error/recovery state.

### Sprint 5 - Checkout dan Payment Recovery

Scope:

- Implementasi progressive checkout.
- Redesign address, shipping per company, voucher, pajak/faktur, payment, dan order summary.
- Sticky/collapsible mobile summary.
- Redesign waiting/success/multi-order payment page.
- Perkuat double-submit prevention dan recovery state.

Acceptance criteria:

- [ ] Guest dan member checkout tetap lulus end-to-end.
- [ ] Multi-company menghasilkan transaksi independen sesuai business rule existing.
- [ ] Ongkir, diskon, PPN, faktur pajak, dan total per perusahaan transparan.
- [ ] Payment method mengikuti konfigurasi masing-masing perusahaan.
- [ ] CTA selalu terlihat tetapi tidak menutup form/keyboard.
- [ ] Provider error dan popup close memiliki recovery action.
- [ ] Tidak ada duplicate order, payment, atau stock movement.

### Sprint 6 - Profile, Orders, Tracking, Wishlist, dan Content

Scope:

- Redesign profile navigation dan account summary.
- Redesign order history/detail actions.
- Redesign tracking timeline.
- Samakan wishlist dengan product card baru.
- Redesign notification, blog, content page, newsletter, dan support states.

Acceptance criteria:

- [ ] Customer dapat menemukan order dan aksi berikutnya dengan cepat.
- [ ] Invoice/faktur pajak/retur/review/tracking hanya muncul pada state yang benar.
- [ ] Data sensitif tetap dimasking.
- [ ] Tracking tidak membocorkan order sebelum verifikasi.
- [ ] Semua tab memiliki loading, empty, error, dan retry state.

### Sprint 7 - Accessibility, Performance, Regression, dan Release

Scope:

- Audit keyboard, screen reader, contrast, zoom, reduced motion, dan mobile safe area.
- Audit responsive di browser/device matrix.
- Audit performance budget dan query count.
- Screenshot regression seluruh halaman.
- Full backend/browser test dan production build.
- Update manual book screenshot dan dokumentasi customer.

Acceptance criteria:

- [ ] Seluruh acceptance criteria global terpenuhi.
- [ ] Tidak ada horizontal overflow pada viewport minimum 360px.
- [ ] Core flow dapat diselesaikan keyboard-only.
- [ ] Target performance tercapai atau exception memiliki bukti, owner, dan due date.
- [ ] Backend, browser E2E, build, dan security check lulus.
- [ ] Manual book menggunakan screenshot UI terbaru.
- [ ] Owner melakukan sign-off sebelum rollout penuh.

## Quality Gate Setiap Sprint

- [ ] Scope sprint dan perubahan route/API terdokumentasi.
- [ ] Unit/feature test relevan ditambah atau diperbarui.
- [ ] Browser test happy path dan negative path relevan lulus.
- [ ] `npm run build` lulus.
- [ ] Tidak ada error console browser pada core flow.
- [ ] Desktop dan mobile screenshot review selesai.
- [ ] Keyboard/focus/contrast/touch-target review selesai pada komponen yang disentuh.
- [ ] Loading/empty/error/disabled/retry state sudah diuji.
- [ ] Tidak ada query Eloquent baru di Blade.
- [ ] Performance dibandingkan dengan baseline.
- [ ] SEO dan structured data diperiksa pada halaman publik yang disentuh.
- [ ] Perubahan business rule, jika ada, telah dipindahkan ke PRD domain terkait.

## Acceptance Criteria Global

- [ ] Tailwind Browser CDN tidak digunakan oleh frontend customer.
- [ ] Styling dan icon aksi kritis tersedia tanpa koneksi ke CDN pihak ketiga.
- [ ] Layout customer menggunakan production Vite assets.
- [ ] Tidak ada dataset 60 produk/varian yang diserialisasi ke setiap halaman untuk autocomplete.
- [ ] Seluruh halaman menggunakan design token dan component state yang konsisten.
- [ ] Identitas visual industrial-modern diterapkan tanpa gradient/dekorasi generik berlebihan.
- [ ] Body text, form, metadata, dan CTA memenuhi readability target.
- [ ] Homepage memiliki hierarki search -> kategori -> produk -> promo -> trust/content.
- [ ] Product card menampilkan informasi teknis minimum dan seller.
- [ ] Detail produk memiliki variant, stock, price, shipping, specification, dan CTA yang jelas.
- [ ] Cart dan checkout memiliki sticky summary/CTA yang aman pada mobile.
- [ ] Multi-company, guest checkout, Midtrans, manual transfer, RajaOngkir, PPN, point, dan faktur pajak tetap berfungsi.
- [ ] Profile, order, tracking, wishlist, notification, blog, promo, dan content page ikut dimodernisasi.
- [ ] Semua halaman memiliki loading, empty, success, validation, error, disabled, dan recovery state yang relevan.
- [ ] Keyboard, focus, modal/drawer, contrast, target sentuh, reduced motion, dan zoom lolos audit.
- [ ] SEO metadata, canonical, sitemap, structured data, dan public URL tidak mengalami regression.
- [ ] Performance budget dan Web Vitals target diverifikasi menggunakan data build/release candidate.
- [ ] Manual book dan screenshot customer diperbarui setelah UI final.

## Definition of Done Program

Program modernisasi dianggap selesai ketika:

1. Sprint 0-7 dan seluruh quality gate telah ditutup.
2. Seluruh acceptance criteria global telah memiliki bukti.
3. Core flow berikut lulus pada desktop dan mobile:
   - Search -> detail -> cart -> checkout -> pembayaran.
   - Guest buy-now -> checkout -> pembayaran/bukti transfer.
   - Multi-company cart -> checkout -> transaksi terpisah.
   - Login -> order history -> tracking/invoice/faktur pajak.
   - Wishlist -> detail -> pembelian.
4. Tidak ada P0/P1 visual, accessibility, performance, atau checkout defect terbuka.
5. Owner, technical owner, dan QA menyetujui rollout.

## Keputusan yang Sudah Ditetapkan

- PRD modernisasi frontend berdiri sendiri dan tidak dicampur ke PRD faktur pajak.
- Laravel Blade dan Tailwind tetap digunakan.
- Asset customer dipindahkan ke Vite production build.
- Arah visual adalah industrial-modern.
- Plus Jakarta Sans boleh dipertahankan sebagai body font jika self-hosted.
- Search, informasi spesifikasi, dan kejelasan transaksi diprioritaskan dibanding dekorasi.
- Implementasi dilakukan per sprint; checkout tidak diubah bersamaan dengan fondasi visual.

## Open Questions yang Tidak Menghalangi Sprint 0-1

- Apakah warna aksen final mengikuti logo existing atau memakai safety orange sebagai CTA utama?
- Apakah toko memiliki daftar brand/industri yang dapat ditampilkan di homepage?
- Field spesifikasi mana yang wajib pada product card untuk setiap kategori produk?
- Apakah estimasi ongkir di detail produk boleh meminta lokasi guest sebelum checkout?
- Analytics provider apa yang disetujui untuk production?
- Apakah legal content akan tetap dikelola melalui content page admin existing?
- Apakah rollout memakai feature flag/pilot traffic atau langsung mengganti UI seluruh customer?

Pertanyaan tersebut diselesaikan pada Sprint 0 atau sebelum sprint yang bergantung padanya.
