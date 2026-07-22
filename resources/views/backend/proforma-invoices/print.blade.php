<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma Invoice {{ $proformaInvoice->proforma_invoice_no }}</title>
    <style>
        @include('backend.partials.print-styles')
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print / Simpan PDF</button>
    </div>
    <main class="page">
        <div class="row">
            <div>
                @include('backend.partials.print-company-block', ['company' => $proformaInvoice->company])
            </div>
            <div class="right">
                <h1>Proforma Invoice</h1>
                <p>{{ $proformaInvoice->proforma_invoice_no }}</p>
                <p>{{ optional($proformaInvoice->issued_at)->translatedFormat('d M Y') }}</p>
                <p><span class="badge">{{ strtoupper(str_replace('_', ' ', $proformaInvoice->status)) }}</span></p>
            </div>
        </div>

        <h2 class="section-label">Kepada</h2>
        <p><strong>{{ $proformaInvoice->customerName() }}</strong></p>
        <p>{{ $proformaInvoice->user?->email ?? $proformaInvoice->manual_customer_email ?? '-' }}</p>

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
                @foreach ($proformaInvoice->details as $detail)
                    <tr class="item-row">
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                        <td class="right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="summary-row">
                    <td colspan="3">Subtotal</td>
                    <td class="right">Rp {{ number_format($proformaInvoice->subtotal_amount, 0, ',', '.') }}</td>
                </tr>
                @include('backend.partials.financial-breakdown-print', ['document' => $proformaInvoice])
                <tr class="summary-row grand-total">
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td class="right total">Rp {{ number_format($proformaInvoice->grand_total, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-row">
                    <td colspan="3">Sudah Dibayar</td>
                    <td class="right">Rp {{ number_format($proformaInvoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-row">
                    <td colspan="3"><strong>Outstanding</strong></td>
                    <td class="right"><strong>Rp {{ number_format($proformaInvoice->outstanding_amount, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </main>
</body>
</html>
