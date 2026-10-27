<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'آرون سافت')</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;color:#334155;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
                <tr>
                    <td align="center" style="background:#1e3a8a;padding:24px;">
                        <div style="color:#ffffff;font-size:22px;font-weight:bold;letter-spacing:1px;">ARON SOFT</div>
                        <div style="color:#bfdbfe;font-size:13px;margin-top:4px;">طراحی سایت، نرم‌افزار و زیرساخت</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 28px 8px;line-height:2;font-size:15px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px 28px;font-size:12px;color:#94a3b8;line-height:1.9;">
                        @yield('footer')
                        <div style="border-top:1px solid #e2e8f0;margin-top:12px;padding-top:12px;">
                            آرون سافت — تهران، بلوار ارتش، کوچه نصر ۲، پلاک ۸، واحد ۸ — ۰۲۱-۲۲۴۵۱۹۱۲ — info@aron-soft.com
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
