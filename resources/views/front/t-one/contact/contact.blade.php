@php
    $c = config('aron_services.contact');
    $map = $c['map'];
    $d = 0.006; // نیم‌عرض کادر نقشه
    $bbox = implode(',', [$map['lng'] - $d * 1.6, $map['lat'] - $d, $map['lng'] + $d * 1.6, $map['lat'] + $d]);
    $field = 'w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-darkbg-950 text-gray-900 dark:text-white placeholder-gray-400 transition focus:outline-none focus:ring-2 focus:ring-tech-blue/40 focus:border-tech-blue';
    $label = 'block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2';
@endphp
@component('front.t-one.layouts.content')
    <header class="relative pt-32 pb-12 lg:pt-40 lg:pb-16 overflow-hidden">
        <div class="absolute inset-0 bg-dot-pattern text-gray-300 dark:text-tech-cyan/10 pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full border border-tech-blue/20 bg-tech-blue/5 text-tech-blue text-xs font-bold mb-6">
                <span class="w-2 h-2 bg-tech-green rounded-full me-2 animate-pulse"></span>
                پاسخگویی معمولاً در کمتر از ۲۴ ساعت
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 dark:text-white mb-5 leading-tight">
                با ما <span class="text-tech-blue">در ارتباط باشید</span>
            </h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-8 leading-relaxed">
                سؤال، ایده یا پروژه‌ای دارید؟ پیام بگذارید یا مستقیم تماس بگیرید؛ تیم آرون‌سافت کنار شماست.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="#contact-form-section" class="px-7 py-3 bg-tech-blue text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="ph ph-chat-centered-text"></i> ارسال پیام
                </a>
                <a href="{{ route('order.create') }}" class="px-7 py-3 bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition flex items-center gap-2">
                    <i class="ph ph-rocket-launch"></i> ثبت سفارش پروژه
                </a>
            </div>
        </div>
    </header>

    <section id="contact-form-section" class="pb-20 bg-white dark:bg-darkbg-950 pt-4">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-5 gap-8">

            {{-- ===== فرم ===== --}}
            <div class="lg:col-span-3 order-2 lg:order-1">
                <div class="bg-white dark:bg-darkbg-900 rounded-3xl border border-gray-200 dark:border-white/5 shadow-sm p-6 md:p-10" id="contact-card">

                    <div id="contact-success" class="hidden text-center py-10" role="status" aria-live="polite">
                        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-tech-green/10 text-tech-green">
                            <i class="ph-fill ph-check-circle text-4xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">پیام شما ارسال شد</h2>
                        <p id="contact-success-text" class="text-gray-500 dark:text-gray-400 leading-relaxed max-w-md mx-auto"></p>
                        <button type="button" id="contact-reset" class="mt-8 px-6 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition">ارسال پیام جدید</button>
                    </div>

                    <div id="contact-form-wrap">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">فرم تماس</h2>
                            <p class="text-gray-500 dark:text-gray-400">فیلدهای ستاره‌دار الزامی هستند. شماره شما فقط برای هماهنگی استفاده می‌شود.</p>
                        </div>

                        @if(session('success'))
                            <div class="mb-6 rounded-xl bg-tech-green/10 border border-tech-green/30 text-green-800 dark:text-green-300 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
                        @endif

                        <div id="contact-alert" class="hidden mb-6 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 text-sm font-medium" role="alert"></div>

                        <form id="contact-form" method="POST" action="{{ route('contact.store') }}" novalidate class="space-y-5">
                            @csrf
                            {{-- honeypot: برای انسان نامرئی --}}
                            <div style="position:absolute;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none" aria-hidden="true">
                                <label>وب‌سایت خود را وارد نکنید <input type="text" name="website" tabindex="-1" autocomplete="off" value=""></label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="{{ $label }}" for="c-name">نام و نام خانوادگی <span class="text-tech-red">*</span></label>
                                    <input class="{{ $field }}" type="text" id="c-name" name="name" value="{{ old('name') }}" placeholder="مثال: علی رضایی" autocomplete="name" required maxlength="120">
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" data-error-for="name">@error('name'){{ $message }}@enderror</p>
                                </div>
                                <div>
                                    <label class="{{ $label }}" for="c-mobile">شماره موبایل <span class="text-tech-red">*</span></label>
                                    <input class="{{ $field }} text-left" type="tel" inputmode="tel" id="c-mobile" name="mobile" value="{{ old('mobile') }}" placeholder="09123456789" dir="ltr" autocomplete="tel" required maxlength="20">
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" data-error-for="mobile">@error('mobile'){{ $message }}@enderror</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="{{ $label }}" for="c-email">ایمیل <span class="text-gray-400 font-normal">(اختیاری)</span></label>
                                    <input class="{{ $field }} text-left" type="email" id="c-email" name="email" value="{{ old('email') }}" placeholder="you@example.com" dir="ltr" autocomplete="email" maxlength="190">
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" data-error-for="email">@error('email'){{ $message }}@enderror</p>
                                </div>
                                <div>
                                    <label class="{{ $label }}" for="c-subject">موضوع</label>
                                    <select class="{{ $field }} cursor-pointer" id="c-subject" name="subject">
                                        <option value="">انتخاب کنید…</option>
                                        @foreach($subjects as $s)
                                            <option value="{{ $s }}" @selected(old('subject') === $s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" data-error-for="subject">@error('subject'){{ $message }}@enderror</p>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $label }}" for="c-message">پیام شما <span class="text-tech-red">*</span></label>
                                <textarea class="{{ $field }} resize-y" id="c-message" name="message" rows="5" placeholder="درباره پروژه یا سؤال خود بنویسید…" required minlength="10" maxlength="3000">{{ old('message') }}</textarea>
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" data-error-for="message">@error('message'){{ $message }}@enderror</p>
                            </div>

                            <div>
                                <x-captcha />
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" data-error-for="captcha"></p>
                            </div>

                            <button type="submit" id="contact-btn" class="w-full py-3.5 bg-tech-blue text-white rounded-xl font-bold hover:bg-blue-600 transition shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                <span id="contact-btn-text">ارسال پیام</span>
                                <span id="contact-spinner" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                            </button>
                            <p class="text-xs text-gray-400 text-center">با ارسال پیام، با تماس تیم آرون‌سافت موافقت می‌کنید.</p>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ===== اطلاعات تماس ===== --}}
            <aside class="lg:col-span-2 order-1 lg:order-2 space-y-4">
                <a href="tel:{{ preg_replace('/\D/', '', $c['phone']) }}" class="flex items-center gap-4 p-5 rounded-2xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 hover:border-tech-blue transition group shadow-sm">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-tech-blue/10 text-tech-blue flex items-center justify-center text-2xl"><i class="ph ph-phone"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">تلفن</div>
                        <div class="font-bold text-gray-900 dark:text-white group-hover:text-tech-blue transition">{{ $c['phone_display'] }}</div>
                    </div>
                </a>

                <a href="mailto:{{ $c['email'] }}" class="flex items-center gap-4 p-5 rounded-2xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 hover:border-tech-cyan transition group shadow-sm">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-tech-cyan/10 text-tech-cyan flex items-center justify-center text-2xl"><i class="ph ph-envelope-simple"></i></div>
                    <div class="min-w-0">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">ایمیل</div>
                        <div class="font-bold text-gray-900 dark:text-white group-hover:text-tech-cyan transition truncate" dir="ltr">{{ $c['email'] }}</div>
                    </div>
                </a>

                <a href="https://t.me/{{ $c['telegram'] }}" target="_blank" rel="noopener" class="flex items-center gap-4 p-5 rounded-2xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 hover:border-tech-purple transition group shadow-sm">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-tech-purple/10 text-tech-purple flex items-center justify-center text-2xl"><i class="ph ph-telegram-logo"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">تلگرام</div>
                        <div class="font-bold text-gray-900 dark:text-white group-hover:text-tech-purple transition" dir="ltr">&#64;{{ $c['telegram'] }}</div>
                    </div>
                </a>

                <div class="flex items-start gap-4 p-5 rounded-2xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 shadow-sm">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-tech-red/10 text-tech-red flex items-center justify-center text-2xl"><i class="ph ph-map-pin"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">آدرس دفتر</div>
                        <address class="not-italic font-bold text-gray-900 dark:text-white leading-relaxed">{{ $c['address'] }}</address>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-5 rounded-2xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 shadow-sm">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-tech-green/10 text-tech-green flex items-center justify-center text-2xl"><i class="ph ph-clock"></i></div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">ساعات کاری</div>
                        <div class="font-bold text-gray-900 dark:text-white">{{ $c['hours'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $c['hours_note'] }}</div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- ===== نقشه (OpenStreetMap، بدون کلید API) ===== --}}
    <section class="pb-24 bg-white dark:bg-darkbg-950">
        <div class="max-w-7xl mx-auto px-6">
            <div class="relative h-80 md:h-96 w-full rounded-3xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-sm bg-gray-100 dark:bg-darkbg-900">
                <iframe title="موقعیت آرون سافت روی نقشه" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="w-full h-full border-0"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&amp;layer=mapnik&amp;marker={{ $map['lat'] }}%2C{{ $map['lng'] }}"></iframe>
                <a href="https://www.openstreetmap.org/?mlat={{ $map['lat'] }}&amp;mlon={{ $map['lng'] }}#map={{ $map['zoom'] }}/{{ $map['lat'] }}/{{ $map['lng'] }}" target="_blank" rel="noopener"
                   class="absolute bottom-4 left-4 px-4 py-2 rounded-xl bg-white/95 dark:bg-darkbg-900/95 text-sm font-bold text-gray-800 dark:text-white shadow border border-gray-200 dark:border-white/10 hover:text-tech-blue transition flex items-center gap-2">
                    <i class="ph ph-arrow-square-out"></i> باز کردن نقشه
                </a>
            </div>
        </div>
    </section>

    @slot('bottomscript')
        <script>
            (function () {
                var form = document.getElementById('contact-form');
                if (!form) return;
                var btn = document.getElementById('contact-btn');
                var btnText = document.getElementById('contact-btn-text');
                var spinner = document.getElementById('contact-spinner');
                var alertBox = document.getElementById('contact-alert');
                var wrap = document.getElementById('contact-form-wrap');
                var success = document.getElementById('contact-success');
                var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

                function clearErrors() {
                    alertBox.classList.add('hidden');
                    form.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; });
                }
                function showAlert(msg) {
                    alertBox.textContent = msg;
                    alertBox.classList.remove('hidden');
                    alertBox.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
                function loading(on) {
                    btn.disabled = on;
                    spinner.classList.toggle('hidden', !on);
                    btnText.textContent = on ? 'در حال ارسال…' : 'ارسال پیام';
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    clearErrors();
                    loading(true);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'},
                        body: new FormData(form),
                        credentials: 'same-origin'
                    }).then(function (res) {
                        return res.json().catch(function () { return {}; }).then(function (data) { return {res: res, data: data}; });
                    }).then(function (r) {
                        if (r.res.ok) {
                            document.getElementById('contact-success-text').textContent = r.data.message || 'پیام شما ثبت شد.';
                            wrap.classList.add('hidden');
                            success.classList.remove('hidden');
                            form.reset();
                            success.scrollIntoView({behavior: 'smooth', block: 'center'});
                        } else if (r.res.status === 422 && r.data.errors) {
                            var first = null;
                            Object.keys(r.data.errors).forEach(function (key) {
                                var el = form.querySelector('[data-error-for="' + key + '"]');
                                if (el) { el.textContent = r.data.errors[key][0]; first = first || el; }
                            });
                            if (first) first.scrollIntoView({behavior: 'smooth', block: 'center'});
                        } else {
                            showAlert(r.data.message || 'ارسال پیام ممکن نشد. لطفاً دوباره تلاش کنید.');
                            if (r.res.status === 419) { /* نشست منقضی */ }
                        }
                    }).catch(function () {
                        showAlert('ارتباط با سرور برقرار نشد. اتصال اینترنت را بررسی کنید و دوباره تلاش کنید.');
                    }).finally(function () { loading(false); });
                });

                document.getElementById('contact-reset').addEventListener('click', function () {
                    success.classList.add('hidden');
                    wrap.classList.remove('hidden');
                    document.getElementById('c-name').focus();
                });
            })();
        </script>
    @endslot
@endcomponent
