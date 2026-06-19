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
                        <td style="background-color:#ffffff; padding: 0; border-bottom: 1px solid #484848;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    {{-- Left Section (Green) --}}
                                    <td width="35%"
                                        style="background-color:#1B4332; padding: 30px 20px; text-align: center; vertical-align: middle;">
                                        <p
                                            style="margin:0; color:#ffffff; font-size:20px; font-weight:600; line-height: 1.3;">
                                            Smart Ambulance Telemedicine System
                                        </p>
                                    </td>
                                    {{-- Right Section (White with elements) --}}
                                    <td width="65%"
                                        style="background-color:#ffffff; padding: 30px 20px; text-align: center; vertical-align: middle;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" style="padding-bottom: 10px;">
                                                    <img src="{{ asset('assets/logo.png') }}" alt="Hospital Icon"
                                                        width="40" height="40" style="display:block; border:0;">
                                                    {{-- Ganti URL gambar di atas dengan URL icon rumah sakit yang sesuai --}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <h1
                                                        style="margin:0; color:#1B4332; font-size:26px; font-weight:bold;">
                                                        Reset Password
                                                    </h1>
                                                    <div
                                                        style="height: 2px; background-color: #000000; margin: 10px auto 10px;">
                                                    </div>
                                                    <p style="margin:0; color:#4a4a4a; font-size:13px;">
                                                        Permintaan Pengaturan Ulang Kata Sandi Akun
                                                    </p>
                                                    <p style="margin:8px 0 0; color:#a0a0a0; font-size:10px;">
                                                        SATS Security System Integration
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 36px 40px;">
                            <p style="margin:0 0 16px; color:#374151; font-size:15px;">Halo,</p>
                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Kami menerima permintaan reset password untuk akun kamu. Klik tombol di bawah untuk
                                membuat password baru.
                            </p>

                            {{-- Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0 28px;">
                                        <a href="{{ $resetUrl }}"
                                            style="display:inline-block; background-color:#1B4332; color:#ffffff;
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
                                <a href="{{ $resetUrl }}"
                                    style="color:#1B4332; font-size:13px;">{{ $resetUrl }}</a>
                            </p>

                            <p style="margin:0; color:#9ca3af; font-size:12px; line-height:1.6;">
                                Link ini akan kadaluarsa dalam <strong>60 menit</strong>.
                                Jika kamu tidak merasa meminta reset password, abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:0; text-align:center;">
                            <!--[if gte mso 9]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:500px; height:60px;">
                                <v:fill type="frame" src="{{ asset('assets/slanted-gradient.png') }}" color="#000000" />
                                <v:textbox inset="0,0,0,0">
                            <![endif]-->
                            <div
                                style="background-color: #1B4332;
                                        background-image: url('{{ asset('assets/slanted-gradient.png') }}');
                                        background-repeat: no-repeat;
                                        background-position: center center;
                                        background-size: 500px auto;
                                        padding:30px 40px;
                                        height: 20px;
                                        text-align:center;
                                        border-top:1px solid #e5e7eb;
                                        color:#d1fae5;
                                        font-size:12px; line-height:1.6;">
                                <p style="margin:0; color:inherit; font-size:inherit;">
                                     Smart Ambulance Telemedicine System © {{ date('Y') }}
                                </p>
                            </div>
                            <!--[if gte mso 9]>
                                </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
