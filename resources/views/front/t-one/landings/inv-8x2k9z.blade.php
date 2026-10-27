<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>پیش‌فاکتور آرون سافت</title>

    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #eff6ff;
            --accent-color: #2563eb;
            --text-color: #334155;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 40px 20px;
            color: var(--text-color);
            line-height: 1.8;
            display: flex;
            justify-content: center;
        }

        .invoice-box {
            width: 100%;
            max-width: 850px;
            background-color: #ffffff;
            padding: 50px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            margin-bottom: 60px;
            box-sizing: border-box; /* مهم برای ریسپانسیو */
        }

        .invoice-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
        }

        /* --- دکمه پرینت --- */
        .actions-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            width: 90%; /* در موبایل عرض کامل بگیرد */
            max-width: 300px;
            text-align: center;
        }

        .btn-print {
            padding: 12px 30px;
            border-radius: 50px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            border: none;
            font-family: 'Vazirmatn', sans-serif;
            box-shadow: 0 5px 20px rgba(30, 58, 138, 0.4);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        /* --- هدر --- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 25px;
        }

        .company-info h1 { margin: 0; color: var(--primary-color); font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .company-info .subtitle { font-size: 14px; color: #64748b; margin-top: 5px; display: block; font-weight: 500; }
        .website-badge { display: inline-block; background-color: var(--secondary-color); color: var(--accent-color); padding: 6px 16px; border-radius: 50px; font-weight: 700; text-decoration: none; margin-bottom: 8px; border: 1px solid #bfdbfe; font-size: 14px; }
        .contact-header { font-size: 0.9em; color: #64748b; margin-top: 10px; }
        .contact-header span { display: block; }

        /* --- جدول اطلاعات کارفرما/پیمانکار --- */
        .info-table { width: 100%; margin-bottom: 40px; border-spacing: 0 15px; border-collapse: separate; }
        .info-box { background-color: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; border-right: 5px solid var(--primary-color); }
        .info-box strong { display: block; margin-bottom: 12px; color: var(--primary-color); font-size: 1.1em; }
        .info-row { display: flex; margin-bottom: 8px; font-size: 0.95em; }
        .info-label { color: #64748b; width: 70px; flex-shrink: 0; }
        .info-value { font-weight: 600; color: #334155; }
        .info-value-ltr { font-weight: 600; direction: ltr; text-align: right; }

        /* --- جدول خدمات --- */
        /* اضافه کردن اسکرول برای موبایل */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 40px;
        }

        .services-table { width: 100%; min-width: 600px; /* حداقل عرض برای جلوگیری از فشرده شدن */ border-collapse: collapse; border-spacing: 0; border: none; }
        .services-table th { background-color: var(--primary-color); color: white; padding: 18px; text-align: center; font-weight: 700; font-size: 15px; white-space: nowrap; }
        .services-table td { padding: 18px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .services-table tr:last-child td { border-bottom: none; }
        .services-table tr:nth-child(even) { background-color: #f8fafc; }

        .price-col { text-align: center !important; white-space: nowrap; width: 18%; font-weight: 700; color: #0f172a; font-size: 1.1em; }
        .row-idx { text-align: center !important; width: 50px; font-weight: bold; color: var(--primary-color); }
        .total-row td { background-color: var(--secondary-color) !important; color: var(--primary-color); font-weight: 900; font-size: 1.4em; padding: 25px; }

        .terms { background-color: #fff8e1; border: 1px solid #fce7b2; color: #856404; padding: 25px; border-radius: 10px; margin-top: 20px; }
        .terms h3 { margin-top: 0; color: #856404; font-size: 16px; margin-bottom: 10px; }

        .footer { margin-top: 80px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 220px; }
        .signature-line { margin-top: 60px; border-top: 2px solid #cbd5e1; }
        .signature p { font-weight: 700; color: var(--primary-color); margin-bottom: 5px; }

        /* =========================================
           RESPONSIVE STYLES (Mobile)
           ========================================= */
        @media only screen and (max-width: 768px) {
            body { padding: 10px; }
            .invoice-box { padding: 20px; }

            /* هدر ستونی شود */
            .header { flex-direction: column; align-items: center; text-align: center; gap: 20px; }
            .invoice-details { text-align: center; width: 100%; }
            .invoice-details p { justify-content: center; }

            /* جدول اطلاعات دو تیکه شود */
            .info-table, .info-table tbody, .info-table tr, .info-table td {
                display: block; width: 100% !important; box-sizing: border-box;
            }
            .info-table td { padding: 0 0 15px 0 !important; }

            /* تغییر استایل باکس اطلاعات برای موبایل */
            .info-box {
                border-right: 1px solid #e2e8f0;
                border-top: 5px solid var(--primary-color);
            }

            /* فوتر ستونی شود */
            .footer { flex-direction: column; gap: 40px; margin-top: 40px; }
            .signature { width: 100%; }
            .signature-line { margin-top: 40px; }

            /* جمع کل چپ چین نباشد */
            .total-row td { text-align: center !important; padding: 15px !important; font-size: 1.2em !important; }
        }

        @media print {
            body { background-color: #fff; padding: 0; display: block; }
            .invoice-box { box-shadow: none; border: none; padding: 0; max-width: 100%; margin: 0; border-radius: 0; }
            .actions-bar { display: none !important; }
            .website-badge { border: 1px solid #ccc; color: #000; background: none; }
            .terms { background-color: #fff; border: 1px solid #ccc; color: #000; }
            .info-box { border: 1px solid #ccc; }
            .table-responsive { border: none; overflow: visible; } /* حذف اسکرول در پرینت */
            .services-table { min-width: 100%; }
        }
    </style>
</head>
<body>

<div class="actions-bar">
    <button class="btn-print" onclick="window.print()">
        چاپ / دانلود PDF
    </button>
</div>

<div class="invoice-box">
    <div class="header">
        <div class="company-info">
            <h1>آرون سافت</h1>
            <span class="subtitle">خدمات تخصصی طراحی وب و دواپس (DevOps)</span>
            <div class="contact-header">
                <span>تلفن: 09122578260</span>
                <span>ایمیل: sales@aron-soft.com</span>
            </div>
        </div>
        <div class="invoice-details">
            <a href="https://aron-soft.com" target="_blank" class="website-badge">aron-soft.com</a>
            <p style="margin: 0; font-size: 0.9em; font-weight: 600;"><strong>تاریخ:</strong> ۰۳/۱۰/۱۴۰۴</p>
            <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #64748b;"><strong>شماره:</strong> 140401-AS</p>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                <div class="info-box">
                    <strong>کارفرما (مشتری)</strong>
                    <div class="info-row"><span class="info-label">نام:</span><span class="info-value">..............................</span></div>
                    <div class="info-row"><span class="info-label">پروژه:</span><span class="info-value">فروشگاه اینترنتی</span></div>
                    <div class="info-row"><span class="info-label">تلفن:</span><span class="info-value">..............................</span></div>
                </div>
            </td>
            <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                <div class="info-box">
                    <strong>مجری (پیمانکار)</strong>
                    <div class="info-row"><span class="info-label">شرکت:</span><span class="info-value">آرون سافت</span></div>
                    <div class="info-row"><span class="info-label">ایمیل:</span><span class="info-value-ltr">sales@aron-soft.com</span></div>
                    <div class="info-row"><span class="info-label">تماس:</span><span class="info-value-ltr">09122578260</span></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-responsive">
        <table class="services-table">
            <thead>
            <tr>
                <th class="row-idx">#</th>
                <th style="text-align: right;">شرح خدمات</th>
                <th style="text-align: right;">جزئیات فنی</th>
                <th class="price-col">مبلغ (تومان)</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="row-idx">۱</td>
                <td><strong>هسته فروشگاه و طراحی UI</strong></td>
                <td>نصب وردپرس/ووکامرس، قالب ریسپانسیو، پنل کاربری</td>
                <td class="price-col">۱۶,۰۰۰,۰۰۰</td>
            </tr>
            <tr>
                <td class="row-idx">۲</td>
                <td><strong>زیرساخت پرداخت آنلاین</strong></td>
                <td>اتصال درگاه بانکی، تسویه آنی، مشاوره اینماد</td>
                <td class="price-col">۳,۵۰۰,۰۰۰</td>
            </tr>
            <tr>
                <td class="row-idx">۳</td>
                <td><strong>سیستم‌های پرداخت پیشرفته</strong></td>
                <td>پرداخت در محل هوشمند (شرطی)، کارت‌به‌کارت</td>
                <td class="price-col">۴,۵۰۰,۰۰۰</td>
            </tr>
            <tr>
                <td class="row-idx">۴</td>
                <td><strong>ورود اطلاعات و سئو</strong></td>
                <td>دیتا‌گذاری ۵۰ محصول، بهینه‌سازی تصاویر و متا تگ</td>
                <td class="price-col">۴,۰۰۰,۰۰۰</td>
            </tr>
            <tr>
                <td class="row-idx">۵</td>
                <td><strong>امنیت و پشتیبانی</strong></td>
                <td>SSL، فایروال، کشینگ (Cache)، ۳ ماه پشتیبانی</td>
                <td class="price-col">۲,۰۰۰,۰۰۰</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" style="text-align: left; padding-left: 30px;">جمع کل قابل پرداخت:</td>
                <td class="price-col">۳۰,۰۰۰,۰۰۰</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="terms">
        <h3>شرایط پرداخت:</h3>
        <ul>
            <li><strong>۵۰٪ پیش‌پرداخت:</strong> مبلغ ۱۵,۰۰۰,۰۰۰ تومان (زمان عقد قرارداد).</li>
            <li><strong>۳۰٪ پس از تایید دمو:</strong> مبلغ ۹,۰۰۰,۰۰۰ تومان.</li>
            <li><strong>۲۰٪ تسویه نهایی:</strong> مبلغ ۶,۰۰۰,۰۰۰ تومان (زمان تحویل).</li>
        </ul>
    </div>

    <div class="footer">
        <div class="signature">
            <p>مهر و امضای کارفرما</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature">
            <p>مهر و امضای آرون سافت</p>
            <div class="signature-line"></div>
        </div>
    </div>
</div>

</body>
</html>
