# Sprint 2 — Design System dan Global Shell

Tanggal verifikasi: 14 September 2026

## Hasil

- Token industrial-modern terpusat di `resources/css/app.css`: warna, typography, radius, elevation,
  motion, container, serta layer navbar/dropdown/sticky/chat/toast/drawer/modal.
- Shell customer memakai graphite dan warm-white sebagai dasar, cobalt untuk fokus/informasi, serta
  safety-orange untuk aksi transaksi.
- Announcement, navbar desktop/mobile, mega menu, mobile task navigation, footer, toast region, dan
  tombol WhatsApp telah diselaraskan.
- Mega menu mendukung Enter/Space dari tombol native, Arrow Up/Down, Home, End, dan Escape.
- Dropdown/drawer/modal memiliki state `aria-expanded`, Escape, dan focus restoration. Modal/drawer
  foundation juga memiliki focus trap.
- Bottom navigation selalu menampilkan lima label sehingga active state tidak mengubah lebar item.
- Safe-area diterapkan pada bottom navigation; toast berada di atas task navigation; Tawk mobile
  tetap memiliki offset 86 px; WhatsApp desktop berada di sisi kiri.
- Link legal memakai URL permanen. Empat halaman penting memiliki fallback yang dapat dibuka dan
  diindeks bila konten CMS belum diterbitkan.
- Tidak ada perubahan business rule, kontrak API, atau URL publik existing.

## Katalog komponen

Semua komponen berada di `resources/views/components/ui`.

| Komponen | Tag Blade | State/varian utama |
| --- | --- | --- |
| Button | `<x-ui.button>` | primary, secondary, outline, text, danger, loading, disabled |
| Field dan select | `<x-ui.field>`, `<x-ui.select>` | hint, validation error, `aria-invalid` |
| Checkbox/radio | `<x-ui.check>` | checkbox atau radio, target sentuh 44 px |
| Quantity | `<x-ui.quantity>` | min, max, increment/decrement |
| Badge | `<x-ui.badge>` | neutral, info, success, warning, danger |
| Price | `<x-ui.price>` | prefix, harga utama, harga coret |
| State dan alert | `<x-ui.state>`, `<x-ui.alert>` | empty/action serta info/success/warning/danger |
| Skeleton | `<x-ui.skeleton>` | loading dengan reduced-motion fallback |
| Modal dan drawer | `<x-ui.modal>`, `<x-ui.drawer>` | close, Escape, focus trap/restoration |
| Dropdown | `<x-ui.dropdown>` | expanded/collapsed, outside click, Escape |
| Tabs dan accordion | `<x-ui.tabs>`, `<x-ui.tab>`, `<x-ui.accordion>` | selected/open semantics |
| Pagination | `<x-ui.pagination>` | previous/next serta disabled state |
| Product card | `<x-ui.product-card>` | image fallback, seller, SKU, price, stock, unit |
| Section/store label | `<x-ui.section-heading>`, `<x-ui.store-label>` | hierarchy dan seller identity |

## Responsive dan accessibility review

- Screenshot desktop 1440 × 1000: [shell dan mega menu](screenshots/sprint-2-shell-desktop.png).
- Screenshot mobile 390 × 844: [search dan bottom navigation](screenshots/sprint-2-shell-mobile.png).
- Focus-visible global menggunakan outline cobalt 3 px dengan offset 3 px.
- Aksi shell dan form minimum 44 px; input mobile minimum 16 px.
- Status penting memiliki teks/label dan tidak hanya mengandalkan warna.
- `prefers-reduced-motion: reduce` menonaktifkan motion non-esensial.
- Tidak ditemukan error JavaScript pada browser test desktop/mobile.

## Perbandingan query dan HTML

Pengukuran memakai seed dan `FrontendCustomerBaselineTest` yang sama. Query tetap atau turun karena
redesign tidak menambah akses data dari Blade. Pengurangan HTML berasal dari penghapusan style,
markup modal footer, dan script navbar inline yang duplikatif.

| Halaman | Query Sprint 1 | Query Sprint 2 | HTML Sprint 1 | HTML Sprint 2 |
| --- | ---: | ---: | ---: | ---: |
| Homepage | 17 | 17 | 195,211 B | 136,933 B |
| Kategori | 13 | 13 | 176,775 B | 118,542 B |
| Detail produk | 39 | 39 | 175,883 B | 117,668 B |
| Cart | 10 | 9 | 115,885 B | 54,825 B |
| Checkout | 12 | 11 | 194,304 B | 133,285 B |
| Profil | 10 | 9 | 252,393 B | 191,338 B |

## Build dan pengujian

- CSS customer: 186.64 KB raw / 28.31 KB gzip, masih di bawah budget 120 KB gzip.
- JavaScript customer: 10.69 KB raw / 3.89 KB gzip, masih di bawah budget 220 KB gzip.
- `npm run build`: lulus; asset CSS/JS dan font ter-versioning.
- `StorefrontDesignSystemTest`: 4 test / 27 assertion lulus.
- Full unit/feature suite: 164 test / 958 assertion lulus.
- `StorefrontPerformanceFoundationTest`: 4 test / 30 assertion lulus.
- `SeoFoundationTest`: 4 test / 18 assertion lulus.
- `FrontendCustomerBaselineTest`: 1 test / 8 assertion lulus.
- Full browser regression: 11 skenario lulus di Chrome headless, termasuk shell, autocomplete,
  guest/member checkout, fulfillment, negative path, upload, dan admin responsive.
- Blade compile melalui `php artisan view:cache`: lulus.

## Handoff ke Sprint 3

Homepage, katalog, search result, dan product card lama belum dimigrasikan penuh karena itu scope
Sprint 3. Gunakan token dan komponen UI dari sprint ini; jangan membuat varian tombol, toast, atau
product card lokal baru pada halaman tersebut.
