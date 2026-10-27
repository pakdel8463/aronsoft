@php
    use App\Helpers\Fa;
    $number = Fa::toFa($proforma->id + 1000).'-AS';
    $date = Fa::toFa(jdate($proforma->created_at)->format('Y/m/d'));
    $total = (int) $proforma->total_amount;
    $parts = [['۵۰٪ پیش‌پرداخت', 0.5, 'زمان عقد قرارداد'], ['۳۰٪ پس از تأیید دمو', 0.3, null], ['۲۰٪ تسویه نهایی', 0.2, 'زمان تحویل']];
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>پیش‌فاکتور {{ $number }} | آرون سافت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn%5Bwght%5D.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('vendor/phosphor/phosphor.css') }}">
    @vite(['resources/css/front.css'])
    <style>@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: #fff !important; } @page { margin: 12mm; } }</style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased print:bg-white">
<div class="mx-auto max-w-4xl px-4 py-6 sm:py-10 print:max-w-none print:p-0">

    <div class="no-print mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ url('/') }}" class="link-arrow"><i class="ph ph-arrow-right" aria-hidden="true"></i> بازگشت به سایت</a>
        <button type="button" onclick="window.print()" class="btn btn-primary"><i class="ph ph-printer" aria-hidden="true"></i> چاپ / ذخیره PDF</button>
    </div>

    <main class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card print:rounded-none print:border-0 print:shadow-none" aria-labelledby="inv-title">
        <header class="flex flex-col gap-4 bg-slate-900 p-6 text-white sm:flex-row sm:items-start sm:justify-between sm:p-8 print:bg-slate-900">
            <div>
                <h1 id="inv-title" class="text-2xl font-extrabold">پیش‌فاکتور خدمات</h1>
                <p class="mt-1 text-sm text-slate-300">آرون سافت | طراحی وب و DevOps</p>
            </div>
            <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-sm">
                <dt class="text-slate-400">شماره</dt><dd class="font-bold" dir="ltr">{{ $number }}</dd>
                <dt class="text-slate-400">تاریخ</dt><dd class="font-bold">{{ $date }}</dd>
                <dt class="text-slate-400">وب‌سایت</dt><dd class="font-bold" dir="ltr">aron-soft.com</dd>
            </dl>
        </header>

        <div class="grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
            <section class="rounded-xl border border-slate-200 p-4" aria-labelledby="inv-client">
                <h2 id="inv-client" class="mb-2 text-sm font-extrabold text-slate-900">کارفرما</h2>
                <dl class="grid grid-cols-[4rem_1fr] gap-y-1.5 text-sm">
                    <dt class="text-slate-500">نام</dt><dd class="font-semibold" dir="auto">{{ $proforma->client_name }}</dd>
                    <dt class="text-slate-500">پروژه</dt><dd class="font-semibold" dir="auto">{{ $proforma->project_title }}</dd>
                    <dt class="text-slate-500">تماس</dt><dd class="font-semibold" dir="ltr" style="text-align:right">{{ $proforma->client_phone ?: $proforma->client_email ?: '—' }}</dd>
                </dl>
            </section>
            <section class="rounded-xl border border-slate-200 p-4" aria-labelledby="inv-vendor">
                <h2 id="inv-vendor" class="mb-2 text-sm font-extrabold text-slate-900">مجری</h2>
                <dl class="grid grid-cols-[4rem_1fr] gap-y-1.5 text-sm">
                    <dt class="text-slate-500">شرکت</dt><dd class="font-semibold">آرون سافت</dd>
                    <dt class="text-slate-500">ایمیل</dt><dd class="font-semibold" dir="ltr" style="text-align:right">sales@aron-soft.com</dd>
                    <dt class="text-slate-500">تماس</dt><dd class="font-semibold" dir="ltr" style="text-align:right">09122578260</dd>
                </dl>
            </section>
        </div>

        <section class="px-6 sm:px-8" aria-label="اقلام">
            <table class="hidden w-full border-collapse text-sm sm:table print:table">
                <thead><tr class="bg-brand-900 text-white print:bg-brand-900">
                    <th scope="col" class="w-12 p-3 text-center font-bold">#</th>
                    <th scope="col" class="p-3 text-start font-bold">شرح خدمات</th>
                    <th scope="col" class="p-3 text-start font-bold">توضیحات</th>
                    <th scope="col" class="p-3 text-start font-bold">مبلغ (تومان)</th>
                </tr></thead>
                <tbody>
                @foreach($proforma->items as $i => $item)
                    <tr class="border-b border-slate-200">
                        <td class="p-3 text-center text-slate-500">{{ Fa::toFa($i + 1) }}</td>
                        <td class="p-3 font-semibold text-slate-900" dir="auto">{{ $item['title'] ?? '' }}</td>
                        <td class="p-3 text-slate-600" dir="auto">{{ $item['description'] ?? '—' }}</td>
                        <td class="whitespace-nowrap p-3 font-bold">{{ Fa::toFa(number_format((int) ($item['price'] ?? 0))) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr class="bg-brand-50 print:bg-brand-50">
                    <td colspan="3" class="p-4 text-end font-extrabold text-slate-900">جمع کل قابل پرداخت</td>
                    <td class="whitespace-nowrap p-4 text-base font-extrabold text-tech-blue">{{ Fa::toFa(number_format($total)) }} تومان</td>
                </tr></tfoot>
            </table>

            <ul class="space-y-3 sm:hidden print:hidden">
                @foreach($proforma->items as $i => $item)
                    <li class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-bold text-slate-900" dir="auto"><span class="text-slate-400">{{ Fa::toFa($i + 1) }}.</span> {{ $item['title'] ?? '' }}</h3>
                            <span class="whitespace-nowrap text-sm font-extrabold">{{ Fa::toFa(number_format((int) ($item['price'] ?? 0))) }} <span class="text-xs font-normal text-slate-500">تومان</span></span>
                        </div>
                        @if(!empty($item['description']))<p class="mt-1.5 text-xs leading-6 text-slate-600" dir="auto">{{ $item['description'] }}</p>@endif
                    </li>
                @endforeach
                <li class="flex items-center justify-between rounded-xl bg-brand-50 p-4"><span class="font-extrabold text-slate-900">جمع کل</span><span class="font-extrabold text-tech-blue">{{ Fa::toFa(number_format($total)) }} تومان</span></li>
            </ul>
        </section>

        <section class="p-6 sm:p-8" aria-labelledby="inv-pay">
            <h2 id="inv-pay" class="mb-3 text-sm font-extrabold text-slate-900">شرایط پرداخت</h2>
            <ul class="space-y-1.5 text-sm leading-7 text-slate-700">
                @foreach($parts as [$label, $ratio, $when])
                    <li><strong>{{ $label }}:</strong> {{ Fa::toFa(number_format((int) round($total * $ratio))) }} تومان@if($when) ({{ $when }})@endif</li>
                @endforeach
            </ul>
        </section>

        <footer class="grid gap-8 border-t border-slate-200 p-6 sm:grid-cols-2 sm:p-8 print:grid-cols-2">
            <div class="text-center"><p class="mb-12 text-sm font-bold text-slate-700">مهر و امضای کارفرما</p><div class="border-t border-dashed border-slate-300"></div></div>
            <div class="text-center"><p class="mb-12 text-sm font-bold text-slate-700">مهر و امضای آرون سافت</p><div class="border-t border-dashed border-slate-300"></div></div>
        </footer>
    </main>
</div>
</body>
</html>
