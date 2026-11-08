# Prototype Konseptual Mobile Shell

Status: non-executable; tidak ada source code UI yang dibuat.

## Layout Shell

```text
┌────────────────────────────────────┐
│ safe-area top                      │
│ Context header / back / action     │
│ Search (Home/Katalog)              │
├────────────────────────────────────┤
│                                    │
│  Route content                     │
│  - loading skeleton                │
│  - content                         │
│  - empty/error/retry               │
│                                    │
│  padding mengikuti active CTA      │
│                                    │
├────────────────────────────────────┤
│ contextual sticky action (opsional)│
├────────────────────────────────────┤
│ Home Katalog Cart Transaksi Profil │
│ safe-area bottom                   │
└────────────────────────────────────┘
```

Pada viewport di atas 767 px, shell tetap maksimum 430 px dan dipusatkan. Area di luar shell memakai
background netral; tidak berubah menjadi layout desktop baru.

## Navigasi Utama

| Tab | Route | Guest behavior |
| --- | --- | --- |
| Home | `/app` | Boleh |
| Katalog | `/app/categories` | Boleh |
| Keranjang | `/app/cart` | Arahkan login atau guest buy-now context pada parity awal |
| Transaksi | `/app/orders` | Login dengan return URL |
| Profil | `/app/account` | Login dengan return URL |

Badge cart maksimal `99+`. Active state memakai icon, label, dan visual state; tidak hanya warna.

## Route State

Setiap layar wajib memiliki:

- route-level loading;
- empty state;
- recoverable error dan retry;
- offline state bila request gagal karena koneksi;
- session-expired state untuk private route;
- focus target setelah navigation;
- title dan history entry yang benar.

Bottom navigation tidak tampil bersamaan dengan fullscreen modal/payment provider. Sticky purchase,
checkout CTA, keyboard, toast, chat, install prompt, modal, dan bottom sheet dikelola satu layer store.

## Home

Urutan awal:

1. Delivery/value proposition ringkas.
2. Search bar yang langsung aktif.
3. Shortcut kategori.
4. Produk unggulan/terbaru.
5. Promo aktif.
6. Trust/support.

Produk pertama harus terlihat tanpa homepage menjadi kumpulan banner panjang. Promo aplikasi/install
tidak boleh menutup search pada kunjungan pertama.

## Search dan Katalog

```text
[ Back ] [ Cari nama, SKU, ukuran... ]
[ Active filter chips                ]
[ Filter ] [ Sort ]       124 hasil
--------------------------------------
[ Product card ][ Product card ]
[ Product card ][ Product card ]
```

- Query, sort, dan filter disimpan di URL.
- Filter menggunakan bottom sheet dengan `Terapkan` dan `Reset`.
- Kembali dari detail memulihkan posisi scroll dan loaded page.
- Search suggestion minimal dua karakter, debounce, cancellation, keyboard, dan screen-reader state.

## Detail Produk

```text
[Back]                    [Share][♡]
[ Gallery / swipe / indicator       ]
Seller · SKU · stock
Product title
Price / unit
[ Variant selectors                 ]
Specification summary
Shipping / return / trust
--------------------------------------
[ price ] [Tambah ke Cart] [Beli]
```

Sticky action menggantikan, bukan menumpuk, bottom navigation. Variant drawer menghormati keyboard
dan safe area. CTA disabled selalu memiliki alasan.

## Cart

- Group per company/seller.
- Item memperlihatkan variant, unit, authoritative price, quantity, stock state, dan subtotal.
- Optimistic quantity memiliki disabled/pending state dan rollback.
- Item invalid tetap terlihat dengan alasan dan recovery action.
- Summary/checkout CTA sticky tanpa menutup item terakhir.

Parity awal mengikuti member cart existing. Guest tetap dapat memakai `Beli Sekarang`; guest cart
penuh memerlukan keputusan data model terpisah.

## Checkout

```text
1 Data penerima/alamat
2 Pengiriman per perusahaan
3 Voucher, point, faktur pajak
4 Metode pembayaran
5 Review dan konfirmasi
```

- Hanya satu tahap utama terbuka pada satu waktu.
- Summary dapat dibuka/tutup dan total berasal dari preview server.
- Perubahan harga/stok/ongkir menghasilkan diff, bukan silent overwrite.
- CTA submit memakai satu idempotency key sampai request mendapat hasil final.
- Keyboard tidak menutup field error atau CTA.

## Payment dan Recovery

- Setiap order per company memiliki card/status sendiri.
- Polling berhenti pada terminal state dan saat tab background sesuai interval policy.
- Menutup popup/provider menampilkan `Coba Bayar Lagi`, `Ganti Metode`, atau `Lihat Status`.
- Manual transfer menampilkan rekening company yang benar dan upload proof.
- Status dari browser hanya display; webhook/server tetap authority.

## Account dan Orders

Profile tidak menjadi satu layar panjang. Route terpisah digunakan untuk:

- biodata;
- alamat;
- keamanan/password;
- orders;
- wishlist;
- notification;
- tax profile.

Order card hanya menampilkan aksi yang valid terhadap state: bayar, upload proof, tracking, invoice,
faktur pajak, selesai, retur, review, atau beli lagi.

## Layer Map

| Layer | Prioritas relatif |
| --- | ---: |
| Route content | 0 |
| Sticky header | 10 |
| Bottom navigation/context CTA | 20 |
| Toast/chat | 30 |
| Drawer/backdrop | 40 |
| Modal/critical confirmation | 50 |
| Provider handoff/system prompt | External |

Angka final menjadi design token, bukan z-index ad-hoc per halaman.

## Accessibility Behavior

- Route navigation memindahkan focus ke `<h1>`/main region, kecuali Back yang memulihkan trigger.
- Drawer/modal memakai focus trap, Escape, close button, dan restoration.
- Live region membedakan status informatif dari error kritis.
- Swipe gallery tetap memiliki tombol dan indikator yang dapat diakses.
- Skeleton tidak dibacakan sebagai konten dan reduced motion mematikan motion non-esensial.
- Semua aksi minimum 44×44 px; form 16 px; zoom 200% tidak kehilangan fungsi.

## Prototype Acceptance untuk Fase 1

- Direct load dan refresh pada tiga nested route tidak 404.
- Back/Forward memulihkan route dan focus.
- Shell 360 px tidak overflow dan shell desktop maksimum 430 px.
- Bottom navigation, sticky CTA, keyboard, toast, dan drawer tidak overlap.
- Feature flag dapat menonaktifkan entry SPA tanpa mengubah desktop.
- Manifest/SW mobile hanya menguasai scope `/app/`.
- Tidak ada cart/checkout/payment mutation nyata pada prototype foundation.
