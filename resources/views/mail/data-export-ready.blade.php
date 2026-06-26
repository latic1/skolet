<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Export Ready</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Inter,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:40px 0;">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
            <tr>
                <td style="background:#1d4ed8;padding:28px 40px;">
                    <h1 style="margin:0;font-size:20px;font-weight:700;color:#ffffff;">{{ config('app.name') }}</h1>
                    <p style="margin:4px 0 0;font-size:13px;color:#bfdbfe;">Data Export Ready</p>
                </td>
            </tr>
            <tr>
                <td style="padding:36px 40px;">
                    <p style="margin:0 0 8px;font-size:15px;color:#374151;">Hello {{ $recipientName }},</p>
                    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                        Your data export for <strong style="color:#111827;">{{ $exportType }}</strong> is ready for download.
                    </p>

                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:0 0 24px;">
                        <p style="margin:0 0 4px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Download Link</p>
                        <a href="{{ $downloadUrl }}" style="display:inline-block;margin-top:10px;padding:10px 20px;background:#1d4ed8;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:600;">
                            Download Export ZIP
                        </a>
                        <p style="margin:12px 0 0;font-size:12px;color:#9ca3af;">
                            Link expires: <strong>{{ $expiresAt }}</strong>
                        </p>
                    </div>

                    <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.5;">
                        This link is for your use only. After it expires, you can request a new export from Settings → Data &amp; Privacy.
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:20px 40px;">
                    <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
                        © {{ date('Y') }} {{ config('app.name') }}. Sent from your school's administration system.
                    </p>
                </td>
            </tr>
        </table>
    </td></tr>
</table>
</body>
</html>
