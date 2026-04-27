<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:12px; overflow:hidden; border: 1px solid #e5e7eb;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#00a884; padding: 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:600;">
                                Reset Password
                            </h1>
                            <p style="margin:6px 0 0; color:#d1fae5; font-size:13px;">
                                Smart Ambulance Telemedicine System
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 36px 40px;">
                            <p style="margin:0 0 16px; color:#374151; font-size:15px;">Halo,</p>
                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Kami menerima permintaan reset password untuk akun kamu. Klik tombol di bawah untuk membuat password baru.
                            </p>

                            {{-- Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0 28px;">
                                        <a href="{{ $resetUrl }}"
                                            style="display:inline-block; background-color:#00a884; color:#ffffff;
                                                   text-decoration:none; padding:12px 32px; border-radius:8px;
                                                   font-size:14px; font-weight:600;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; color:#6b7280; font-size:13px;">
                                Jika tombol tidak berfungsi, copy link berikut ke browser kamu:
                            </p>
                            <p style="margin:0 0 24px; word-break:break-all;">
                                <a href="{{ $resetUrl }}" style="color:#00a884; font-size:13px;">{{ $resetUrl }}</a>
                            </p>

                            <p style="margin:0; color:#9ca3af; font-size:12px; line-height:1.6;">
                                Link ini akan kadaluarsa dalam <strong>60 menit</strong>. 
                                Jika kamu tidak merasa meminta reset password, abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f9fafb; padding:20px 40px; text-align:center;
                                   border-top:1px solid #e5e7eb;">
                            <p style="margin:0; color:#9ca3af; font-size:12px;">
                                © {{ date('Y') }} SATS — Smart Ambulance Telemedicine System
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>