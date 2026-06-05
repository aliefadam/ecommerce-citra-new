<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Pajak Tersedia</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 28px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:34px 36px;background:#0f766e;color:#ffffff;">
                            <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ccfbf1;">Faktur Pajak</p>
                            <h1 style="margin:0;font-size:26px;line-height:1.25;">Faktur pajak Anda sudah tersedia</h1>
                            <p style="margin:10px 0 0;font-size:14px;color:#d1fae5;">Silakan masuk ke akun customer untuk mengunduh file resmi dari detail transaksi.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:14px 16px;border-bottom:1px solid #e2e8f0;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.7px;">Invoice</p>
                                        <p style="margin:0;font-size:15px;font-weight:700;color:#0f172a;">{{ $transaction->invoice_no }}</p>
                                    </td>
                                    <td style="padding:14px 16px;border-bottom:1px solid #e2e8f0;text-align:right;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.7px;">Total</p>
                                        <p style="margin:0;font-size:15px;font-weight:700;color:#0f172a;">Rp {{ number_format((int) $transaction->grand_total, 0, ',', '.') }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.7px;">Nomor Faktur Pajak</p>
                                        <p style="margin:0;font-size:14px;color:#334155;">{{ $taxInvoice->tax_invoice_number ?: '-' }}</p>
                                    </td>
                                    <td style="padding:14px 16px;text-align:right;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.7px;">Tanggal Faktur</p>
                                        <p style="margin:0;font-size:14px;color:#334155;">{{ $taxInvoice->tax_invoice_date?->translatedFormat('d M Y') ?? '-' }}</p>
                                    </td>
                                </tr>
                            </table>

                            <a href="{{ $downloadUrl }}" style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:10px;padding:13px 18px;font-size:14px;font-weight:700;">Download Faktur Pajak</a>

                            <p style="margin:22px 0 0;font-size:13px;line-height:1.7;color:#64748b;">Link download membutuhkan login akun customer pemilik transaksi. File terbaru juga selalu tersedia dari halaman profil pada detail pesanan.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
