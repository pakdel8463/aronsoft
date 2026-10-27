@php
    $field = 'w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-darkbg-950 text-gray-900 dark:text-white placeholder-gray-400 transition focus:outline-none focus:ring-2 focus:ring-tech-blue/40 focus:border-tech-blue';
    $label = 'block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2';
    $err = 'mt-1.5 text-xs text-red-600 dark:text-red-400';
    $stepTitles = ['نوع پروژه', 'جزئیات', 'اطلاعات تماس'];
@endphp
@component('front.t-one.layouts.content')
    <header class="relative pt-32 pb-10 lg:pt-40 overflow-hidden">
        <div class="absolute inset-0 bg-dot-pattern text-gray-300 dark:text-tech-cyan/10 pointer-events-none"></div>
        <div class="max-w-3xl mx-auto px-6 text-center relative z-10">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full border border-tech-blue/20 bg-tech-blue/5 text-tech-blue text-xs font-bold mb-6">
                <span class="w-2 h-2 bg-tech-green rounded-full me-2 animate-pulse"></span>
                مشاوره اولیه رایگان است
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 leading-tight">
                پروژه‌تان را <span class="text-tech-blue">همین‌جا شروع کنید</span>
            </h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 leading-relaxed">
                در سه مرحله کوتاه، نیازتان را بنویسید. کارشناسان ما با شما تماس می‌گیرند و برآورد هزینه و زمان را ارائه می‌دهند.
            </p>
        </div>
    </header>

    <section class="pb-24 bg-white dark:bg-darkbg-950">
        <div class="max-w-5xl mx-auto px-6">

            {{-- stepper --}}
            <ol id="order-stepper" class="flex items-center justify-between max-w-2xl mx-auto mb-10" aria-label="مراحل ثبت سفارش">
                @foreach($stepTitles as $i => $t)
                    <li class="flex items-center {{ $loop->last ? '' : 'flex-1' }}" data-step-ind="{{ $i + 1 }}">
                        <div class="flex flex-col items-center gap-2 relative">
                            <span class="step-dot w-10 h-10 rounded-full flex items-center justify-center text-sm font-extrabold border-2 border-gray-300 dark:border-white/20 text-gray-400 bg-white dark:bg-darkbg-950 transition">{{ \App\Helpers\Fa::toFa($i + 1) }}</span>
                            <span class="step-label text-xs font-bold text-gray-400 whitespace-nowrap transition">{{ $t }}</span>
                        </div>
                        @unless($loop->last)
                            <div class="step-line flex-1 h-0.5 mx-2 mb-6 bg-gray-200 dark:bg-white/10 transition"></div>
                        @endunless
                    </li>
                @endforeach
            </ol>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 bg-white dark:bg-darkbg-900 rounded-3xl border border-gray-200 dark:border-white/5 shadow-sm p-6 md:p-10">

                    @if(session('error'))
                        <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
                    @endif
                    <div id="order-alert" class="hidden mb-6 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 text-sm font-medium" role="alert"></div>

                    <form id="order-form" method="POST" action="{{ route('order.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div style="position:absolute;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none" aria-hidden="true">
                            <label>وب‌سایت خود را وارد نکنید <input type="text" name="website" tabindex="-1" autocomplete="off" value=""></label>
                        </div>

                        {{-- ===== مرحله ۱ ===== --}}
                        <div data-step="1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">چه چیزی نیاز دارید؟</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">نزدیک‌ترین گزینه را انتخاب کنید؛ بعداً می‌توانید توضیح بدهید.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($types as $key => $t)
                                    <label class="cursor-pointer block">
                                        <input type="radio" name="type" value="{{ $key }}" class="peer sr-only" @checked(old('type', $preselect) === $key)>
                                        <span class="flex items-start gap-4 p-4 rounded-2xl border-2 border-gray-200 dark:border-white/10 bg-white dark:bg-darkbg-950 transition hover:border-tech-blue/60 peer-checked:border-tech-blue peer-checked:bg-tech-blue/5 peer-focus-visible:ring-2 peer-focus-visible:ring-tech-blue/40">
                                            <span class="w-11 h-11 shrink-0 rounded-xl bg-tech-blue/10 text-tech-blue flex items-center justify-center text-2xl"><i class="ph {{ $t['icon'] }}"></i></span>
                                            <span class="block">
                                                <span class="block font-bold text-gray-900 dark:text-white">{{ $t['label'] }}</span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">{{ $t['hint'] }}</span>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="{{ $err }}" data-error-for="type">@error('type'){{ $message }}@enderror</p>
                        </div>

                        {{-- ===== مرحله ۲ ===== --}}
                        <div data-step="2" class="hidden">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">جزئیات پروژه</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">هرچه دقیق‌تر بنویسید، برآورد دقیق‌تری می‌گیرید. همه فیلدها اختیاری‌اند.</p>

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div id="budget-wrap">
                                        <label class="{{ $label }}" for="o-budget">بودجه تقریبی</label>
                                        <select class="{{ $field }} cursor-pointer" id="o-budget" name="budget">
                                            <option value="">انتخاب کنید…</option>
                                            @foreach($budgets as $k => $v)
                                                <option value="{{ $k }}" @selected(old('budget') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                        <p class="{{ $err }}" data-error-for="budget"></p>
                                    </div>
                                    <div>
                                        <label class="{{ $label }}" for="o-deadline">زمان مورد نظر برای تحویل</label>
                                        <select class="{{ $field }} cursor-pointer" id="o-deadline" name="deadline">
                                            <option value="">انتخاب کنید…</option>
                                            @foreach($deadlines as $k => $v)
                                                <option value="{{ $k }}" @selected(old('deadline') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                        <p class="{{ $err }}" data-error-for="deadline"></p>
                                    </div>
                                </div>

                                <div>
                                    <span class="{{ $label }}">امکانات مورد نیاز <span class="text-gray-400 font-normal">(چندانتخابی)</span></span>
                                    <div id="features-box" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5"></div>
                                    <p class="{{ $err }}" data-error-for="features"></p>
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="o-desc">توضیحات</label>
                                    <textarea class="{{ $field }} resize-y" id="o-desc" name="description" rows="5" maxlength="5000" placeholder="هدف پروژه، سایت‌های نمونه، محدودیت‌ها یا هر نکته‌ای که لازم است بدانیم…">{{ old('description') }}</textarea>
                                    <p class="{{ $err }}" data-error-for="description"></p>
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="o-file">فایل پیوست <span class="text-gray-400 font-normal">(اختیاری، حداکثر ۵ مگابایت)</span></label>
                                    <label for="o-file" class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 dark:border-white/15 cursor-pointer hover:border-tech-blue transition bg-gray-50 dark:bg-darkbg-950">
                                        <i class="ph ph-paperclip text-xl text-tech-blue"></i>
                                        <span id="file-name" class="text-sm text-gray-500 dark:text-gray-400 truncate">برای انتخاب فایل کلیک کنید (PDF، Word، Excel، تصویر، ZIP)</span>
                                    </label>
                                    <input class="sr-only" type="file" id="o-file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.jpg,.jpeg,.png,.webp">
                                    <p class="{{ $err }}" data-error-for="attachment"></p>
                                </div>
                            </div>
                        </div>

                        {{-- ===== مرحله ۳ ===== --}}
                        <div data-step="3" class="hidden">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">چطور با شما تماس بگیریم؟</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">اطلاعات شما فقط برای هماهنگی همین درخواست استفاده می‌شود.</p>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $label }}" for="o-name">نام و نام خانوادگی <span class="text-tech-red">*</span></label>
                                        <input class="{{ $field }}" type="text" id="o-name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" autocomplete="name" maxlength="120" placeholder="مثال: علی رضایی">
                                        <p class="{{ $err }}" data-error-for="name">@error('name'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label class="{{ $label }}" for="o-mobile">شماره موبایل <span class="text-tech-red">*</span></label>
                                        <input class="{{ $field }} text-left" type="tel" inputmode="tel" id="o-mobile" name="mobile" value="{{ old('mobile') }}" dir="ltr" autocomplete="tel" maxlength="20" placeholder="09123456789">
                                        <p class="{{ $err }}" data-error-for="mobile">@error('mobile'){{ $message }}@enderror</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $label }}" for="o-email">ایمیل <span class="text-gray-400 font-normal">(برای ارسال کد پیگیری)</span></label>
                                        <input class="{{ $field }} text-left" type="email" id="o-email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" dir="ltr" autocomplete="email" maxlength="190" placeholder="you@example.com">
                                        <p class="{{ $err }}" data-error-for="email">@error('email'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label class="{{ $label }}" for="o-time">زمان مناسب تماس</label>
                                        <select class="{{ $field }} cursor-pointer" id="o-time" name="contact_time">
                                            <option value="">انتخاب کنید…</option>
                                            @foreach($contactTimes as $k => $v)
                                                <option value="{{ $k }}" @selected(old('contact_time') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                        <p class="{{ $err }}" data-error-for="contact_time"></p>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $label }}">کد امنیتی <span class="text-tech-red">*</span></label>
                                    <div class="[&_.aron-captcha-field]:!mb-0 [&_.aron-captcha-field]:!flex-wrap [&_#aron-captcha-input]:!min-w-0 [&_#aron-captcha-input]:!w-full [&_#aron-captcha-input]:!rounded-xl [&_#aron-captcha-input]:!h-12 [&_img]:!h-12 [&_img]:!w-auto [&_img]:!shrink-0 [&_img]:!rounded-xl [&_button]:!shrink-0 [&_button]:!h-12 [&_button]:!rounded-xl">
                                        @include('aronlabs-captcha::captcha-input')
                                    </div>
                                    <p class="{{ $err }}" data-error-for="captcha"></p>
                                </div>

                                <div>
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="consent" value="1" class="mt-1 w-5 h-5 rounded border-gray-300 text-tech-blue focus:ring-tech-blue" @checked(old('consent'))>
                                        <span class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">با ثبت این درخواست، با تماس تیم آرون‌سافت (تلفنی یا ایمیلی) برای بررسی و ارائه پیشنهاد موافقم.</span>
                                    </label>
                                    <p class="{{ $err }}" data-error-for="consent">@error('consent'){{ $message }}@enderror</p>
                                </div>
                            </div>
                        </div>

                        {{-- ناوبری --}}
                        <div class="flex items-center justify-between gap-3 mt-10 pt-6 border-t border-gray-100 dark:border-white/5">
                            <button type="button" id="btn-prev" class="hidden px-6 py-3 rounded-xl border border-gray-200 dark:border-white/10 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition flex items-center gap-2">
                                <i class="ph ph-arrow-right"></i> مرحله قبل
                            </button>
                            <span id="nav-spacer"></span>
                            <button type="button" id="btn-next" class="px-8 py-3 bg-tech-blue text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/20 flex items-center gap-2">
                                مرحله بعد <i class="ph ph-arrow-left"></i>
                            </button>
                            <button type="submit" id="btn-submit" class="hidden px-8 py-3 bg-tech-blue text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/20 items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                <span id="submit-text">ثبت درخواست</span>
                                <span id="submit-spinner" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ستون کناری --}}
                <aside class="space-y-4 lg:sticky lg:top-28">
                    <div class="p-6 rounded-3xl bg-gray-50 dark:bg-darkbg-900 border border-gray-200 dark:border-white/5">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4">بعد از ثبت چه می‌شود؟</h3>
                        <ol class="space-y-4 text-sm">
                            <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-tech-blue/10 text-tech-blue font-extrabold flex items-center justify-center text-xs">۱</span><span class="text-gray-600 dark:text-gray-300 leading-relaxed">کد پیگیری می‌گیرید و درخواست‌تان بررسی می‌شود.</span></li>
                            <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-tech-blue/10 text-tech-blue font-extrabold flex items-center justify-center text-xs">۲</span><span class="text-gray-600 dark:text-gray-300 leading-relaxed">یک مشاور در بازه انتخابی با شما تماس می‌گیرد.</span></li>
                            <li class="flex gap-3"><span class="w-7 h-7 shrink-0 rounded-full bg-tech-blue/10 text-tech-blue font-extrabold flex items-center justify-center text-xs">۳</span><span class="text-gray-600 dark:text-gray-300 leading-relaxed">پیش‌فاکتور و زمان‌بندی شفاف دریافت می‌کنید.</span></li>
                        </ol>
                    </div>
                    <div class="p-6 rounded-3xl bg-tech-blue/5 border border-tech-blue/20">
                        <div class="font-bold text-gray-900 dark:text-white mb-1">ترجیح می‌دهید صحبت کنید؟</div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">همین حالا تماس بگیرید.</p>
                        <a href="tel:02122451912" class="inline-flex items-center gap-2 font-extrabold text-tech-blue"><i class="ph ph-phone"></i> {{ config('aron_services.contact.phone_display') }}</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @slot('bottomscript')
        <script>
            window.ORDER_TYPES = @json($types);
            window.ORDER_OLD_FEATURES = @json(array_values((array) old('features', [])));
            (function () {
                var form = document.getElementById('order-form');
                if (!form) return;
                var cap = document.getElementById('aron-captcha-input'); if (cap) { cap.placeholder = 'کد داخل تصویر'; cap.setAttribute('dir','ltr'); }
                var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
                var panels = [].slice.call(form.querySelectorAll('[data-step]'));
                var inds = [].slice.call(document.querySelectorAll('[data-step-ind]'));
                var btnPrev = document.getElementById('btn-prev');
                var btnNext = document.getElementById('btn-next');
                var btnSubmit = document.getElementById('btn-submit');
                var alertBox = document.getElementById('order-alert');
                var featuresBox = document.getElementById('features-box');
                var step = 1;

                var fieldStep = {type: 1, budget: 2, deadline: 2, features: 2, description: 2, attachment: 2};

                function digits(s) {
                    return String(s || '').replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
                        .replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
                }
                function setError(name, msg) {
                    var el = form.querySelector('[data-error-for="' + name + '"]');
                    if (el) el.textContent = msg || '';
                }
                function clearErrors() {
                    alertBox.classList.add('hidden');
                    form.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; });
                }
                function showAlert(msg) {
                    alertBox.textContent = msg;
                    alertBox.classList.remove('hidden');
                    alertBox.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
                function currentType() {
                    var r = form.querySelector('input[name="type"]:checked');
                    return r ? r.value : null;
                }

                function renderFeatures() {
                    var t = currentType();
                    var data = t && window.ORDER_TYPES[t] ? window.ORDER_TYPES[t].features : {};
                    var keep = [].slice.call(featuresBox.querySelectorAll('input:checked')).map(function (i) { return i.value; });
                    if (!keep.length && !featuresBox.dataset.touched) keep = window.ORDER_OLD_FEATURES || [];
                    featuresBox.innerHTML = '';
                    Object.keys(data).forEach(function (k) {
                        var wrap = document.createElement('label');
                        wrap.className = 'cursor-pointer block';
                        wrap.innerHTML =
                            '<input type="checkbox" name="features[]" class="peer sr-only">' +
                            '<span class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-darkbg-950 text-sm font-medium text-gray-700 dark:text-gray-200 transition hover:border-tech-blue/60 peer-checked:border-tech-blue peer-checked:bg-tech-blue/5 peer-checked:text-tech-blue peer-focus-visible:ring-2 peer-focus-visible:ring-tech-blue/40">' +
                            '<i class="ph ph-check-circle text-lg opacity-30"></i><span></span></span>';
                        var input = wrap.querySelector('input');
                        input.value = k;
                        input.checked = keep.indexOf(k) !== -1;
                        wrap.querySelector('span span').textContent = data[k];
                        input.addEventListener('change', function () { featuresBox.dataset.touched = '1'; });
                        featuresBox.appendChild(wrap);
                    });
                    // بودجه برای «مشاوره رایگان» معنا ندارد
                    document.getElementById('budget-wrap').classList.toggle('hidden', t === 'consult');
                    if (t === 'consult') document.getElementById('o-budget').value = '';
                }

                function paint() {
                    panels.forEach(function (p) { p.classList.toggle('hidden', Number(p.dataset.step) !== step); });
                    inds.forEach(function (li) {
                        var n = Number(li.dataset.stepInd);
                        var dot = li.querySelector('.step-dot');
                        var label = li.querySelector('.step-label');
                        var line = li.querySelector('.step-line');
                        var done = n < step, active = n === step;
                        dot.className = 'step-dot w-10 h-10 rounded-full flex items-center justify-center text-sm font-extrabold border-2 transition ' +
                            (done ? 'bg-tech-blue border-tech-blue text-white' : active ? 'border-tech-blue text-tech-blue bg-white dark:bg-darkbg-950 ring-4 ring-tech-blue/15' : 'border-gray-300 dark:border-white/20 text-gray-400 bg-white dark:bg-darkbg-950');
                        label.className = 'step-label text-xs font-bold whitespace-nowrap transition ' + (done || active ? 'text-gray-900 dark:text-white' : 'text-gray-400');
                        if (done) dot.innerHTML = '<i class="ph-bold ph-check"></i>';
                        else dot.textContent = '۱۲۳'.charAt(n - 1);
                        if (line) line.className = 'step-line flex-1 h-0.5 mx-2 mb-6 transition ' + (done ? 'bg-tech-blue' : 'bg-gray-200 dark:bg-white/10');
                    });
                    btnPrev.classList.toggle('hidden', step === 1);
                    btnNext.classList.toggle('hidden', step === 3);
                    btnSubmit.classList.toggle('hidden', step !== 3);
                    btnSubmit.classList.toggle('flex', step === 3);
                    window.scrollTo({top: Math.max(0, form.getBoundingClientRect().top + window.scrollY - 220), behavior: 'smooth'});
                }

                function validate(n) {
                    clearErrors();
                    var ok = true;
                    if (n === 1 && !currentType()) { setError('type', 'لطفاً نوع پروژه را انتخاب کنید.'); ok = false; }
                    if (n === 2) {
                        var f = document.getElementById('o-file').files[0];
                        if (f && f.size > 5 * 1024 * 1024) { setError('attachment', 'حجم فایل پیوست نباید بیش از ۵ مگابایت باشد.'); ok = false; }
                    }
                    if (n === 3) {
                        if (form.name.value.trim().length < 3) { setError('name', 'نام و نام خانوادگی را وارد کنید.'); ok = false; }
                        var m = digits(form.mobile.value).replace(/[\s\-()]/g, '');
                        if (!/^(\+98|0098|98|0)?9\d{9}$/.test(m)) { setError('mobile', 'شماره موبایل معتبر نیست (مثال: ۰۹۱۲۱۲۳۴۵۶۷).'); ok = false; }
                        if (form.email.value && !/^\S+@\S+\.\S+$/.test(form.email.value)) { setError('email', 'ایمیل معتبر نیست.'); ok = false; }
                        if (!document.getElementById('aron-captcha-input').value.trim()) { setError('captcha', 'کد امنیتی را وارد کنید.'); ok = false; }
                        if (!form.consent.checked) { setError('consent', 'برای ثبت درخواست باید با تماس تیم آرون‌سافت موافقت کنید.'); ok = false; }
                    }
                    return ok;
                }

                function go(n) { step = n; if (n === 2) renderFeatures(); paint(); }

                btnNext.addEventListener('click', function () { if (validate(step)) go(step + 1); });
                btnPrev.addEventListener('click', function () { clearErrors(); go(step - 1); });
                form.querySelectorAll('input[name="type"]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        setError('type', '');
                        featuresBox.dataset.touched = '1';
                        featuresBox.innerHTML = '';
                    });
                });
                document.getElementById('o-file').addEventListener('change', function () {
                    var f = this.files[0];
                    document.getElementById('file-name').textContent = f ? f.name : 'برای انتخاب فایل کلیک کنید (PDF، Word، Excel، تصویر، ZIP)';
                });
                // Enter در مراحل اول و دوم = مرحله بعد
                form.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && step < 3) { e.preventDefault(); btnNext.click(); }
                });

                function refreshCaptcha() {
                    var i = document.getElementById('aron-captcha-input'); if (i) i.value = '';
                    var r = document.getElementById('aron-refresh-captcha'); if (r) r.click();
                }
                function loading(on) {
                    btnSubmit.disabled = on;
                    document.getElementById('submit-spinner').classList.toggle('hidden', !on);
                    document.getElementById('submit-text').textContent = on ? 'در حال ثبت…' : 'ثبت درخواست';
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (step !== 3) { btnNext.click(); return; }
                    if (!validate(3)) return;
                    loading(true);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'},
                        body: new FormData(form),
                        credentials: 'same-origin'
                    }).then(function (res) {
                        return res.json().catch(function () { return {}; }).then(function (data) { return {res: res, data: data}; });
                    }).then(function (r) {
                        if (r.res.ok && r.data.redirect) {
                            window.location.href = r.data.redirect;
                            return;
                        }
                        loading(false);
                        if (r.res.status === 422 && r.data.errors) {
                            var minStep = 3;
                            Object.keys(r.data.errors).forEach(function (key) {
                                var base = key.split('.')[0];
                                setError(base, r.data.errors[key][0]);
                                minStep = Math.min(minStep, fieldStep[base] || 3);
                            });
                            if (r.data.errors.captcha) refreshCaptcha();
                            go(minStep);
                        } else {
                            showAlert(r.data.message || 'ثبت درخواست ممکن نشد. لطفاً دوباره تلاش کنید.');
                            refreshCaptcha();
                        }
                    }).catch(function () {
                        loading(false);
                        showAlert('ارتباط با سرور برقرار نشد. اتصال اینترنت را بررسی کنید و دوباره تلاش کنید.');
                    });
                });

                // اگر type از قبل انتخاب شده بود (لینک ?type=...) مستقیم به مرحله ۲
                @if($preselect && ! $errors->any())
                    step = 2; renderFeatures();
                @elseif($errors->any())
                    step = {{ $errors->has('type') ? 1 : ($errors->hasAny(['budget','deadline','features','description','attachment']) ? 2 : 3) }}; renderFeatures();
                @endif
                paint();
                window.scrollTo(0, 0);
            })();
        </script>
    @endslot
@endcomponent
