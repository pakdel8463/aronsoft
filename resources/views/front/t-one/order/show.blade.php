@php
    $steps = ['new' => 'ثبت شد', 'contacted' => 'تماس گرفته شد', 'quoted' => 'پیش‌فاکتور', 'won' => 'قرارداد'];
    $keys = array_keys($steps);
    $currentIdx = array_search($order->status, $keys, true);
    $lost = $order->status === 'lost';
@endphp
@component('front.t-one.layouts.content')
    <section class="relative pt-32 pb-24 lg:pt-40 bg-white dark:bg-darkbg-950 overflow-hidden min-h-[80vh]">
        <div class="absolute inset-0 bg-dot-pattern text-gray-300 dark:text-tech-cyan/10 pointer-events-none"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-6">

            <div class="text-center mb-10">
                @if($justCreated)
                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-tech-green/10 text-tech-green">
                        <i class="ph-fill ph-check-circle text-5xl"></i>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-3">درخواست شما ثبت شد</h1>
                    <p class="text-gray-500 dark:text-gray-400 leading-loose">
                        {{ $order->name }} عزیز، ممنون از اعتمادتان.
                        {{ $order->contact_time_label ? 'کارشناسان ما در بازه «'.$order->contact_time_label.'» با شما تماس می‌گیرند.' : 'کارشناسان ما در اولین فرصت با شما تماس می‌گیرند.' }}
                    </p>
                @else
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-3">پیگیری درخواست</h1>
                    <p class="text-gray-500 dark:text-gray-400">آخرین وضعیت درخواست شما در آرون‌سافت</p>
                @endif
            </div>

            {{-- کد پیگیری --}}
            <div class="rounded-3xl bg-tech-blue/5 border border-tech-blue/20 p-6 md:p-8 text-center mb-8">
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">کد پیگیری</div>
                <div class="flex items-center justify-center gap-3">
                    <span id="order-code" class="text-4xl md:text-5xl font-black tracking-widest text-tech-blue" dir="ltr">{{ $order->code }}</span>
                    <button type="button" id="copy-code" class="w-10 h-10 rounded-xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/10 text-gray-500 hover:text-tech-blue transition flex items-center justify-center" title="کپی کد">
                        <i class="ph ph-copy text-xl"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">این صفحه را نشانک (Bookmark) کنید یا کد را نگه دارید.@if($order->email) لینک پیگیری به ایمیل شما هم ارسال شد.@endif</p>
            </div>

            {{-- وضعیت --}}
            <div class="rounded-3xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 p-6 md:p-8 mb-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-bold text-gray-900 dark:text-white">وضعیت</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $lost ? 'bg-red-50 dark:bg-red-500/10 text-tech-red' : 'bg-tech-blue/10 text-tech-blue' }}">{{ $order->status_label }}</span>
                </div>
                @if($lost)
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">این درخواست بسته شده است. هر زمان خواستید می‌توانید یک <a href="{{ route('order.create') }}" class="text-tech-blue font-bold">درخواست جدید</a> ثبت کنید.</p>
                @else
                    <ol class="flex items-start justify-between">
                        @foreach($steps as $k => $t)
                            @php $done = $currentIdx !== false && array_search($k, $keys, true) <= $currentIdx; @endphp
                            <li class="flex-1 flex flex-col items-center text-center relative">
                                @unless($loop->first)
                                    <span class="absolute top-4 right-1/2 w-full h-0.5 {{ $done ? 'bg-tech-blue' : 'bg-gray-200 dark:bg-white/10' }}"></span>
                                @endunless
                                <span class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-sm {{ $done ? 'bg-tech-blue text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-400' }}">
                                    <i class="ph-bold {{ $done ? 'ph-check' : 'ph-circle' }}"></i>
                                </span>
                                <span class="mt-2 text-xs font-bold {{ $done ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">{{ $t }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            {{-- خلاصه --}}
            <div class="rounded-3xl bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/5 p-6 md:p-8 shadow-sm">
                <h2 class="font-bold text-gray-900 dark:text-white mb-5">خلاصه درخواست</h2>
                <dl class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    <div class="py-3 flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">نوع پروژه</dt><dd class="font-bold text-gray-900 dark:text-white">{{ $order->type_label }}</dd></div>
                    @if($order->budget_label)<div class="py-3 flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">بودجه</dt><dd class="font-bold text-gray-900 dark:text-white">{{ $order->budget_label }}</dd></div>@endif
                    @if($order->deadline_label)<div class="py-3 flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">زمان‌بندی</dt><dd class="font-bold text-gray-900 dark:text-white">{{ $order->deadline_label }}</dd></div>@endif
                    @if($order->contact_time_label)<div class="py-3 flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">زمان تماس</dt><dd class="font-bold text-gray-900 dark:text-white">{{ $order->contact_time_label }}</dd></div>@endif
                    @if($order->feature_labels)
                        <div class="py-3">
                            <dt class="text-gray-500 dark:text-gray-400 mb-2">امکانات</dt>
                            <dd class="flex flex-wrap gap-2">
                                @foreach($order->feature_labels as $f)
                                    <span class="px-3 py-1 rounded-lg bg-tech-blue/5 text-tech-blue text-xs font-bold">{{ $f }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                    @if($order->description)
                        <div class="py-3"><dt class="text-gray-500 dark:text-gray-400 mb-2">توضیحات</dt><dd class="text-gray-700 dark:text-gray-300 leading-loose whitespace-pre-line">{{ $order->description }}</dd></div>
                    @endif
                    @if($order->attachment_name)
                        <div class="py-3 flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">پیوست</dt><dd class="font-bold text-gray-900 dark:text-white truncate"><i class="ph ph-paperclip"></i> {{ $order->attachment_name }}</dd></div>
                    @endif
                    <div class="py-3 flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">تاریخ ثبت</dt><dd class="font-bold text-gray-900 dark:text-white">{{ \Morilog\Jalali\Jalalian::fromCarbon($order->created_at)->format('Y/m/d - H:i') }}</dd></div>
                </dl>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="px-7 py-3 bg-tech-blue text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/20">بازگشت به صفحه اصلی</a>
                <a href="{{ route('contact') }}" class="px-7 py-3 bg-white dark:bg-darkbg-900 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">تماس با ما</a>
            </div>
        </div>
    </section>

    @slot('bottomscript')
        <script>
            (function () {
                var b = document.getElementById('copy-code');
                if (!b) return;
                b.addEventListener('click', function () {
                    var code = document.getElementById('order-code').textContent.trim();
                    var done = function () { b.innerHTML = '<i class="ph ph-check text-xl text-tech-green"></i>'; setTimeout(function () { b.innerHTML = '<i class="ph ph-copy text-xl"></i>'; }, 1500); };
                    if (navigator.clipboard) navigator.clipboard.writeText(code).then(done);
                });
            })();
        </script>
    @endslot
@endcomponent
