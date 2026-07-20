<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Resi Terpilih</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            background: #f1f5f9;
            color: #000;
        }

        .actions {
            width: 100mm;
            margin: 18px auto 10px;
            text-align: right;
        }

        button {
            border: 0;
            background: #2563eb;
            color: #fff;
            padding: 9px 14px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        .notice {
            width: 100mm;
            margin: 0 auto 14px;
            padding: 10px 12px;
            border: 1px solid #f59e0b;
            background: #fffbeb;
            color: #92400e;
            border-radius: 8px;
            font-size: 12px;
            line-height: 1.35;
        }

        .notice strong {
            display: block;
            margin-bottom: 5px;
        }

        .label {
            width: 100mm;
            min-height: 148mm;
            margin: 0 auto 24px;
            background: #fff;
            border: 2px solid #222;
        }

        .row {
            display: flex;
        }

        .cell {
            border-top: 1.5px solid #555;
            padding: 7px 9px;
        }

        .top {
            height: 19mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
        }

        .brand {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -.04em;
            color: #27356f;
        }

        .brand small {
            display: block;
            color: #555;
            font-size: 9px;
            letter-spacing: 0;
            font-weight: 700;
            margin-top: -2px;
        }

        .courier {
            font-size: 24px;
            font-weight: 900;
            font-style: italic;
            color: #203a82;
            text-align: right;
            line-height: .8;
        }

        .courier span {
            display: block;
            color: #e11d48;
            font-size: 9px;
            font-style: normal;
            letter-spacing: .08em;
            margin-top: 5px;
        }

        .barcode-wrap {
            border-top: 1.5px solid #555;
            padding: 8px 8px 6px;
            text-align: center;
        }

        .barcode {
            height: 17mm;
            display: flex;
            align-items: stretch;
            justify-content: center;
            gap: 1px;
            overflow: hidden;
        }

        .bar {
            display: block;
            background: #000;
            height: 100%;
        }

        .resi {
            font-size: 15px;
            font-weight: 900;
            margin-top: 5px;
        }

        .total {
            border-top: 1.5px solid #555;
            padding: 8px 8px;
            text-align: center;
            font-size: 24px;
            font-weight: 900;
        }

        .service {
            border-top: 1.5px solid #555;
            padding: 5px 8px;
            text-align: center;
            font-size: 15px;
            font-weight: 900;
        }

        .half {
            width: 50%;
            font-size: 12px;
            font-weight: 800;
            min-height: 12mm;
        }

        .half+.half {
            border-left: 1.5px solid #222;
        }

        .items {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.25;
        }

        .party {
            width: 50%;
            padding: 10px 9px;
            min-height: 43mm;
        }

        .title {
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 3px;
        }

        .name {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.05;
            margin-bottom: 9px;
        }

        .address {
            font-size: 12px;
            font-weight: 800;
            line-height: 1.08;
            white-space: pre-line;
        }

        .phone {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.08;
            margin-top: 10px;
        }

        .notes {
            min-height: 17mm;
            font-size: 11px;
            line-height: 1.2;
        }

        .foot {
            font-size: 10px;
            font-style: italic;
            line-height: 1.25;
        }

        @page {
            size: 100mm 148mm;
            margin: 0;
        }

        @media print {
            body {
                background: #fff;
            }

            .actions,
            .notice {
                display: none;
            }

            .label {
                width: 100mm;
                min-height: 148mm;
                margin: 0;
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <button onclick="window.print()" @disabled($validTransactions->isEmpty())>Print Resi</button>
    </div>

    @if ($transactions->isEmpty())
        <div class="notice">
            <strong>Tidak ada transaksi dipilih.</strong>
            Pilih minimal satu transaksi dari halaman admin Transactions.
        </div>
    @endif

    @if ($invalidTransactions->isNotEmpty())
        <div class="notice">
            <strong>{{ $invalidTransactions->count() }} transaksi tidak bisa dicetak.</strong>
            @foreach ($invalidTransactions as $item)
                <div>{{ $item['transaction']->invoice_no }}: {{ implode(' ', $item['issues']) }}</div>
            @endforeach
        </div>
    @endif

    @foreach ($validTransactions as $transaction)
        @php
            $trackingNumber = trim((string) ($transaction->tracking_number ?: $transaction->order_id ?: $transaction->invoice_no));
            $barcodeSeed = preg_replace('/[^A-Za-z0-9]/', '', $trackingNumber) ?: (string) $transaction->id;
            $barcodeChars = str_split(strtoupper($barcodeSeed));
            $bars = [];
            foreach ($barcodeChars as $char) {
                $value = ord($char);
                for ($i = 0; $i < 4; $i++) {
                    $bars[] = 1 + (($value >> $i) & 3);
                }
            }
            while (count($bars) < 56) {
                $bars = array_merge($bars, [1, 3, 2, 1, 4, 2, 1]);
            }
            $bars = array_slice($bars, 0, 74);
            $totalWeight = $transaction->details->sum(function ($detail) {
                $weight = (int) ($detail->productVariant?->weight_grams ?: (int) env('CHECKOUT_DEFAULT_ITEM_WEIGHT', 1000));
                return max(1, $weight) * max(1, (int) $detail->quantity);
            });
            $totalQty = $transaction->details->sum('quantity');
            $itemSummary = $transaction->details
                ->map(fn ($detail) => 'Qty: ' . (int) $detail->quantity . ' | ' . $detail->product_name . ($detail->variant_name ? ' - ' . $detail->variant_name : ''))
                ->implode(' / ');
            $customerAddress = trim(
                ($transaction->shipping_address_line ?: '-') .
                    ($transaction->shipping_city ? "\n" . $transaction->shipping_city : '') .
                    ($transaction->shipping_province ? ', ' . $transaction->shipping_province : '') .
                    ($transaction->shipping_postal_code ? ', ' . $transaction->shipping_postal_code : ''),
            );
            $transactionStoreLocation = $storeLocationsByCompany->get($transaction->company_id);
            $senderAddress = $transactionStoreLocation
                ? trim(
                    ($transactionStoreLocation->label ?: 'Lokasi Toko') .
                        "\n" .
                        ($transactionStoreLocation->city_name ?: '') .
                        ($transactionStoreLocation->province_name ? ', ' . $transactionStoreLocation->province_name : ''),
                )
                : 'Alamat toko belum diatur';
            $notes = trim((string) $transaction->shipping_note);
            if ($notes === '') {
                $notes = $transaction->details->pluck('item_note')->filter()->implode(' / ');
            }
            $serviceText = $transaction->shipping_label ?: '-';
            $serviceParts = preg_split('/\s+/', trim($serviceText), 2);
            $courierName = strtoupper($serviceParts[0] ?? 'KURIR');
            $courierService = strtoupper($serviceParts[1] ?? 'EXPRESS');
            $senderPhone = (string) ($appStoreSettings['social_whatsapp'] ?? '-');
            $senderPhone = preg_replace('/^https?:\/\/(wa\.me|api\.whatsapp\.com\/send\?phone=)\//', '', $senderPhone);
            $senderPhone = trim($senderPhone) !== '' ? $senderPhone : '-';
            $recipientName = $transaction->shipping_recipient_name ?: ($transaction->user?->name ?: '-');
        @endphp

        <main class="label">
            <div class="top">
                <div class="brand">
                    {{ $appStoreName ?? 'Ecommerce Citra' }}
                    <small>ONLINE STORE</small>
                </div>
                <div class="courier">
                    {{ $courierName }}
                    <span>{{ $courierService }}</span>
                </div>
            </div>

            <div class="barcode-wrap">
                <div class="barcode" aria-label="Barcode {{ $trackingNumber }}">
                    @foreach ($bars as $width)
                        <span class="bar" style="width: {{ $width }}px"></span>
                    @endforeach
                </div>
                <div class="resi">RESI : {{ $trackingNumber }}</div>
            </div>

            <div class="total">TOTAL : Rp.{{ number_format((int) $transaction->grand_total, 0, ',', '.') }}</div>
            <div class="service">{{ $transaction->order_id }} | Layanan: {{ $serviceText }}</div>

            <div class="row">
                <div class="cell half">Asuransi: Rp. 0,-</div>
                <div class="cell half">Berat: {{ number_format((int) $totalWeight, 0, ',', '.') }} gr</div>
            </div>

            <div class="cell items">- {{ $itemSummary ?: 'Qty: ' . (int) $totalQty . ' | Paket' }}</div>

            <div class="row cell" style="padding:0">
                <div class="party">
                    <div class="title">Penerima:</div>
                    <div class="name">{{ $recipientName }}</div>
                    <div class="address">{{ $customerAddress }}</div>
                    <div class="phone">Nomor Telepon:<br>{{ $transaction->shipping_phone ?: '-' }}</div>
                </div>
                <div class="party">
                    <div class="title">Pengirim:</div>
                    <div class="name">{{ $appStoreName ?? 'Ecommerce Citra' }}</div>
                    <div class="address">{{ $senderAddress }}</div>
                    <div class="phone">Nomor Telepon:<br>{{ $senderPhone }}</div>
                </div>
            </div>

            <div class="cell notes">
                Catatan:<br>
                {{ $notes ?: '-' }}
            </div>

            <div class="cell foot">
                *Pengirim wajib meminta bukti serah terima paket ke kurir.
            </div>
        </main>
    @endforeach
</body>

</html>
