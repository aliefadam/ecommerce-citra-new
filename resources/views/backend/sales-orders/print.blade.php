<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Order {{ $salesOrder->sales_order_no }}</title>
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
                @include('backend.partials.print-company-block', ['company' => $salesOrder->company])
            </div>
            <div class="right">
                <h1>Sales Order</h1>
                <p>{{ $salesOrder->sales_order_no }}</p>
                <p>{{ $salesOrder->created_at->translatedFormat('d M Y') }}</p>
                <p><span class="badge">{{ strtoupper(str_replace('_', ' ', $salesOrder->status)) }}</span></p>
            </div>
        </div>

        <h2 class="section-label">Kepada</h2>
        <p><strong>{{ $salesOrder->customerName() }}</strong></p>
        <p>{{ $salesOrder->user?->email ?? $salesOrder->manual_customer_email ?? '-' }}</p>
        <p>{{ $salesOrder->manual_customer_phone ?? $salesOrder->user?->phone_number ?? '-' }}</p>

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
                @foreach ($salesOrder->details as $detail)
                    <tr class="item-row">
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                        <td class="right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="summary-row">
                    <td colspan="3">Subtotal</td>
                    <td class="right">Rp {{ number_format($salesOrder->subtotal_amount, 0, ',', '.') }}</td>
                </tr>
                @include('backend.partials.financial-breakdown-print', ['document' => $salesOrder])
                <tr class="summary-row grand-total">
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td class="right total">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </main>
</body>
</html>
