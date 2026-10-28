@php $nid = 'nl-'.\Illuminate\Support\Str::random(4); @endphp
<div class="card p-5 text-center">
    <span class="icon-tile mx-auto mb-3"><i class="ph ph-envelope-simple" aria-hidden="true"></i></span>
    <h3 class="font-bold text-slate-900">خبرنامه آرون سافت</h3>
    <p class="mb-4 mt-1 text-xs leading-6 text-slate-600">تازه‌ترین مقالات را در ایمیل خود دریافت کنید. عضویت با ایمیل تأیید فعال می‌شود.</p>
    <form action="{{ route('newsletter.subscribe') }}" method="POST" data-ajax-form data-hide-on-success novalidate class="space-y-1 text-start">
        @csrf
        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div data-form-fields>
            <label for="{{ $nid }}-email" class="form-label">ایمیل</label>
            <input id="{{ $nid }}-email" type="email" name="email" required class="form-input mb-3 text-left" dir="ltr" autocomplete="email" placeholder="name@example.com">
            <x-captcha />
            <button type="submit" class="btn btn-primary btn-block">عضویت در خبرنامه</button>
        </div>
        <p data-form-msg class="form-msg" hidden aria-live="polite"></p>
    </form>
</div>
