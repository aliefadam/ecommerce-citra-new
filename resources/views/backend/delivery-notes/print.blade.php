<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan {{ $deliveryNote->delivery_note_no }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; background: #f8fafc; color: #0f172a; }
        .page { max-width: 840px; margin: 24px auto; background: #fff; padding: 32px; border: 1px solid #e2e8f0; }
        .row { display: flex; justify-content: space-between; gap: 24px; }
        h1 { margin: 0; font-size: 24px; }
        h2 { font-size: 14px; margin: 24px 0 10px; color: #475569; text-transform: uppercase; letter-spacing: .04em; }
        p { margin: 4px 0; font-size: 14px; color: #475569; }
        .brand { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-bottom: 8px; }
        .brand-mark { width: 38px; height: 38px; border-radius: 10px; background: #2563eb; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f8fafc; color: #475569; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 700; }
        .actions { max-width: 840px; margin: 24px auto 0; text-align: right; }
        button { border: 0; background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .signature-box { width: 30%; text-align: center; }
        .signature-line { margin-top: 60px; border-top: 1px solid #94a3b8; padding-top: 6px; font-size: 12px; color: #64748b; }
        .divider { border-top: 2px dashed #cbd5e1; margin: 32px 0; }
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
                <h1>Surat Jalan</h1>
                <p>{{ $deliveryNote->delivery_note_no }}</p>
                <p>{{ $deliveryNote->created_at->translatedFormat('d M Y') }}</p>
            </div>
            <div class="right">
                <div class="brand">
                    <span class="brand-mark">{{ strtoupper(substr($deliveryNote->company->name ?? 'C', 0, 1)) }}</span>
                    <strong>{{ $deliveryNote->company->name ?? '' }}</strong>
                </div>
                <p><span class="badge">{{ strtoupper($deliveryNote->status) }}</span></p>
            </div>
        </div>

        <div class="row">
            <div>
                <h2>Penerima</h2>
                <p><strong>{{ $deliveryNote->recipient_name }}</strong></p>
                <p>{{ $deliveryNote->shipping_address }}</p>
            </div>
            <div class="right">
                <h2>Pengiriman</h2>
                <p>Kurir: {{ $deliveryNote->courier_name ?: '-' }}</p>
                <p>Sales Order: {{ $deliveryNote->salesOrder?->sales_order_no }}</p>
            </div>
        </div>

        <h2>Item Dikirim</h2>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th class="right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliveryNote->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td>{{ $detail->sku }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Pengirim (Gudang)</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Kurir</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Penerima</div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="row">
            <div>
                <h1>Packing List</h1>
                <p>{{ $deliveryNote->packingList?->packing_list_no }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th class="right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliveryNote->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}{{ $detail->variant_name ? ' (' . $detail->variant_name . ')' : '' }}</td>
                        <td>{{ $detail->sku }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <tr>
                <td>Total Berat</td>
                <td class="right">{{ number_format(($deliveryNote->packingList?->total_weight_grams ?? 0) / 1000, 2) }} kg</td>
            </tr>
            @if ($deliveryNote->packingList?->total_packages)
                <tr>
                    <td>Jumlah Koli</td>
                    <td class="right">{{ $deliveryNote->packingList->total_packages }}</td>
                </tr>
            @endif
        </table>
    </main>
</body>
</html>
