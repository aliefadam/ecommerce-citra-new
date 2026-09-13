# Sprint 1 — Asset Pipeline dan Performance Foundation

Tanggal verifikasi: 13 September 2026

## Hasil

- Layout customer, halaman autentikasi, dan shell admin memakai asset build lokal.
- Tailwind Browser CDN, Google Fonts, Flaticon CDN, Remix Icon CDN, serta Tom Select CDN telah
  dihapus dari alur customer.
- Plus Jakarta Sans, Barlow Condensed, Flaticon UIcons, dan Remix Icon disajikan lokal.
- Chat Tawk dimuat ketika browser idle, dengan fallback setelah event `load`.
- Navbar tidak lagi mengambil dan menyerialisasi 60 produk pada setiap request.
- Category tree disiapkan `StorefrontNavigationService`, disimpan dalam cache selama enam jam, dan
  otomatis dihapus saat kategori utama/detail berubah.
- Endpoint `GET /pencarian/saran?q=...` mengembalikan maksimal delapan produk storefront aktif dan
  dapat mencari nama, SKU, kategori, varian, dan atribut.
- Autocomplete desktop/mobile memiliki minimum dua karakter, debounce 250 ms, pembatalan request
  lama, loading, empty/error state, serta navigasi Arrow Up/Down, Enter, dan Escape.

## Perbandingan query dan HTML

Pengukuran memakai seeder serta mekanisme yang sama dengan Sprint 0 melalui
`FrontendCustomerBaselineTest`.

| Halaman | Query Sprint 0 | Query Sprint 1 | Perubahan | HTML Sprint 0 | HTML Sprint 1 |
| --- | ---: | ---: | ---: | ---: | ---: |
| Homepage | 21 | 17 | -19.0% | 208,846 B | 195,211 B |
| Kategori | 19 | 13 | -31.6% | 190,410 B | 176,775 B |
| Detail produk | 45 | 39 | -13.3% | 189,518 B | 175,883 B |
| Cart | 17 | 10 | -41.2% | 129,418 B | 115,885 B |
| Checkout | 19 | 12 | -36.8% | 207,837 B | 194,304 B |
| Profil | 17 | 10 | -41.2% | 266,026 B | 252,393 B |

Duplicate query turun dari 4 menjadi 1 pada homepage dan dari 5 menjadi 0 pada kategori. Penurunan
HTML sekitar 13.5 KB per halaman berasal dari penghapusan payload suggestion statis di navbar.

## Build

- CSS customer: 172.89 KB raw / 25.27 KB gzip, sebelumnya 176.81 KB / 25.51 KB gzip.
- JavaScript customer autocomplete: 3.74 KB raw / 1.68 KB gzip.
- Vite menghasilkan nama asset ber-hash dan manifest produksi.
- Font dan icon font memiliki file lokal ber-hash.

## Bukti pengujian

- `npm run build`: lulus.
- Feature suite: 160 test, 931 assertion lulus.
- Test fondasi Sprint 1: asset lokal, endpoint suggestion, filter produk nonaktif, dan invalidasi
  category cache lulus.
- SEO foundation: metadata index/noindex dan sitemap lulus.
- Browser autocomplete: async result, minimum input, keyboard navigation, empty state, dan Escape
  lulus pada Chrome headless.
- Browser regression lain (responsive admin, table, upload/dropzone, guest checkout positif/negatif,
  dan fulfillment member) lulus. Timeout fulfillment dibuat 300 detik karena runtime Windows lokal
  sempat melambat; skenario tersebut lulus saat dijalankan ulang terpisah.

## Catatan deployment

Server produksi wajib menjalankan `npm ci && npm run build` sebelum cache Laravel dibuat. Folder
`public/build` harus ikut tersedia pada release. Setelah deployment jalankan `php artisan optimize`
agar manifest dan route terbaru dipakai.
