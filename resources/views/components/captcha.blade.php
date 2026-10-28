{{--
    کپچای نامرئی (proof-of-work). داخل هر <form> بگذارید: <x-captcha />  — در کنترلر: 'captcha' => \App\Captcha\Captcha::rules()
    مرورگر معما را در پس‌زمینه حل می‌کند (public/js/captcha.js)؛ کاربر چیزی تایپ نمی‌کند.
    کلاس‌ها و آیکن‌هایی که اسکریپت تنظیم می‌کند (برای build تیلویند و زیرمجموعه‌ی آیکن‌ها):
    ph-circle-notch animate-spin ph-shield-check text-emerald-600 ph-warning-circle text-red-600
--}}
@props(['dark' => false])
@if (\App\Captcha\Captcha::enabled())
    <div data-pow-captcha data-challenge="{{ json_encode(\App\Captcha\Captcha::challenge()) }}" data-refresh-url="{{ route('captcha.challenge') }}"
         {{ $attributes->class(['flex items-center gap-2 text-xs', $dark ? 'text-slate-300' : 'text-slate-500']) }}>
        <input type="hidden" name="captcha" value="" data-pow-value>
        <span class="relative grid h-5 w-5 shrink-0 place-items-center" aria-hidden="true">
            <i class="ph ph-shield-check text-base" data-pow-icon></i>
        </span>
        <span data-pow-status aria-live="polite">بررسی امنیتی خودکار…</span>
        <noscript><span class="font-bold text-red-600">برای ارسال فرم، جاوااسکریپت مرورگر را فعال کنید.</span></noscript>
    </div>
    @error('captcha')<p class="form-error" role="alert">{{ $message }}</p>@enderror
    @once
        <script src="{{ asset('js/captcha.js') }}?v={{ @filemtime(public_path('js/captcha.js')) ?: 1 }}" defer></script>
    @endonce
@endif
