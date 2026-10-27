<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') | @yield('title') — آرون سافت</title>
    <style>
        @font-face{font-family:'Vazirmatn';src:url('/fonts/vazirmatn/Vazirmatn%5Bwght%5D.woff2') format('woff2');font-weight:100 900;font-display:swap}
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%}
        body{font-family:'Vazirmatn',Tahoma,Arial,sans-serif;background:#f9fafb;color:#111827;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;
            background-image:radial-gradient(#dbeafe 1px,transparent 1px);background-size:22px 22px}
        .card{width:100%;max-width:520px;background:#fff;border:1px solid #e5e7eb;border-radius:24px;padding:48px 32px;text-align:center;box-shadow:0 20px 50px -20px rgba(37,99,235,.25)}
        .brand{min-height:44px;display:inline-flex;align-items:center;gap:8px;font-weight:900;color:#1e3a8a;letter-spacing:1px;margin-bottom:28px;text-decoration:none}
        .brand i{width:34px;height:34px;border-radius:10px;background:#2563eb;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-style:normal;font-size:18px}
        .code{font-size:88px;line-height:1;font-weight:900;color:#2563eb;margin:0 0 8px;direction:ltr}
        h1{font-size:22px;margin:0 0 12px}
        p{margin:0 0 28px;color:#6b7280;line-height:2;font-size:15px}
        .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
        .btn{display:inline-block;padding:12px 26px;border-radius:12px;font-weight:700;font-size:14px;text-decoration:none;border:1px solid transparent;cursor:pointer;font-family:inherit}
        .btn-primary{background:#2563eb;color:#fff;box-shadow:0 10px 24px -10px rgba(37,99,235,.7)}
        .btn-primary:hover{background:#1d4ed8}
        .btn-ghost{background:#fff;color:#374151;border-color:#d1d5db}
        .btn-ghost:hover{background:#f3f4f6}
        .foot{margin-top:28px;font-size:12px;color:#9ca3af}
    </style>
</head>
<body>
<main class="card">
    <a class="brand" href="/"><i>A</i> ARON SOFT</a>
    <div class="code">@yield('code')</div>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <div class="actions">
        @yield('actions')
    </div>
    <div class="foot">در صورت تکرار مشکل با ما تماس بگیرید: ۰۲۱-۲۲۴۵۱۹۱۲</div>
</main>
</body>
</html>
