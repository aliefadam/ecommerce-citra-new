{{-- CSS bersama untuk halaman cetak Quotation/Sales Order/Invoice/Proforma Invoice. --}}
* { box-sizing: border-box; font-family: Arial, sans-serif; }
body { margin: 0; background: #f8fafc; color: #0f172a; }
.page { max-width: 900px; margin: 24px auto; background: #fff; padding: 32px; border: 1px solid #e2e8f0; }
.row { display: flex; justify-content: space-between; gap: 24px; }
h1 { margin: 0; font-size: 24px; letter-spacing: .02em; }
p { margin: 3px 0; font-size: 13px; color: #475569; }
.right { text-align: right; }
.total { font-size: 17px; font-weight: 700; color: #2563eb; }
.badge { display: inline-block; padding: 5px 10px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 11px; font-weight: 700; }
.actions { max-width: 900px; margin: 24px auto 0; text-align: right; }
button { border: 0; background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; }
.terms { white-space: pre-line; }
h2.section-label { font-size: 12px; margin: 28px 0 8px; color: #475569; text-transform: uppercase; letter-spacing: .04em; }

/* Identitas perusahaan (logo + alamat), lihat partials.print-company-block. Tampil sekali saja di atas halaman pertama. */
.print-company { display: flex; align-items: flex-start; gap: 10px; }
.print-company-logo { width: 56px; height: 56px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
.brand-mark { width: 44px; height: 44px; border-radius: 10px; background: #2563eb; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
.print-company-info strong { display: block; font-size: 15px; color: #0f172a; }
.print-company-info p { font-size: 11px; margin: 2px 0; max-width: 320px; }

/*
 * Baris item + ringkasan total dalam satu tabel dengan <thead> berisi judul
 * kolom saja (Produk/Qty/Harga/Subtotal) — diuji stabil berulang di setiap
 * halaman cetak (Chromium gagal mengulang <thead> jika berisi konten
 * letterhead yang lebih tinggi/banyak baris, jadi letterhead sengaja
 * ditaruh di luar tabel ini, lihat catatan di quotations/print.blade.php).
 */
table.print-doc { width: 100%; border-collapse: collapse; margin-top: 20px; }
table.print-doc thead { display: table-header-group; }
table.print-doc thead th { background: #f8fafc; color: #475569; border-bottom: 2px solid #cbd5e1; border-top: 1px solid #cbd5e1; padding: 10px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; text-align: left; }
table.print-doc thead th.right { text-align: right; }
table.print-doc tbody td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
table.print-doc tbody tr.item-row { page-break-inside: avoid; }
table.print-doc tbody tr.summary-row td { border-bottom: 0; padding-top: 6px; padding-bottom: 6px; }
table.print-doc tbody tr.grand-total td { border-top: 2px solid #0f172a; padding-top: 10px; }
table.print-doc tbody tr.notes-row td { border-bottom: 0; padding-top: 20px; }

@page { size: A4; margin: 14mm 12mm; }
@media print {
    body { background: #fff; }
    .page { margin: 0; max-width: none; padding: 0; border: 0; }
    .actions { display: none; }
    table.print-doc tbody tr.item-row { break-inside: avoid; }
}
