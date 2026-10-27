@php
    /** @var string $page  slug صفحه برای ثبت منبع لید | $type نوع درخواست | $cta متن دکمه | $withName | $withMessage | $id */
    $uid = 'lf-'.\Illuminate\Support\Str::random(5);
    $cta = $cta ?? 'درخواست تماس';
    $withName = $withName ?? true;
    $withMessage = $withMessage ?? false;
@endphp
<form action="{{ route('leads.store') }}" method="POST" data-ajax-form data-hide-on-success novalidate class="space-y-3 text-start" id="{{ $id ?? $uid }}">
    @csrf
    <input type="hidden" name="page" value="{{ $page ?? request()->path() }}">
    <input type="hidden" name="request_type" value="{{ $type ?? 'درخواست مشاوره' }}">
    <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div data-form-fields class="space-y-3">
        @if($withName)
            <div>
                <label for="{{ $uid }}-name" class="form-label">نام و نام خانوادگی</label>
                <input id="{{ $uid }}-name" type="text" name="name" class="form-input" autocomplete="name" maxlength="120" placeholder="مثال: علی رضایی">
            </div>
        @endif
        <div>
            <label for="{{ $uid }}-mobile" class="form-label">شماره موبایل <span class="text-red-600" aria-hidden="true">*</span></label>
            <input id="{{ $uid }}-mobile" type="tel" name="mobile" required class="form-input text-left" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="09123456789" aria-describedby="{{ $uid }}-help">
            <p id="{{ $uid }}-help" class="form-help">فقط برای تماس مشاور استفاده می‌شود.</p>
        </div>
        @if($withMessage)
            <div>
                <label for="{{ $uid }}-msg" class="form-label">توضیحات (اختیاری)</label>
                <textarea id="{{ $uid }}-msg" name="message" rows="3" maxlength="1000" class="form-input" placeholder="در یک‌دو جمله نیازتان را بنویسید"></textarea>
            </div>
        @endif
        <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="ph ph-phone-call text-lg" aria-hidden="true"></i><span>{{ $cta }}</span></button>
    </div>
    <p data-form-msg class="form-msg" hidden aria-live="polite"></p>
</form>
