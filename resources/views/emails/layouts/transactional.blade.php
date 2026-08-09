<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $appName ?? config('app.name'))</title>
</head>
<body dir="rtl" style="margin:0;padding:0;background:#F7F7F8;font-family:'Segoe UI',Tahoma,Arial,sans-serif;direction:rtl;text-align:right;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F7F8;padding:24px 0;direction:rtl;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;background:#FFFFFF;border:1px solid #E5E5E8;border-radius:12px;overflow:hidden;direction:rtl;">
                    <tr>
                        <td style="background:#4F46E5;padding:28px 32px;text-align:center;direction:rtl;">
                            <div style="color:#FFFFFF;font-size:20px;font-weight:700;"><span style="unicode-bidi:isolate;">{{ $appName }}</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;text-align:right;direction:rtl;font-size:14px;line-height:1.7;color:#4B5563;">
                            @yield('content')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
