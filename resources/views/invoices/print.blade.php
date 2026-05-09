<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; background: #f8fafc; color: #0f172a; }
        .page { max-width: 840px; margin: 24px auto; background: #fff; padding: 32px; border: 1px solid #e2e8f0; }
        .row { display: flex; justify-content: space-between; gap: 24px; }
        h1 { margin: 0; font-size: 28px; }
        h2 { font-size: 14px; margin: 28px 0 10px; color: #475569; text-transform: uppercase; letter-spacing: .04em; }
        p { margin: 4px 0; font-size: 14px; color: #475569; }
        .brand { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-bottom: 8px; }
        .brand-logo { width: 38px; height: 38px; border-radius: 10px; object-fit: contain; border: 1px solid #e2e8f0; padding: 4px; }
        .brand-mark { width: 38px; height: 38px; border-radius: 10px; background: #2563eb; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f8fafc; color: #475569; }
        .right { text-align: right; }
        .total { font-size: 18px; font-weight: 700; color: #2563eb; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 700; }
        .actions { max-width: 840px; margin: 24px auto 0; text-align: right; }
        button { border: 0; background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; }
        @media print { body { background: #fff; } .page { margin: 0; max-width: none; border: 0; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print / Simpan PDF</button>
    </div>
    <main class="page">
        <div class="row">
            <div>
                <h1>Invoice</h1>
                <p>{{ $transaction->invoice_no }}</p>
                <p>{{ optional($transaction->created_at)->translatedFormat('d M Y H:i') }}</p>
            </div>
            <div class="right">
                <div class="brand">
                    @if (!empty($appStoreLogoUrl))
                        <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}" class="brand-logo">
                    @else
                        <span class="brand-mark">{{ strtoupper(substr($appStoreName, 0, 1)) }}</span>
                    @endif
                    <strong>{{ $appStoreName }}</strong>
                </div>
                <p><span class="badge">{{ strtoupper($transaction->status) }}</span></p>
            </div>
        </div>

        <div class="row">
            <div>
                <h2>Customer</h2>
                <p><strong>{{ $transaction->user?->name ?? '-' }}</strong></p>
                <p>{{ $transaction->user?->email ?? '-' }}</p>
                <p>{{ $transaction->payment_method ?: '-' }}</p>
            </div>
            <div>
                <h2>Pengiriman</h2>
                <p><strong>{{ $transaction->shipping_recipient_name ?: '-' }}</strong></p>
                <p>{{ $transaction->shipping_phone ?: '-' }}</p>
                <p>{{ $transaction->shipping_address_line }}{{ $transaction->shipping_city ? ', ' . $transaction->shipping_city : '' }}{{ $transaction->shipping_province ? ', ' . $transaction->shipping_province : '' }}</p>
                <p>{{ $transaction->shipping_label ?: '-' }}{{ $transaction->tracking_number ? ' / Resi: ' . $transaction->tracking_number : '' }}</p>
            </div>
        </div>

        <h2>Produk</h2>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                        <td class="right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">Rp {{ number_format($transaction->subtotal_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ongkos Kirim</td>
                <td class="right">Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            @if ((int) $transaction->discount_amount > 0)
                <tr>
                    <td>Voucher {{ $transaction->coupon_code }}</td>
                    <td class="right">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Grand Total</strong></td>
                <td class="right total">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </main>
</body>
</html>
