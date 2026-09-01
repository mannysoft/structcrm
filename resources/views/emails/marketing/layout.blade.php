<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f4f6; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px; max-width:600px;">
                    <tr>
                        <td style="background-color:#1f5cf0; padding:24px 32px; border-radius:12px 12px 0 0;">
                            <span style="color:#ffffff; font-size:18px; font-weight:700;">{{ config('app.name', 'ConstructFlow') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; padding:32px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; padding:16px 32px 24px 32px; border-radius:0 0 12px 12px; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:12px; line-height:18px; color:#6b7280;">
                                You're receiving this because you opted in to product updates from {{ config('app.name', 'ConstructFlow') }}.
                                If you'd rather not receive these emails, you can
                                <a href="{{ $unsubscribeUrl }}" style="color:#1f5cf0; text-decoration:underline;">unsubscribe</a> at any time.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
