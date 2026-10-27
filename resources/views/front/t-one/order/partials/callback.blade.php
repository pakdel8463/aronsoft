{{--
    فرم «درخواست تماس» سریع (فقط موبایل) — با fetch به leads.store ارسال می‌شود.
    استفاده:  @include('front.t-one.order.partials.callback', ['requestType' => 'مشاوره سئو'])
    پارامترهای اختیاری: requestType، button (متن دکمه)، placeholder، class (کلاس wrapper)
--}}
@php
    $cbId = 'cb'.\Illuminate\Support\Str::random(6);
    $cbType = $requestType ?? 'درخواست تماس';
    $cbButton = $button ?? 'درخواست مشاوره';
    $cbPlaceholder = $placeholder ?? 'شماره موبایل (مثلاً ۰۹۱۲…)';
@endphp
<div id="{{ $cbId }}" class="{{ $class ?? '' }}">
    <form method="POST" action="{{ route('leads.store') }}" novalidate
          class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto p-2 bg-white dark:bg-darkbg-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm">
        @csrf
        <input type="hidden" name="request_type" value="{{ $cbType }}">
        <input type="hidden" name="page" value="{{ '/'.ltrim(request()->path(), '/') }}">
        <div style="position:absolute;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
        </div>
        <div class="relative flex-grow">
            <label class="sr-only" for="{{ $cbId }}-m">شماره موبایل</label>
            <input id="{{ $cbId }}-m" type="tel" inputmode="tel" name="mobile" required maxlength="20" dir="ltr" autocomplete="tel"
                   placeholder="{{ $cbPlaceholder }}"
                   class="w-full px-4 py-3 bg-transparent font-medium focus:outline-none text-gray-900 dark:text-white placeholder-gray-400 text-center">
        </div>
        <button type="submit" class="px-8 py-3 bg-tech-blue text-white font-bold rounded-xl hover:bg-blue-600 transition-colors shrink-0 shadow-lg shadow-blue-500/20 whitespace-nowrap disabled:opacity-70">
            {{ $cbButton }}
        </button>
    </form>
    <p class="cb-msg hidden text-center text-sm font-medium mt-3" role="status" aria-live="polite"></p>
</div>
<script>
    (function () {
        var root = document.getElementById('{{ $cbId }}');
        var form = root.querySelector('form');
        var msg = root.querySelector('.cb-msg');
        var btn = form.querySelector('button[type=submit]');
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        function say(text, ok) {
            msg.textContent = text;
            msg.className = 'cb-msg text-center text-sm font-medium mt-3 ' + (ok ? 'text-green-600' : 'text-red-600');
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            btn.disabled = true;
            fetch(form.action, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'},
                body: new FormData(form),
                credentials: 'same-origin'
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (d) { return {res: res, d: d}; });
            }).then(function (r) {
                if (r.res.ok) { say(r.d.message || 'درخواست شما ثبت شد.', true); form.reset(); }
                else if (r.res.status === 422 && r.d.errors) { say(Object.values(r.d.errors)[0][0], false); }
                else { say(r.d.message || 'ثبت درخواست ممکن نشد. دوباره تلاش کنید.', false); }
            }).catch(function () { say('ارتباط با سرور برقرار نشد.', false); })
              .finally(function () { btn.disabled = false; });
        });
    })();
</script>
