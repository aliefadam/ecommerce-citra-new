<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        * { box-sizing: border-box; }
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; }
        body {
            margin: 0; padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            color: #1e293b;
        }
        .label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .value {
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
        }
        @media only screen and (max-width: 600px) {
            .wrapper { width: 100% !important; }
            .info-col { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;">
<tr><td align="center" style="padding:32px 16px;">

    {{-- ── Outer card ── --}}
    <table class="wrapper" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
        style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);max-width:600px;width:100%;">

        {{-- ── HEADER ── --}}
        <tr>
            <td style="background:linear-gradient(135deg,#2563eb 0%,#4f46e5 100%);padding:36px 40px 28px;text-align:center;">

                {{-- Logo --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom:18px;">
                    <tr>
                        <td style="background:rgba(255,255,255,0.2);border-radius:10px;width:42px;height:42px;text-align:center;vertical-align:middle;padding:0 10px;">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/shopping-cart.png" width="24" height="24" alt="" style="display:block;margin:9px auto;" />
                        </td>
                        <td style="padding-left:10px;vertical-align:middle;">
                            <span style="font-size:20px;font-weight:800;color:#ffffff;letter-spacing:-0.3px;">Citra Ecommerce</span>
                        </td>
                    </tr>
                </table>

                <p style="font-size:28px;font-weight:700;color:#ffffff;margin:0 0 6px;letter-spacing:-0.5px;">Invoice Pesanan</p>
                <p style="font-size:14px;color:rgba(255,255,255,0.75);margin:0;">Terima kasih telah berbelanja bersama kami!</p>
            </td>
        </tr>

        {{-- ── STATUS BAR ── --}}
        @php
            $statusMap = [
                'pending'    => ['label' => 'Menunggu Pembayaran', 'bg' => '#fef3c7', 'color' => '#92400e'],
                'settlement' => ['label' => 'Pembayaran Diterima',  'bg' => '#dcfce7', 'color' => '#166534'],
                'capture'    => ['label' => 'Pembayaran Diterima',  'bg' => '#dcfce7', 'color' => '#166534'],
                'paid'       => ['label' => 'Lunas',                'bg' => '#dcfce7', 'color' => '#166534'],
                'process'    => ['label' => 'Sedang Diproses',      'bg' => '#dbeafe', 'color' => '#1e40af'],
                'kirim'      => ['label' => 'Sedang Dikirim',       'bg' => '#ede9fe', 'color' => '#5b21b6'],
                'expire'     => ['label' => 'Kedaluwarsa',          'bg' => '#fee2e2', 'color' => '#991b1b'],
                'cancel'     => ['label' => 'Dibatalkan',           'bg' => '#fee2e2', 'color' => '#991b1b'],
            ];
            $statusKey  = strtolower($transaction->status ?? 'pending');
            $statusInfo = $statusMap[$statusKey] ?? ['label' => ucfirst($statusKey), 'bg' => '#f1f5f9', 'color' => '#475569'];
        @endphp
        <tr>
            <td style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 40px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <span style="font-size:13px;font-weight:600;color:#64748b;">{{ $transaction->invoice_no }}</span>
                        </td>
                        <td style="text-align:right;vertical-align:middle;">
                            <span style="display:inline-block;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:600;letter-spacing:0.3px;background:{{ $statusInfo['bg'] }};color:{{ $statusInfo['color'] }};">
                                {{ $statusInfo['label'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ── BODY ── --}}
        <tr>
            <td style="padding:32px 40px;">

                {{-- ── Informasi Pesanan ── --}}
                <p style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;margin:0 0 12px;">Informasi Pesanan</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
                    <tr>
                        <td style="padding:14px 18px;border-right:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;width:50%;vertical-align:top;">
                            <p class="label" style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;margin:0 0 4px;">Tanggal Pesan</p>
                            <p class="value" style="font-size:14px;font-weight:500;color:#1e293b;margin:0;">{{ $transaction->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB</p>
                        </td>
                        <td style="padding:14px 18px;border-bottom:1px solid #e2e8f0;width:50%;vertical-align:top;">
                            <p class="label" style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;margin:0 0 4px;">Metode Pembayaran</p>
                            <p class="value" style="font-size:14px;font-weight:500;color:#1e293b;margin:0;">
                                {{ strtoupper($transaction->payment_type ?? '-') }}{{ $transaction->payment_method ? ' · ' . $transaction->payment_method : '' }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 18px;border-right:1px solid #e2e8f0;width:50%;vertical-align:top;">
                            <p class="label" style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;margin:0 0 4px;">Ekspedisi</p>
                            <p class="value" style="font-size:14px;font-weight:500;color:#1e293b;margin:0;">{{ $transaction->shipping_label ?: '-' }}</p>
                        </td>
                        <td style="padding:14px 18px;width:50%;vertical-align:top;">
                            <p class="label" style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;margin:0 0 4px;">Nama Pemesan</p>
                            <p class="value" style="font-size:14px;font-weight:500;color:#1e293b;margin:0;">{{ $transaction->user->name ?? '-' }}</p>
                        </td>
                    </tr>
                </table>

                {{-- ── Alamat Pengiriman ── --}}
                <p style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;margin:0 0 12px;">Alamat Pengiriman</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:16px 20px;">
                            <p style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 3px;">{{ $transaction->shipping_recipient_name ?: ($transaction->user->name ?? '-') }}</p>
                            @if($transaction->shipping_phone)
                                <p style="font-size:13px;color:#64748b;margin:0 0 8px;">{{ $transaction->shipping_phone }}</p>
                            @endif
                            <p style="font-size:13px;color:#475569;line-height:1.7;margin:0;">
                                {{ $transaction->shipping_address_line }}<br>
                                {{ $transaction->shipping_city }}{{ $transaction->shipping_province ? ', ' . $transaction->shipping_province : '' }}{{ $transaction->shipping_postal_code ? ' ' . $transaction->shipping_postal_code : '' }}
                            </p>
                        </td>
                    </tr>
                </table>

                {{-- ── Item Pesanan ── --}}
                <p style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;margin:0 0 12px;">Item Pesanan</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
                    {{-- Header --}}
                    <tr style="background:#f8fafc;">
                        <td style="padding:11px 16px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;">Produk</span>
                        </td>
                        <td style="padding:11px 16px;border-bottom:1px solid #e2e8f0;text-align:center;white-space:nowrap;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;">Qty</span>
                        </td>
                        <td style="padding:11px 16px;border-bottom:1px solid #e2e8f0;text-align:right;white-space:nowrap;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;">Harga</span>
                        </td>
                        <td style="padding:11px 16px;border-bottom:1px solid #e2e8f0;text-align:right;white-space:nowrap;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:#94a3b8;">Subtotal</span>
                        </td>
                    </tr>
                    {{-- Rows --}}
                    @foreach($transaction->details as $item)
                    <tr>
                        <td style="padding:14px 16px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                            <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 2px;">{{ $item->product_name }}</p>
                            @if($item->variant_name)
                                <p style="font-size:12px;color:#94a3b8;margin:0 0 2px;">{{ $item->variant_name }}</p>
                            @endif
                            @if($item->item_note)
                                <p style="font-size:11px;color:#f59e0b;margin:0;">Catatan: {{ $item->item_note }}</p>
                            @endif
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid #f1f5f9;text-align:center;vertical-align:top;white-space:nowrap;">
                            <span style="font-size:13px;font-weight:500;color:#334155;">{{ $item->quantity }}x</span>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid #f1f5f9;text-align:right;vertical-align:top;white-space:nowrap;">
                            <span style="font-size:13px;color:#334155;">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid #f1f5f9;text-align:right;vertical-align:top;white-space:nowrap;">
                            <span style="font-size:13px;font-weight:600;color:#1e293b;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </table>

                {{-- ── Ringkasan Pembayaran ── --}}
                <p style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;margin:0 0 12px;">Ringkasan Pembayaran</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:13px 20px;border-bottom:1px solid #e2e8f0;background:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:14px;color:#64748b;">Subtotal Produk</td>
                                    <td style="font-size:14px;font-weight:500;color:#1e293b;text-align:right;white-space:nowrap;">Rp {{ number_format($transaction->subtotal_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:13px 20px;border-bottom:1px solid #e2e8f0;background:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:14px;color:#64748b;">Ongkos Kirim</td>
                                    <td style="font-size:14px;font-weight:500;color:#1e293b;text-align:right;white-space:nowrap;">Rp {{ number_format($transaction->shipping_cost, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 20px;background:linear-gradient(135deg,#2563eb 0%,#4f46e5 100%);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:15px;font-weight:700;color:#ffffff;">Total Pembayaran</td>
                                    <td style="font-size:18px;font-weight:800;color:#ffffff;text-align:right;white-space:nowrap;">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        {{-- ── FOOTER ── --}}
        <tr>
            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px;text-align:center;">
                <p style="font-size:13px;color:#94a3b8;line-height:1.7;margin:0 0 10px;">
                    Email ini dikirim secara otomatis. Harap simpan sebagai bukti transaksi Anda.<br>
                    Jika ada pertanyaan, silakan hubungi tim kami melalui halaman bantuan.
                </p>
                <p style="font-size:13px;font-weight:700;color:#2563eb;margin:0;">Citra Ecommerce &mdash; Belanja Mudah, Nyaman, Terpercaya</p>
            </td>
        </tr>

    </table>
    {{-- end outer card --}}

</td></tr>
</table>
{{-- end full-width wrapper --}}

</body>
</html>
