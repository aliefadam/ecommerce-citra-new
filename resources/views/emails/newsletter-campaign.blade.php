<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:0; background-color:#fff7ed; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $messageBody)), 120) }}
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#fff7ed; margin:0; padding:0; width:100%;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px; width:100%;">
                    <tr>
                        <td align="center" style="padding-bottom:16px; font-size:12px; line-height:18px; color:#9a3412; letter-spacing:0.08em; text-transform:uppercase; font-weight:700;">
                            {{ $storeName }} Newsletter
                        </td>
                    </tr>

                    <tr>
                        <td style="background:linear-gradient(135deg, #fff7ed 0%, #ffedd5 50%, #fef3c7 100%); border:1px solid #fed7aa; border-bottom:none; border-radius:24px 24px 0 0; padding:40px 36px 32px; color:#7c2d12; text-align:center;">
                            <div style="font-size:12px; line-height:18px; letter-spacing:0.18em; text-transform:uppercase; color:#ea580c; font-weight:700; margin-bottom:12px;">
                                Newsletter
                            </div>
                            <div style="font-size:30px; line-height:38px; font-weight:800; margin:0 0 12px; color:#7c2d12;">
                                {{ $subjectLine }}
                            </div>
                            <div style="font-size:15px; line-height:24px; color:#9a3412; margin:0;">
                                Update terbaru dari {{ $storeName }} untuk kamu.
                            </div>
                        </td>
                    </tr>

                    @if (!empty($heroImageUrl))
                        <tr>
                            <td style="background-color:#ffffff; border-left:1px solid #fed7aa; border-right:1px solid #fed7aa; padding:0;">
                                <img src="{{ $heroImageUrl }}" alt="Newsletter Banner" width="640" style="display:block; width:100%; max-width:640px; height:auto; border:0;" />
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #fed7aa; border-top:none; border-radius:0 0 24px 24px; padding:36px 36px 28px;">
                            <div style="font-size:16px; line-height:30px; color:#374151; margin:0 0 24px; white-space:normal;">
                                {!! nl2br(e($messageBody)) !!}
                            </div>

                            @if (!empty($ctaLabel) && !empty($ctaUrl))
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
                                    <tr>
                                        <td align="center" bgcolor="#f97316" style="border-radius:999px;">
                                            <a href="{{ $ctaUrl }}" target="_blank"
                                                style="display:inline-block; padding:14px 28px; font-size:15px; line-height:20px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:999px;">
                                                {{ $ctaLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 24px; border-collapse:separate;">
                                <tr>
                                    <td style="background-color:#fff7ed; border:1px solid #fdba74; border-radius:16px; padding:16px 18px; font-size:14px; line-height:22px; color:#7c2d12;">
                                        <strong style="display:block; margin-bottom:6px; color:#9a3412;">Terima kasih sudah berlangganan</strong>
                                        Kamu menerima email ini karena terdaftar sebagai subscriber newsletter {{ $storeName }}.
                                    </td>
                                </tr>
                            </table>

                            <div style="border-top:1px solid #fed7aa; padding-top:20px; font-size:14px; line-height:24px; color:#6b7280;">
                                Salam hangat,<br>
                                <span style="font-weight:700; color:#7c2d12;">Tim {{ $storeName }}</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:18px 20px 0; font-size:12px; line-height:20px; color:#9ca3af;">
                            Email ini dikirim oleh {{ $storeName }}.<br>
                            Mohon simpan email ini jika informasinya penting untuk kamu.<br>
                            @if (!empty($unsubscribeUrl))
                                <a href="{{ $unsubscribeUrl }}" target="_blank" style="color:#f97316; text-decoration:none; font-weight:700;">Berhenti berlangganan</a>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
