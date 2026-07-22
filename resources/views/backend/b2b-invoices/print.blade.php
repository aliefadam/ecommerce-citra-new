<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $b2bInvoice->b2b_invoice_no }}</title>
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
                @include('backend.partials.print-company-block', ['company' => $b2bInvoice->company])
            </div>
            <div class="right">
                <h1>Invoice</h1>
                <p>{{ $b2bInvoice->b2b_invoice_no }}</p>
                <p>{{ optional($b2bInvoice->issued_at)->translatedFormat('d M Y') }}</p>
                <p>Jatuh Tempo: {{ optional($b2bInvoice->due_date)->translatedFormat('d M Y') }}</p>
                <p><span class="badge">{{ strtoupper(str_replace('_', ' ', $b2bInvoice->status)) }}</span></p>
            </div>
        </div>

        <h2 class="section-label">Kepada</h2>
        <p><strong>{{ $b2bInvoice->customerName() }}</strong></p>
        <p>{{ $b2bInvoice->user?->email ?? $b2bInvoice->manual_customer_email ?? '-' }}</p>

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
                @foreach ($b2bInvoice->details as $detail)
                    <tr class="item-row">
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                        <td class="right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="summary-row">
                    <td colspan="3">Subtotal</td>
                    <td class="right">Rp {{ number_format($b2bInvoice->subtotal_amount, 0, ',', '.') }}</td>
                </tr>
                @include('backend.partials.financial-breakdown-print', ['document' => $b2bInvoice])
                <tr class="summary-row grand-total">
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td class="right total">Rp {{ number_format($b2bInvoice->grand_total, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-row">
                    <td colspan="3">Sudah Dibayar</td>
                    <td class="right">Rp {{ number_format($b2bInvoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-row">
                    <td colspan="3"><strong>Outstanding</strong></td>
                    <td class="right"><strong>Rp {{ number_format($b2bInvoice->outstanding_amount, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </main>
</body>
</html>
