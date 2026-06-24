<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your {{ config('app.name') }} Account</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:'Inter',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:12px;border:1px solid #e7eaf3;overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="background:#2563eb;padding:28px 40px;">
                        <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">
                            {{ config('app.name') }}
                        </p>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:32px 40px;">

                        <p style="margin:0 0 6px;font-size:16px;font-weight:600;color:#101828;">
                            Hi {{ $recipientName }},
                        </p>
                        <p style="margin:0 0 28px;font-size:14px;color:#6a7282;line-height:1.6;">
                            Your account on <strong>{{ config('app.name') }}</strong> has been created.
                            Below are your login credentials.
                        </p>

                        {{-- Credentials box --}}
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background:#f6f7fb;border:1px solid #e7eaf3;border-radius:8px;margin-bottom:28px;">
                            <tr>
                                <td style="padding:16px 20px;{{ $plainPassword ? 'border-bottom:1px solid #e7eaf3;' : '' }}">
                                    <span style="font-size:12px;color:#99a1af;display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Email</span>
                                    <span style="font-size:14px;font-weight:500;color:#101828;">{{ $recipientEmail }}</span>
                                </td>
                            </tr>
                            @if($plainPassword)
                            <tr>
                                <td style="padding:16px 20px;">
                                    <span style="font-size:12px;color:#99a1af;display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Password</span>
                                    <span style="font-size:15px;font-weight:600;color:#101828;font-family:monospace;letter-spacing:1px;">{{ $plainPassword }}</span>
                                </td>
                            </tr>
                            @endif
                        </table>

                        {{-- CTA button --}}
                        <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            <tr>
                                <td style="background:#2563eb;border-radius:8px;">
                                    <a href="{{ $loginUrl }}"
                                       style="display:inline-block;padding:12px 28px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">
                                        Log in to your account →
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- Security note --}}
                        <p style="margin:0;font-size:12px;color:#99a1af;line-height:1.7;padding:16px 20px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;">
                            <strong style="color:#92400e;">Security tip:</strong>
                            Please change your password after your first login.
                            If you did not expect this email, contact your administrator.
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:16px 40px;border-top:1px solid #e7eaf3;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#99a1af;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
