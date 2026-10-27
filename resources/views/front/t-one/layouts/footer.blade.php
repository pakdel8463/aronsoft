@php
    $fcol1 = $globalMenus['footer-col-1'] ?? ($globalMenus['footer'] ?? null);
    $fcol2 = $globalMenus['footer-col-2'] ?? null;
    $year = \App\Helpers\Fa::toFa(\Morilog\Jalali\Jalalian::now()->getYear());
@endphp
<footer class="border-t border-slate-200 bg-slate-50 text-sm">
    <div class="container-x grid grid-cols-1 gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <a href="{{ url('/') }}" class="mb-4 inline-flex min-h-[44px] items-center gap-2 text-xl font-extrabold tracking-tight" aria-label="آرون سافت">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tech-blue text-lg font-bold text-white" aria-hidden="true">A</span>
                <span><span class="text-slate-900">آرون</span><span class="text-tech-blue">‌سافت</span></span>
            </a>
            <p class="mb-5 max-w-sm leading-8 text-slate-600">راهکارهای نرم‌افزاری، طراحی سایت و زیرساخت ابری برای کسب‌وکارهای در حال رشد.</p>
            <div class="flex gap-2">
                <a href="https://www.instagram.com/aron.soft/" target="_blank" rel="noopener" aria-label="اینستاگرام آرون سافت" class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-tech-blue hover:text-tech-blue"><i class="ph ph-instagram-logo text-xl" aria-hidden="true"></i></a>
                <a href="https://www.linkedin.com/company/77992207/" target="_blank" rel="noopener" aria-label="لینکدین آرون سافت" class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-tech-blue hover:text-tech-blue"><i class="ph ph-linkedin-logo text-xl" aria-hidden="true"></i></a>
                <a href="https://t.me/aronsoft" target="_blank" rel="noopener" aria-label="تلگرام آرون سافت" class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-tech-blue hover:text-tech-blue"><i class="ph ph-telegram-logo text-xl" aria-hidden="true"></i></a>
            </div>
        </div>

        <nav class="lg:col-span-2" aria-label="شرکت">
            <h2 class="mb-3 text-sm font-bold text-slate-900">شرکت</h2>
            <ul class="space-y-0.5">
                @if($fcol1)
                    @foreach($fcol1->tree() as $item)
                        <li><a href="{{ $item->href ?: '#' }}" class="inline-flex min-h-[44px] items-center text-slate-600 transition hover:text-tech-blue">{{ $item->display_title }}</a></li>
                    @endforeach
                @endif
            </ul>
        </nav>

        <nav class="lg:col-span-3" aria-label="خدمات">
            <h2 class="mb-3 text-sm font-bold text-slate-900">خدمات</h2>
            <ul class="space-y-0.5">
                @if($fcol2)
                    @foreach($fcol2->tree() as $item)
                        <li><a href="{{ $item->href ?: '#' }}" class="inline-flex min-h-[44px] items-center text-slate-600 transition hover:text-tech-blue">{{ $item->display_title }}</a></li>
                    @endforeach
                @endif
            </ul>
        </nav>

        <div class="lg:col-span-3">
            <h2 class="mb-3 text-sm font-bold text-slate-900">تماس با ما</h2>
            <ul class="space-y-3 text-slate-600">
                <li class="flex items-start gap-2"><i class="ph ph-map-pin mt-1 text-lg text-slate-400" aria-hidden="true"></i><span class="leading-7">تهران، بلوار ارتش، کوچه نصر ۲، پلاک ۸، واحد ۸</span></li>
                <li><a href="tel:+982122451912" class="inline-flex min-h-[44px] items-center gap-2 hover:text-tech-blue"><i class="ph ph-phone text-lg text-slate-400" aria-hidden="true"></i><span class="num" dir="ltr">021-22451912</span></a></li>
                <li><a href="mailto:info@aron-soft.com" class="inline-flex min-h-[44px] items-center gap-2 hover:text-tech-blue"><i class="ph ph-envelope text-lg text-slate-400" aria-hidden="true"></i><span dir="ltr">info@aron-soft.com</span></a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-slate-200">
        <div class="container-x flex flex-col items-center justify-between gap-2 py-5 text-xs text-slate-500 sm:flex-row">
            <p>© {{ $year }} تمامی حقوق برای آرون سافت محفوظ است.</p>
        </div>
    </div>
</footer>
