<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>איפוס סיסמה - {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background:#F7F7F8;font-family:Heebo,'Segoe UI',Tahoma,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F7F8;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;background:#FFFFFF;border:1px solid #E5E5E8;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#4F46E5;padding:28px 32px;text-align:center;">
                            <div style="color:#FFFFFF;font-size:20px;font-weight:700;"><span style="unicode-bidi:isolate;">{{ $appName }}</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px;font-size:18px;color:#1A1A1E;text-align:center;">איפוס סיסמה</h1>
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.7;color:#4B5563;">
                                שלום <span style="unicode-bidi:isolate;font-weight:600;color:#1A1A1E;">{{ $userName }}</span>,
                            </p>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.7;color:#4B5563;">
                                קיבלנו בקשה לאיפוס הסיסמה של החשבון
                                <span dir="ltr" style="unicode-bidi:isolate;">{{ $email }}</span>
                                ב<span style="unicode-bidi:isolate;">{{ $appName }}</span>. לחצו על הכפתור למטה כדי לבחור סיסמה חדשה.
                            </p>
                            <p style="margin:0 0 24px;text-align:center;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:#4F46E5;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:700;padding:12px 32px;border-radius:8px;">
                                    איפוס סיסמה
                                </a>
                            </p>
                            <p style="margin:0 0 20px;text-align:center;">
                                <a href="{{ $resetUrl }}" dir="ltr" style="font-size:12px;color:#6B7280;word-break:break-all;unicode-bidi:isolate;">{{ $resetUrl }}</a>
                            </p>
                            <p style="margin:0;font-size:12px;line-height:1.7;color:#6B7280;">
                                קישור זה תקף למשך 60 דקות בלבד. אם לא ביקשת איפוס סיסמה, ניתן להתעלם מהודעה זו.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
