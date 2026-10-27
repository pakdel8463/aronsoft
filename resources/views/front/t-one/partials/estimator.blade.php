@php
    // کاتالوگ قیمت از config/aron_services.php (منبع واحد، هم‌خوان با سرور)
    $__catalog = [];
    foreach ((array) config('aron_services.catalog', []) as $key => $cat) {
        $items = [];
        foreach ($cat['items'] as $id => $it) {
            $items[] = ['id' => $id, 'title' => $it['title'], 'price' => (int) $it['price'], 'parent' => $it['parent'] ?? null, 'required' => (bool) ($it['required'] ?? false)];
        }
        $__catalog[$key] = ['label' => $cat['label'], 'items' => $items];
    }
@endphp
<script>window.__ESTIMATOR__ = @json($__catalog, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);</script>

<div id="estimator-backdrop" onclick="toggleEstimator(false)" class="fixed inset-0 z-[90] hidden bg-slate-900/50 opacity-0 transition-opacity" aria-hidden="true"></div>

<div id="estimator-panel" role="dialog" aria-modal="true" aria-labelledby="estimator-title" aria-hidden="true" inert
     class="fixed inset-y-0 right-0 z-[100] flex h-full w-full max-w-md translate-x-full flex-col border-s border-slate-200 bg-white shadow-2xl transition-transform duration-300">

    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
            <h2 id="estimator-title" class="flex items-center gap-2 text-base font-extrabold text-slate-900"><i class="ph ph-calculator text-xl text-tech-blue" aria-hidden="true"></i> محاسبه آنلاین هزینه پروژه</h2>
            <p class="mt-1 text-xs text-slate-500">خدمت را انتخاب کنید؛ پیش‌فاکتور رسمی برایتان صادر می‌شود.</p>
        </div>
        <button type="button" onclick="toggleEstimator(false)" class="btn btn-ghost h-11 w-11 min-w-[44px] !p-0" aria-label="بستن"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
    </div>

    <div class="flex-1 space-y-5 overflow-y-auto p-5">
        <div>
            <label for="service-category" class="form-label">نوع خدمت</label>
            <select id="service-category" class="form-input select-rtl-fix">
                <option value="" disabled selected>یک خدمت را انتخاب کنید</option>
                @foreach($__catalog as $key => $cat)
                    <option value="{{ $key }}">{{ $cat['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div id="features-container" class="hidden space-y-2">
            <p class="text-sm font-bold text-slate-700">امکانات و ماژول‌ها</p>
            <div id="features-list" class="space-y-2"></div>
        </div>

        <fieldset class="space-y-3 rounded-xl border border-slate-200 p-4">
            <legend class="px-2 text-xs font-bold text-slate-600">مشخصات شما (موبایل یا ایمیل الزامی است)</legend>
            <div><label for="est-name" class="form-label">نام و نام خانوادگی</label><input type="text" id="est-name" class="form-input" autocomplete="name"></div>
            <div><label for="est-mobile" class="form-label">شماره موبایل</label><input type="tel" id="est-mobile" class="form-input text-left" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="09123456789"></div>
            <div><label for="est-email" class="form-label">ایمیل</label><input type="email" id="est-email" class="form-input text-left" dir="ltr" autocomplete="email" placeholder="name@example.com"></div>
            <input type="text" id="est-website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        </fieldset>
    </div>

    <div class="shrink-0 border-t border-slate-200 bg-white p-5 pb-safe shadow-[0_-8px_24px_rgba(15,23,42,.06)]">
        <div class="mb-3 flex items-end justify-between">
            <span class="text-sm font-medium text-slate-600">مبلغ برآوردی</span>
            <span><span id="est-total" class="num text-2xl font-extrabold text-tech-blue">۰</span> <span class="text-xs font-bold text-slate-500">تومان</span></span>
        </div>
        <button type="button" id="btn-submit-est" data-url="{{ route('proforma.store') }}" onclick="submitEstimation()" class="btn btn-primary btn-lg btn-block">
            <span id="btn-text">صدور پیش‌فاکتور رسمی</span>
            <i id="btn-loading" class="ph ph-circle-notch hidden animate-spin text-xl" aria-hidden="true"></i>
        </button>
        <p class="mt-2 text-center text-[11px] text-slate-500">قیمت‌ها برآوردی است و نهایی‌سازی پس از بررسی نیازمندی‌ها انجام می‌شود.</p>
        <p id="est-message" class="form-msg mt-3 text-center" role="status" hidden></p>
    </div>
</div>
