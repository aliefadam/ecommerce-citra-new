# Sprint 3 — Homepage, Katalog, Search, dan Product Card

Status: **Selesai**  
Tanggal verifikasi: 27 September 2026

## Hasil

- Homepage menempatkan pencarian, kategori, produk, promo, dan trust/content dalam hierarki yang
  jelas. Produk utama sudah tampil sebelum posisi vertikal 2.400 px pada viewport mobile E2E.
- Product card homepage, kategori, dan hasil pencarian menampilkan seller, harga, stok, satuan,
  serta informasi teknis minimum dari payload server.
- Filter, sort, dan mode tampilan kategori disimpan per URL di `sessionStorage`, lalu dipulihkan
  ketika customer kembali dari detail produk.
- Filter mobile memiliki target sentuh minimum 44 px, dapat ditutup dengan Escape, memindahkan
  fokus ke kontrol pertama, dan mengembalikan fokus ke tombol pembuka.
- Promo yang telah berakhir tidak menampilkan countdown nol palsu. Campaign disembunyikan dan
  diganti pesan `Promo telah berakhir.`; campaign tidak aktif juga tetap dikecualikan server.
- Homepage, pencarian, dan flash sale memiliki empty state yang profesional serta actionable.
- Istilah customer-facing memakai Bahasa Indonesia, termasuk `Kampanye Promo` dan
  `Kampanye aktif`.
- Widget Tawk hanya dimuat pada environment production sehingga pengujian lokal tidak menghasilkan
  error lintas origin tanpa mengubah perilaku production.
- Tidak ada route, kontrak API, atau business rule yang berubah. Harga dan stok tetap berasal dari
  server; tidak ada query Eloquent baru di Blade.

## Bukti acceptance criteria

| Acceptance criteria | Bukti |
| --- | --- |
| Produk utama terlihat lebih cepat pada mobile | Browser test memverifikasi produk pertama berada sebelum `y = 2400`; screenshot homepage mobile tersedia. |
| Card memuat seller, harga, stok, satuan, spesifikasi | Feature test memverifikasi payload homepage/kategori/search; browser test memverifikasi konten kartu. |
| Istilah Bahasa Indonesia konsisten | Label promo diperbarui dan diverifikasi feature test. |
| Filter/sort bertahan setelah kembali dari detail | Browser test memilih filter/sort, membuka detail, kembali, lalu memverifikasi state pulih. |
| Promo berakhir tidak menyisakan countdown palsu | Feature test dan browser test memverifikasi campaign kadaluarsa disembunyikan serta pesan akhir tampil. |
| Empty catalog tetap profesional dan actionable | Feature test homepage kosong serta browser test pencarian kosong memverifikasi CTA pemulihan. |

## Responsive, visual, dan accessibility review

- Desktop 1440 × 1000: [homepage](screenshots/sprint-3-home-desktop.png),
  [kategori](screenshots/sprint-3-category-desktop.png), dan
  [hasil pencarian](screenshots/sprint-3-search-desktop.png).
- Mobile 390 × 844: [homepage](screenshots/sprint-3-home-mobile.png) dan
  [filter kategori](screenshots/sprint-3-category-mobile.png).
- Browser test desktop dan mobile memastikan tidak ada horizontal overflow.
- Tombol wishlist dan pemicu filter mobile diverifikasi minimum 44 × 44 px.
- Focus entry, Escape, dan focus restoration pada filter drawer diverifikasi di browser.
- Tidak ditemukan page error atau console error pada skenario katalog desktop/mobile.

## Perbandingan query dan HTML

Pengukuran memakai seed dan `FrontendCustomerBaselineTest` yang sama. Query homepage dan kategori
tidak bertambah. HTML bertambah secara terukur karena card membawa stok/satuan dan halaman kategori
menyimpan state filter yang kini dapat dipulihkan.

| Halaman | Query Sprint 2 | Query Sprint 3 | HTML Sprint 2 | HTML Sprint 3 |
| --- | ---: | ---: | ---: | ---: |
| Homepage | 17 | 17 | 136.933 B | 165.758 B |
| Kategori | 13 | 13 | 118.542 B | 137.040 B |

Bundle customer tetap di bawah performance budget:

- CSS: 217,51 KB raw / 34,82 KB gzip (budget 120 KB gzip).
- JavaScript: 14,77 KB raw / 5,22 KB gzip (budget 220 KB gzip).

## SEO dan pengujian

- Homepage tetap indexable dan memiliki canonical; hasil pencarian tetap `noindex`.
- `StorefrontCatalogExperienceTest`: 3 test / 20 assertion lulus.
- Feature terkait katalog, design system, performance, dan SEO: 19 test / 134 assertion lulus.
- `FrontendCustomerBaselineTest`: 1 test / 8 assertion lulus.
- Full backend regression: 217 test / 1.310 assertion lulus.
- Full browser regression: 23 skenario lulus, termasuk 2 skenario katalog desktop/mobile baru.
- `npm run build`: lulus.
- Blade compile melalui `php artisan view:cache`: lulus.

## Handoff ke Sprint 4

Fondasi katalog dan product card sudah ditutup. Pekerjaan berikutnya adalah menutup seluruh
acceptance criteria detail produk dan cart dengan bukti visual, accessibility, serta regression yang
sudah mulai tersedia pada browser test product-to-cart.
