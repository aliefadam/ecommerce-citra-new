<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Resi {{ $transaction->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; background: #f1f5f9; color: #0f172a; }
        .actions { width: 100mm; margin: 18px auto 10px; text-align: right; }
        button { border: 0; background: #2563eb; color: #fff; padding: 9px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .label { width: 100mm; min-height: 148mm; margin: 0 auto 24px; background: #fff; border: 1px solid #cbd5e1; padding: 8mm; }
        .top { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 10px; }
        .brand { font-size: 18px; font-weight: 800; }
        .muted { color: #64748b; font-size: 11px; }
        .code { font-size: 12px; font-weight: 700; text-align: right; }
        .section { border-bottom: 1px dashed #94a3b8; padding: 12px 0; }
        .section:last-child { border-bottom: 0; }
        .title { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
        .name { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
        .line { font-size: 13px; line-height: 1.45; margin: 2px 0; }
        .big-resi { border: 2px solid #0f172a; padding: 10px; text-align: center; margin-top: 8px; }
        .big-resi span { display: block; font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }
        .big-resi strong { display: block; font-size: 22px; letter-spacing: .05em; margin-top: 3px; word-break: break-all; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        td { font-size: 12px; padding: 4px 0; vertical-align: top; }
        td:last-child { text-align: right; white-space: nowrap; padding-left: 8px; }
        .footer { display: flex; justify-content: space-between; gap: 10px; font-size: 11px; color: #475569; margin-top: 12px; }
        @page { size: 100mm 148mm; margin: 0; }
        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .label { width: 100mm; min-height: 148mm; margin: 0; border: 0; page-break-after: always; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print Resi</button>
    </div>

    <main class="label">
        <div class="top">
            <div>
                <div class="brand">{{ $appStoreName ?? 'Ecommerce Citra' }}</div>
                <div class="muted">Label pengiriman</div>
            </div>
            <div class="code">
                <div>{{ $transaction->invoice_no }}</div>
                <div class="muted">{{ $transaction->order_id }}</div>
            </div>
        </div>

        <section class="section">
            <div class="title">Penerima</div>
            <div class="name">{{ $transaction->shipping_recipient_name ?: ($transaction->user?->name ?? '-') }}</div>
            <p class="line">{{ $transaction->shipping_phone ?: '-' }}</p>
            <p class="line">
                {{ $transaction->shipping_address_line ?: '-' }}{{ $transaction->shipping_city ? ', ' . $transaction->shipping_city : '' }}{{ $transaction->shipping_province ? ', ' . $transaction->shipping_province : '' }}{{ $transaction->shipping_postal_code ? ' ' . $transaction->shipping_postal_code : '' }}
            </p>
        </section>

        <section class="section">
            <div class="title">Pengiriman</div>
            <p class="line"><strong>Kurir:</strong> {{ $transaction->shipping_label ?: '-' }}</p>
            <div class="big-resi">
                <span>Nomor Resi</span>
                <strong>{{ $transaction->tracking_number ?: '-' }}</strong>
            </div>
        </section>

        <section class="section">
            <div class="title">Pengirim</div>
            <p class="line"><strong>{{ $appStoreName ?? 'Ecommerce Citra' }}</strong></p>
            @if ($storeLocation)
                <p class="line">{{ $storeLocation->label ?: 'Lokasi Toko' }}</p>
                <p class="line">{{ $storeLocation->city_name }}{{ $storeLocation->province_name ? ', ' . $storeLocation->province_name : '' }}</p>
            @else
                <p class="line">Alamat toko belum diatur.</p>
            @endif
        </section>

        <section class="section">
            <div class="title">Isi Paket</div>
            <table>
                @foreach ($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td>x{{ $detail->quantity }}</td>
                    </tr>
                @endforeach
            </table>
        </section>

        <div class="footer">
            <span>Dicetak: {{ now()->translatedFormat('d M Y H:i') }}</span>
            <span>Total: Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
        </div>
    </main>
</body>
</html>
