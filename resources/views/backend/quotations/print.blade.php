<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_no }}</title>
    <style>
        @include('backend.partials.print-styles')
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print / Simpan PDF</button>
    </div>
    <main class="page">
        {{--
            Letterhead & info customer sengaja di luar <table class="print-doc">
            (tampil sekali saja di halaman pertama). <thead> tabel item hanya
            berisi judul kolom (Produk/Qty/Harga/Subtotal) — diuji stabil
            berulang tiap halaman cetak; menaruh letterhead di dalam <thead>
            terbukti membuat Chromium GAGAL mengulangnya begitu kontennya
            melebihi beberapa baris.
        --}}
        <div class="row">
            <div>
                @include('backend.partials.print-company-block', ['company' => $quotation->company])
            </div>
            <div class="right">
                <h1>Quotation</h1>
                <p>{{ $quotation->quotation_no }}</p>
                <p>{{ $quotation->created_at->translatedFormat('d M Y') }} &bull; Berlaku hingga {{ $quotation->valid_until->translatedFormat('d M Y') }}</p>
                <p><span class="badge">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span></p>
            </div>
        </div>

        <h2 class="section-label">Kepada</h2>
        <p><strong>{{ $quotation->customerName() }}</strong></p>
        <p>{{ $quotation->user?->email ?? $quotation->manual_customer_email ?? '-' }}</p>
        <p>{{ $quotation->manual_customer_phone ?? $quotation->user?->phone_number ?? '-' }}</p>

        <table class="print-doc">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->details as $detail)
                    <tr class="item-row">
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                        <td class="right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="summary-row">
                    <td colspan="3">Subtotal</td>
                    <td class="right">Rp {{ number_format($quotation->subtotal_amount, 0, ',', '.') }}</td>
                </tr>
                @include('backend.partials.financial-breakdown-print', ['document' => $quotation])
                <tr class="summary-row grand-total">
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td class="right total">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</td>
                </tr>

                @if ($quotation->note)
                    <tr class="notes-row">
                        <td colspan="4">
                            <h2 class="section-label">Catatan</h2>
                            <p class="terms">{{ $quotation->note }}</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </main>
</body>
</html>
