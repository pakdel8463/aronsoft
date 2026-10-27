@php
use App\View\Seo;
$url = url()->current();
$steps = [
 ['نصب پکیج','پکیج را از طریق Composer اضافه کنید. پکیج به صورت خودکار کشف می‌شود (Autodiscovery).',"composer require aronlabs/captcha"],
 ['انتشار فایل تنظیمات','فایل تنظیمات را منتشر کنید تا بتوانید نوع چالش (text یا math)، طول کد و تنظیمات GD را تغییر دهید. فایل در config/aron-captcha.php قرار می‌گیرد.',"php artisan vendor:publish --tag=aronlabs-captcha-config"],
 ['نمایش کپچا در فرم (Blade)','کد زیر شامل تصویر کپچا و فیلد ورودی پنهان (Session Key) است. آن را در هر فرمی که نیاز به اعتبارسنجی دارد قرار دهید:',"@include('aronlabs-captcha::captcha-input')"],
 ['اعتبارسنجی سمت سرور','در Controller یا Form Request از کلاس CaptchaRule استفاده کنید. نام فیلد باید با نام input در ویو مطابقت داشته باشد (پیش‌فرض: captcha).',"use AronLabs\\Captcha\\Rules\\CaptchaRule;\n\n\$request->validate([\n    'captcha' => ['required', new CaptchaRule],\n]);"],
 ['سفارشی‌سازی View و فونت (اختیاری)','برای تغییر ظاهر Blade و استفاده از فونت دلخواه، دستورات زیر را اجرا کنید. فایل‌ها در resources/views/vendor/aronlabs-captcha و public/vendor/aronlabs-captcha/fonts قرار می‌گیرند.',"php artisan vendor:publish --tag=aronlabs-captcha-views\nphp artisan vendor:publish --tag=aronlabs-captcha-fonts"],
 ['فعال‌سازی Ajax Refresh','برای عملکرد دکمه‌ی رفرش Ajax، مطمئن شوید @stack(\'scripts\') در انتهای Layout اصلی (قبل از بسته شدن body) وجود دارد.',"@stack('scripts')"],
];
$benefits = [
 ['ph-shield-check','امنیت هوشمند','پشتیبانی از چالش‌های ریاضی و متنی برای حفاظت مؤثر.'],
 ['ph-paint-brush','شخصی‌سازی کامل','کنترل بر ظاهر کپچا (رنگ‌ها، فونت Vazirmatn، نویز و ...).'],
 ['ph-arrows-clockwise','Ajax Refresh','تغییر کد کپچا بدون رفرش کامل صفحه.'],
 ['ph-plugs-connected','ادغام لاراول','استفاده‌ی آسان از CaptchaRule در سیستم اعتبارسنجی لاراول.'],
];
Seo::meta(['title'=>'Aron Captcha؛ کپچای فارسی و ایمن برای لاراول','description'=>'راهنمای نصب و استفاده از Aron Captcha: اعتبارسنجی ضد ربات فارسی، سفارشی‌سازی‌شدنی و با Ajax Refresh برای پروژه‌های لاراول.','canonical'=>$url]);
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd([Seo::breadcrumbs([['خانه', url('/')], ['Aron Captcha', $url]]), ['@type'=>'SoftwareSourceCode','name'=>'Aron Captcha','programmingLanguage'=>'PHP','runtimePlatform'=>'Laravel','codeRepository'=>'https://github.com/aronlabs/captcha','description'=>'کپچای فارسی برای لاراول','url'=>$url]]) !!}
    @endslot

    <section class="relative overflow-hidden">
        <div class="hero-glow pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-x relative py-12 text-center md:py-16">
            <div class="mx-auto max-w-3xl">
                @include('front.t-one.partials.breadcrumb', ['items' => [['خانه', url('/')], ['Aron Captcha', null]]])
                <span class="badge mb-4"><i class="ph ph-lock-key" aria-hidden="true"></i> ابزارهای امنیتی</span>
                <h1 class="h1-page"><span dir="ltr" class="inline-block text-tech-blue">Aron Captcha</span>؛ کپچای ایمن، سریع و فارسی برای پروژه‌های لاراول</h1>
                <p class="lead mx-auto mt-4 max-w-2xl">راهنمای جامع نصب، سفارشی‌سازی و استفاده از اعتبارسنجی ضد ربات در لاراول.</p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="#guide" class="btn btn-primary btn-lg">شروع راهنما (گام به گام)</a>
                    <a href="https://github.com/aronlabs/captcha" target="_blank" rel="noopener" class="btn btn-secondary btn-lg"><i class="ph ph-github-logo" aria-hidden="true"></i> مشاهده در GitHub</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section-alt border-y border-slate-200 py-10" aria-labelledby="cp-benefits">
        <div class="container-x">
            <h2 id="cp-benefits" class="sr-only">مزایای کلیدی</h2>
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($benefits as [$ic, $t, $d])
                    <li class="card reveal p-5"><span class="icon-tile mb-3"><i class="ph {{ $ic }}" aria-hidden="true"></i></span><h3 class="font-bold text-slate-900" dir="auto">{{ $t }}</h3><p class="mt-1 text-sm leading-7 text-slate-600">{{ $d }}</p></li>
                @endforeach
            </ul>
        </div>
    </section>

    <section id="guide" class="section" aria-labelledby="cp-guide">
        <div class="container-x max-w-3xl">
            <div class="section-head reveal"><h2 id="cp-guide" class="h2-section">راهنمای نصب و راه‌اندازی (گام به گام)</h2></div>
            <ol class="space-y-5">
                @foreach($steps as $i => [$t, $d, $code])
                    <li class="card reveal p-5 sm:p-6">
                        <div class="mb-3 flex items-center gap-3"><span class="step-num num">{{ \App\Helpers\Fa::toFa($i + 1) }}</span><h3 class="font-bold text-slate-900">{{ $t }}</h3></div>
                        <p class="mb-3 text-sm leading-8 text-slate-600">{{ $d }}</p>
                        <div class="relative" dir="ltr">
                            <pre class="code-block !m-0 overflow-x-auto text-[13px]"><code>{{ $code }}</code></pre>
                            <button type="button" class="copy-btn" onclick="copyCode(this)" aria-label="کپی کد">کپی</button>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container-x">
            <div class="rounded-3xl bg-slate-900 p-8 text-center sm:p-12">
                <h2 class="h2-section !text-white">آماده تقویت امنیت هستید؟</h2>
                <p class="mx-auto mt-3 max-w-xl text-slate-300">سؤالات خود را بپرسید یا نیازهای سفارشی‌سازی را با ما در میان بگذارید.</p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="https://github.com/aronlabs/captcha" target="_blank" rel="noopener" class="btn btn-lg bg-white text-slate-900 hover:bg-slate-100">مشاهده مستندات کامل</a>
                    <a href="{{ url('/contact') }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">تماس با پشتیبانی</a>
                </div>
            </div>
        </div>
    </section>
@endcomponent
