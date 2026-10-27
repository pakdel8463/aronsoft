{{--
    Override ظاهری و دسترس‌پذیر برای aronlabs/captcha (بدون اسکریپت inline؛ منطق در resources/js/front.js).
    شناسه‌ها (aron-captcha-input / aron-refresh-captcha / aron-captcha-image) برای اولین نمونه‌ی هر صفحه
    حفظ شده‌اند تا اسکریپت‌های فرم سفارش/تماس کار کنند. نمونه‌های بعدی در همان صفحه تصویر خود را
    هنگام فوکوس دریافت می‌کنند (کد کپچا در session یکی است).
--}}
@php
    $__capIdx = $GLOBALS['__aron_captcha_idx'] = ($GLOBALS['__aron_captcha_idx'] ?? 0) + 1;
    $__sfx = $__capIdx === 1 ? '' : '-'.$__capIdx;
    $__first = $__capIdx === 1;
@endphp
<div class="aron-captcha-field" data-captcha data-refresh-url="{{ route('aron-captcha.refresh') }}" @unless($__first) data-lazy="1" @endunless>
    <img src="{{ $__first ? app('aronlabs-captcha')->generateBase64Image() : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' }}"
         alt="تصویر کد امنیتی" width="140" height="44" id="aron-captcha-image{{ $__sfx }}" data-captcha-image>
    <button type="button" id="aron-refresh-captcha{{ $__sfx }}" data-captcha-refresh class="aron-captcha-refresh" aria-label="تصویر جدید">
        <i class="ph ph-arrows-clockwise" aria-hidden="true"></i>
    </button>
    <label for="aron-captcha-input{{ $__sfx }}" class="sr-only">کد داخل تصویر</label>
    <input type="text" name="captcha" id="aron-captcha-input{{ $__sfx }}" data-captcha-input class="form-input"
           placeholder="کد داخل تصویر" required autocomplete="off" dir="ltr" inputmode="text" maxlength="12"
           @error('captcha') aria-invalid="true" @enderror>
    @error('captcha')
        <p class="aron-captcha-error" role="alert">{{ $message }}</p>
    @enderror
</div>
