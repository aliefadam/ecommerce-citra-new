# PRD: PPN Checkout End-to-End

## Ringkasan

Fitur ini menambahkan perhitungan PPN pada checkout ecommerce BOQ. Harga produk dianggap belum termasuk PPN. PPN ditambahkan saat checkout berdasarkan pengaturan toko yang dapat diatur administrator.

Keputusan utama:

- Default PPN: 11%.
- Harga item: belum termasuk PPN.
- PPN dihitung dari subtotal produk setelah diskon.
- Ongkir tidak dikenakan PPN.
- Nilai PPN harus disimpan sebagai snapshot pada transaksi agar transaksi lama tidak berubah saat setting PPN diubah.

## Tujuan

- Admin dapat mengaktifkan/nonaktifkan PPN dan mengatur persentasenya.
- Customer melihat rincian PPN secara jelas sebelum membayar.
- Semua jalur checkout menghasilkan total yang konsisten.
- Invoice, riwayat pesanan, dan dashboard admin menampilkan nilai PPN.
- Perubahan setting PPN tidak mengubah histori transaksi lama.

## Non-Tujuan

- Tidak membuat integrasi e-Faktur.
- Tidak membuat nomor seri faktur pajak.
- Tidak membuat perhitungan pajak berbeda per kategori produk.
- Tidak mengenakan PPN pada ongkir.

## Definisi Perhitungan

Rumus:

```text
subtotal_produk = total harga item sebelum diskon
discount_amount = nilai diskon voucher
taxable_amount = max(0, subtotal_produk - discount_amount)
tax_amount = round(taxable_amount * tax_rate / 100)
grand_total = taxable_amount + tax_amount + shipping_cost
```

Contoh:

```text
Subtotal produk   Rp 1.000.000
Diskon voucher    Rp   100.000
DPP               Rp   900.000
PPN 11%           Rp    99.000
Ongkir            Rp    25.000
Grand total       Rp 1.024.000
```

Jika PPN nonaktif:

```text
tax_rate = 0
tax_amount = 0
grand_total = taxable_amount + shipping_cost
```

## User Stories

### Admin

- Sebagai admin, saya bisa mengaktifkan atau menonaktifkan PPN.
- Sebagai admin, saya bisa mengubah persentase PPN, default 11%.
- Sebagai admin, saya bisa melihat nilai PPN pada transaksi yang sudah dibuat.

### Customer

- Sebagai customer, saya bisa melihat rincian PPN di checkout sebelum melakukan pembayaran.
- Sebagai customer, saya bisa melihat nilai PPN di halaman menunggu pembayaran dan riwayat pesanan.
- Sebagai customer, saya mendapat total pembayaran yang sama antara checkout, invoice, dan instruksi pembayaran.

## Scope Functional

### 1. Pengaturan Toko

Tambahkan setting:

- `tax_enabled`: boolean, default `true`.
- `tax_name`: string, default `PPN`.
- `tax_rate`: decimal, default `11.00`.

Validasi:

- `tax_enabled`: boolean.
- `tax_name`: required jika PPN aktif, maksimal 30 karakter.
- `tax_rate`: numeric, minimum 0, maksimum 100, maksimal 2 digit desimal.

UI admin:

- Tambahkan section "Pajak / PPN" di pengaturan toko.
- Tampilkan toggle aktif/nonaktif.
- Tampilkan input persentase.
- Tampilkan helper text: "PPN dihitung dari subtotal produk setelah diskon. Ongkir tidak dikenakan PPN."

### 2. Checkout Frontend

Tampilan ringkasan checkout harus menampilkan:

- Subtotal produk.
- Diskon voucher jika ada.
- DPP / subtotal kena pajak, opsional jika diperlukan untuk kejelasan.
- PPN 11% atau sesuai setting.
- Ongkir.
- Grand total.

PPN harus ter-update saat:

- Item berubah.
- Quantity berubah.
- Voucher diterapkan/dihapus.
- Ongkir berubah.
- Checkout redeem point berubah.

### 3. Checkout Backend

Backend harus menghitung ulang PPN, jangan percaya nilai dari frontend.

Jalur yang perlu tercakup:

- Checkout cart selected.
- Buy now.
- Redeem point checkout.
- Manual transfer checkout.
- Midtrans checkout.

Data request dari frontend boleh membawa nilai PPN untuk preview, tetapi nilai final harus dihitung ulang dari server berdasarkan setting saat transaksi dibuat.

### 4. Database Transaksi

Tambahkan kolom pada tabel `transactions`:

- `tax_name` nullable string.
- `tax_rate` decimal, default 0.
- `taxable_amount` integer, default 0.
- `tax_amount` integer, default 0.

`grand_total` harus sudah termasuk PPN.

Snapshot rules:

- Saat transaksi dibuat, simpan nama/rate/amount PPN saat itu.
- Jika admin mengubah PPN besok, transaksi lama tetap memakai snapshot lama.

### 5. Payment Payload

Manual transfer:

- Instruksi total pembayaran harus memakai `grand_total` termasuk PPN.
- Halaman waiting payment menampilkan rincian PPN.

Midtrans:

- Gross amount harus sama dengan `grand_total` termasuk PPN.
- Item details perlu disusun agar total item details sesuai gross amount.
- Opsi paling aman: tambahkan line item `PPN 11%` sebagai item detail positif.
- Diskon tetap sebagai line item negatif jika sekarang sudah dipakai.
- Ongkir tetap line item terpisah.

### 6. Invoice dan Email

Invoice email harus menampilkan:

- Subtotal produk.
- Diskon.
- PPN.
- Ongkir.
- Grand total.

Jika email gagal terkirim, checkout tetap berhasil dan kegagalan dicatat ke log.

### 7. Admin Transaksi

Halaman detail transaksi admin harus menampilkan:

- Taxable amount.
- Tax name dan tax rate.
- Tax amount.
- Grand total.

Listing transaksi boleh tetap menampilkan grand total saja, tetapi detail wajib lengkap.

### 8. Riwayat Customer

Riwayat/detail pesanan customer harus menampilkan PPN agar konsisten dengan checkout dan invoice.

### 9. Laporan

Minimal:

- Laporan penjualan tetap memakai `grand_total`.
- Jika ada breakdown revenue, pertimbangkan menampilkan:
  - subtotal produk,
  - diskon,
  - PPN,
  - ongkir,
  - grand total.

## Acceptance Criteria

- Admin dapat menyimpan setting PPN 11% aktif.
- Checkout menampilkan PPN 11% dari subtotal setelah diskon.
- Ongkir tidak masuk dasar perhitungan PPN.
- Grand total checkout sama dengan grand total transaksi tersimpan.
- Manual transfer berhasil membuat transaksi dengan `tax_amount`.
- Midtrans gross amount sudah termasuk PPN.
- Invoice email dan waiting payment menampilkan PPN.
- Transaksi lama tidak berubah ketika admin mengubah rate PPN.
- Jika PPN dimatikan, checkout tidak menampilkan/menambahkan PPN.
- Jika SMTP gagal, checkout tetap berhasil.

## Edge Cases

- Diskon lebih besar dari subtotal: taxable amount menjadi 0.
- PPN rate 0: tax amount 0.
- Ongkir 0: PPN tetap dihitung dari produk setelah diskon.
- Redeem item dengan harga 0 dan redeem points: PPN 0 jika taxable amount 0.
- Rounding: gunakan `round()` ke integer rupiah.
- Perbedaan frontend/backend: backend menjadi sumber kebenaran.

## Implementation Steps

### Phase 1: Data dan Settings

1. Tambahkan migration kolom pajak pada `transactions`.
2. Tambahkan default setting pajak pada `StoreSetting`.
3. Tambahkan form setting admin untuk `tax_enabled`, `tax_name`, dan `tax_rate`.
4. Tambahkan validasi penyimpanan setting.

### Phase 2: Tax Calculator

1. Buat helper/service kecil untuk menghitung pajak checkout.
2. Input service:
   - subtotal,
   - discount amount,
   - shipping cost,
   - tax settings.
3. Output service:
   - taxable amount,
   - tax name,
   - tax rate,
   - tax amount,
   - grand total.
4. Gunakan service yang sama di manual transfer dan Midtrans.

### Phase 3: Checkout UI

1. Tambahkan data tax settings ke halaman checkout.
2. Update JavaScript perhitungan ringkasan checkout.
3. Tambahkan baris PPN pada ringkasan.
4. Pastikan apply/remove voucher menghitung ulang PPN.
5. Pastikan perubahan ongkir tidak memengaruhi PPN.

### Phase 4: Checkout Backend

1. Update `ManualPaymentController` agar menghitung dan menyimpan PPN.
2. Update `MidtransController` agar gross amount termasuk PPN.
3. Update session/payment payload untuk membawa snapshot PPN.
4. Pastikan penghapusan cart dan reservasi point tetap berjalan seperti sekarang.

### Phase 5: Output Transaksi

1. Update halaman waiting payment.
2. Update invoice email.
3. Update detail transaksi admin.
4. Update profil/riwayat pesanan customer.
5. Update print invoice jika ada.

### Phase 6: Testing

1. Test manual transfer tanpa diskon.
2. Test manual transfer dengan diskon.
3. Test Midtrans tanpa diskon.
4. Test Midtrans dengan diskon.
5. Test ongkir berubah, PPN tetap dari produk setelah diskon.
6. Test PPN nonaktif.
7. Test rate diubah setelah transaksi lama dibuat.
8. Test SMTP gagal tidak menggagalkan checkout.

## Open Questions

- Apakah label "DPP" perlu ditampilkan ke customer atau cukup "PPN 11%"?
- Apakah laporan perlu breakdown PPN pada fase pertama atau cukup di detail transaksi?
- Apakah invoice print perlu format pajak yang lebih formal untuk kebutuhan akuntansi?

