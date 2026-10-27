@php /** $title $text $type (order type) $primary $secondary */ @endphp
<section class="section" aria-labelledby="cta-title">
    <div class="container-x">
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-12 text-center sm:px-12 md:py-16">
            <div class="pointer-events-none absolute inset-0 opacity-30" style="background: radial-gradient(60% 80% at 50% 0%, #2f4ff5, transparent 70%)" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-2xl">
                <h2 id="cta-title" class="h2-section !text-white">{{ $title ?? 'آماده شروع پروژه هستید؟' }}</h2>
                <p class="mt-3 text-base leading-8 text-slate-300">{{ $text ?? 'نیازتان را ثبت کنید تا پیش‌فاکتور و زمان‌بندی اولیه را برایتان ارسال کنیم؛ بدون هزینه و تعهد.' }}</p>
                <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ url('/order'.(!empty($type) ? '?type='.$type : '')) }}" class="btn btn-lg w-full bg-white text-slate-900 hover:bg-slate-100 sm:w-auto">{{ $primary ?? 'ثبت سفارش پروژه' }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                    <a href="{{ url('/contact') }}" class="btn btn-lg w-full border border-white/30 text-white hover:bg-white/10 sm:w-auto">{{ $secondary ?? 'تماس با ما' }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
