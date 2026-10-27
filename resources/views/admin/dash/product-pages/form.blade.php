@php
    use App\Support\ProductPageSchema as Schema;

    $exists = $page->exists;
    $statusValue = old('status', $page->status ?: 'draft');
    $publishedAt = old('published_at', $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '');
    $tplKey = $page->templateKey();
    $tplDef = Schema::template($tplKey);
    $palDefs = Schema::palette($tplKey);
    $palStored = (array) (session()->hasOldInput() ? old('sections._palette', []) : (($page->sections ?? [])['_palette'] ?? []));
    $palVals = Schema::paletteValues($tplKey, $palStored);
    $palFirst = array_values($palVals);
    $accent = $palFirst[0] ?? '#2F4FF5';
    $accent2 = $palFirst[1] ?? $accent;
    $iconValue = (string) old('icon', $page->icon);
    $siteHost = parse_url(url('/'), PHP_URL_HOST) ?: 'example.com';
    $action = $exists ? route('panel.product-pages.update', $page) : route('panel.product-pages.store');
    $faNum = fn ($v) => \App\Helpers\Fa::toFa((string) $v);

    // چیدمان بخش‌ها: ورودی قبلی (در صورت خطای اعتبارسنجی) یا ذخیره‌شده
    $rows = $page->layoutRows();
    if (session()->hasOldInput() && is_array(old('layout'))) {
        $rows = Schema::normalizeLayout(array_map(fn ($k) => ['key' => $k, 'enabled' => ! empty(old('enabled.' . $k))], old('layout')), $tplKey);
    }
    $errorKeys = collect($errors->keys());
    $secHasError = fn ($k) => $errorKeys->contains(fn ($e) => str_starts_with($e, "sections.{$k}.") || $e === "sections.{$k}");
    $defs = Schema::sections($tplKey);
    $icons = Schema::icons();
@endphp
<x-admin.layout :title="$exists ? 'ویرایش صفحه‌ی محصول' : 'صفحه‌ی محصول جدید'"
                :subtitle="$exists ? $page->name : 'اطلاعات محصول و بخش‌های صفحه‌ی معرفی را کامل کنید.'"
                :breadcrumbs="[['label' => 'لندینگ محصولات', 'url' => route('panel.product-pages.index')], ['label' => $exists ? 'ویرایش' : 'صفحه‌ی جدید']]"
                width="max-w-[1500px]">
    @if ($exists)
        <x-slot:actions>
            <x-admin.button :href="$page->publicUrl()" target="_blank" rel="noopener" variant="secondary" size="sm" icon="arrow-square-out">{{ $page->isLive() ? 'مشاهده در سایت' : 'پیش‌نمایش' }}</x-admin.button>
        </x-slot:actions>
    @endif

    <form method="POST" action="{{ $action }}" id="pp-form" data-pp-form novalidate autocomplete="off"
          data-base="{{ asset('') }}" data-site="{{ $siteHost }}" data-name-suffix="{{ config('app.name') }}">
        @csrf
        @if ($exists) @method('PUT') @else <input type="hidden" name="template" value="{{ $tplKey }}"> @endif

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
            {{-- ================= ستون اصلی ================= --}}
            <div class="min-w-0 space-y-5">
                <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-line bg-white px-4 py-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-aron-50 text-xl text-aron-600"><i class="ph ph-palette"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-400">قالب این صفحه</p>
                        <p class="truncate text-sm font-extrabold text-ink">{{ $tplDef['label'] ?? $tplKey }}</p>
                    </div>
                    <span class="flex gap-1" aria-hidden="true">@foreach ($palVals as $hex)<i class="size-4 rounded-full border border-black/10" style="background:{{ $hex }}"></i>@endforeach</span>
                    @if ($exists)
                        <button type="submit" form="pp-sample-form" class="a-btn a-btn-ghost a-btn-sm" data-pp-confirm="محتوای همه‌ی بخش‌ها با نمونه‌ی قالب جایگزین می‌شود و تغییرات قبلی از بین می‌رود. ادامه می‌دهید؟"><i class="ph ph-arrows-clockwise text-[1.15em]"></i>بارگذاری محتوای نمونه‌ی قالب</button>
                    @endif
                </div>

                <x-admin.card title="اطلاعات پایه" subtitle="نام و نشانی محصول؛ در فهرست محصولات و بالای صفحه استفاده می‌شود." icon="identification-card">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="name" id="pp_name" label="نام محصول" required maxlength="120" :value="$page->name" placeholder="مثلاً کالریتو" />
                        <x-admin.input name="name_en" id="pp_name_en" label="نام انگلیسی" ltr maxlength="120" :value="$page->name_en" placeholder="Calorieto" />
                        <div class="sm:col-span-2">
                            <x-admin.field label="نشانی صفحه (slug)" name="slug" for="pp_slug" hint="فقط حروف کوچک انگلیسی، عدد و خط‌تیره. خالی بگذارید تا از نام انگلیسی ساخته شود.">
                                <div class="flex items-stretch overflow-hidden rounded-2xl border border-line bg-white focus-within:border-aron-400 focus-within:ring-4 focus-within:ring-aron-100 {{ $errors->has('slug') ? '!border-coral-400' : '' }}" dir="ltr">
                                    <span class="flex shrink-0 items-center bg-slate-50 px-3 text-xs font-bold text-slate-500">{{ $siteHost }}/products/</span>
                                    <input type="text" name="slug" id="pp_slug" value="{{ old('slug', $page->slug) }}" maxlength="100" dir="ltr" autocomplete="off" spellcheck="false" placeholder="calorieto"
                                           class="h-11 min-w-0 flex-1 border-0 bg-transparent px-3 text-sm font-bold text-ink outline-none">
                                </div>
                            </x-admin.field>
                        </div>
                        <div class="sm:col-span-2"><x-admin.input name="tagline" id="pp_tagline" label="شعار / توضیح یک‌خطی" maxlength="220" :value="$page->tagline" /></div>
                        <x-admin.input name="category_label" id="pp_category" label="برچسب دسته" maxlength="80" :value="$page->category_label" hint="مثلاً «اپلیکیشن موبایل» یا «نرم‌افزار ابری»؛ روی کارت محصول نمایش داده می‌شود." />
                    </div>
                </x-admin.card>

                <div>
                    <div class="mb-3 flex flex-wrap items-end justify-between gap-2 px-1">
                        <div>
                            <h2 class="text-base font-extrabold text-ink">بخش‌های صفحه</h2>
                            <p class="mt-0.5 text-xs leading-5 text-slate-500">ترتیب بخش‌ها را با دکمه‌های ↑ ↓ تغییر دهید و بخش‌های اضافه را خاموش کنید.</p>
                        </div>
                        <div class="flex gap-1.5">
                            <button type="button" class="a-btn a-btn-ghost a-btn-sm" data-pp-expand="all"><i class="ph ph-arrows-out-line-vertical text-[1.15em]"></i>باز کردن همه</button>
                            <button type="button" class="a-btn a-btn-ghost a-btn-sm" data-pp-expand="none"><i class="ph ph-arrows-in-line-vertical text-[1.15em]"></i>بستن همه</button>
                        </div>
                    </div>
                    <div class="space-y-3" data-pp-sections>
                        @foreach ($rows as $row)
                            @php $k = $row['key']; $def = $defs[$k]; @endphp
                            @include('admin.dash.product-pages._section', [
                                'page' => $page, 'key' => $k, 'def' => $def, 'enabled' => (bool) $row['enabled'],
                                'open' => $k === 'hero' || $secHasError($k),
                            ])
                        @endforeach
                    </div>
                    <p class="sr-only" role="status" aria-live="polite" data-pp-live></p>
                </div>

                {{-- ------------- سئو ------------- --}}
                <x-admin.card title="سئو" subtitle="عنوان و توضیحی که در نتایج گوگل و شبکه‌های اجتماعی دیده می‌شود." icon="magnifying-glass">
                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between"><label for="pp_seo_title" class="a-label !mb-0">عنوان سئو</label><span class="text-[11px] font-bold text-slate-400" data-pp-count-for="pp_seo_title" data-limit="60"></span></div>
                                <input type="text" name="seo_title" id="pp_seo_title" value="{{ old('seo_title', $page->seo_title) }}" maxlength="190" placeholder="خالی = نام محصول" class="a-input {{ $errors->has('seo_title') ? 'has-error' : '' }}">
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-0 rounded-full bg-mint-500 transition-all" data-pp-bar-for="pp_seo_title"></div></div>
                                @error('seo_title')<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <div class="mb-1.5 flex items-center justify-between"><label for="pp_seo_description" class="a-label !mb-0">توضیح سئو</label><span class="text-[11px] font-bold text-slate-400" data-pp-count-for="pp_seo_description" data-limit="160"></span></div>
                                <textarea name="seo_description" id="pp_seo_description" rows="3" maxlength="320" placeholder="خالی = شعار محصول" class="a-textarea {{ $errors->has('seo_description') ? 'has-error' : '' }}">{{ old('seo_description', $page->seo_description) }}</textarea>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-0 rounded-full bg-mint-500 transition-all" data-pp-bar-for="pp_seo_description"></div></div>
                                @error('seo_description')<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                            </div>
                            <x-admin.input name="seo_keywords" id="pp_seo_keywords" label="کلمات کلیدی" maxlength="300" :value="$page->seo_keywords" hint="با ویرگول جدا کنید (اختیاری)." />
                            <x-admin.input name="canonical_url" id="pp_canonical" label="آدرس canonical" ltr maxlength="500" :value="$page->canonical_url" placeholder="https://…" hint="فقط اگر این محتوا در آدرس دیگری منتشر شده است." />
                            <x-admin.toggle name="noindex" label="عدم نمایش در موتورهای جستجو (noindex)" hint="صفحه برای گوگل پنهان می‌ماند." :checked="(bool) $page->noindex" />
                        </div>
                        <div>
                            <p class="a-label">پیش‌نمایش در گوگل</p>
                            <div class="rounded-2xl border border-line bg-white p-4" data-pp-snippet>
                                <div class="flex items-center gap-2 text-xs text-slate-500" dir="ltr"><span class="grid size-6 place-items-center rounded-full bg-slate-100"><i class="ph ph-globe text-sm"></i></span><span class="truncate" data-pp-snip-url>{{ $siteHost }} › products › {{ $page->slug }}</span></div>
                                <p class="mt-2 line-clamp-2 text-lg font-bold leading-7 text-[#1a0dab]" data-pp-snip-title>{{ $page->seo_title ?: $page->name }}</p>
                                <p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600" data-pp-snip-desc>{{ $page->seo_description ?: $page->tagline }}</p>
                            </div>
                            <p class="mt-2 text-xs leading-5 text-slate-400">طول ایده‌آل: عنوان تا ۶۰ و توضیح تا ۱۶۰ نویسه. اگر خالی بماند، نام و شعار محصول جایگزین می‌شود.</p>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            {{-- ================= ستون کناری ================= --}}
            <aside class="min-w-0 space-y-5 xl:sticky xl:top-20">
                <x-admin.card title="انتشار" icon="paper-plane-tilt">
                    <div class="space-y-4">
                        <x-admin.select name="status" id="pp_status" label="وضعیت" :options="\App\Models\ProductPage::STATUSES" :selected="$statusValue" />
                        <x-admin.field label="زمان انتشار" name="published_at" for="pp_published_at" hint="خالی = همین حالا (برای وضعیت «منتشرشده»). زمان آینده یعنی انتشار زمان‌بندی‌شده.">
                            <input type="datetime-local" name="published_at" id="pp_published_at" value="{{ $publishedAt }}" dir="ltr" class="a-input {{ $errors->has('published_at') ? 'has-error' : '' }}">
                        </x-admin.field>
                        <x-admin.input name="sort_order" id="pp_sort" type="number" label="ترتیب نمایش" min="0" max="100000" step="1" :value="$page->sort_order ?? 0" hint="عدد کمتر = بالاتر در فهرست محصولات." />
                        <x-admin.button type="submit" icon="floppy-disk" loading class="w-full">{{ $exists ? 'ذخیره‌ی تغییرات' : 'ایجاد صفحه' }}</x-admin.button>
                        @if ($exists)
                            <x-admin.button :href="$page->publicUrl()" target="_blank" rel="noopener" variant="secondary" icon="arrow-square-out" class="w-full">{{ $page->isLive() ? 'مشاهده در سایت' : 'پیش‌نمایش پیش‌نویس' }}</x-admin.button>
                            <p class="text-center text-xs text-slate-400">آخرین ویرایش: {{ jdate($page->updated_at)->format('Y/m/d H:i') }}</p>
                        @endif
                    </div>
                </x-admin.card>

                <x-admin.card title="رنگ‌های قالب" subtitle="رنگ‌های اصلی این قالب؛ خالی بگذارید تا رنگ پیش‌فرض قالب استفاده شود." icon="palette">
                    <div class="space-y-4" data-pp-brand>
                        @foreach ($palDefs as $pi => $pd)
                            @php
                                $pk = $pd['key'];
                                $pv = strtoupper((string) ($palStored[$pk] ?? ''));
                                $jsName = $pi === 0 ? 'accent' : ($pi === 1 ? 'accent_2' : 'pal_' . $pk);
                                $shown = preg_match('/^#[0-9A-F]{6}$/', $pv) ? $pv : $pd['default'];
                            @endphp
                            <div>
                                <label for="pp_pal_{{ $pk }}" class="a-label">{{ $pd['label'] }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="pp_pal_{{ $pk }}_picker" value="{{ strtolower($shown) }}" class="size-11 shrink-0 cursor-pointer rounded-xl border border-line bg-white p-1" aria-label="انتخاب رنگ: {{ $pd['label'] }}" data-pp-color="{{ $jsName }}">
                                    <input type="text" name="sections[_palette][{{ $pk }}]" id="pp_pal_{{ $pk }}" value="{{ $pv }}" dir="ltr" maxlength="7" placeholder="{{ $pd['default'] }}" autocomplete="off" class="a-input font-mono uppercase {{ $errors->has('sections._palette.' . $pk) ? 'has-error' : '' }}" data-pp-color-text="{{ $jsName }}">
                                </div>
                                @error('sections._palette.' . $pk)<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        <div class="overflow-hidden rounded-2xl border border-line" data-pp-brand-preview aria-hidden="true">
                            <div class="flex h-20 items-center justify-center text-3xl" data-pp-brand-bg style="background:linear-gradient(135deg,{{ $accent }},{{ $accent2 }})">
                                <i class="ph {{ $iconValue ?: 'ph-rocket-launch' }}" data-pp-brand-icon></i>
                            </div>
                            <div class="flex items-center justify-between gap-2 bg-white px-3 py-3">
                                <span class="truncate text-sm font-extrabold" data-pp-brand-name>{{ $page->name ?: 'نام محصول' }}</span>
                                <span class="rounded-full px-3 py-1.5 text-xs font-bold" data-pp-brand-btn style="background:{{ $accent }}">دکمه‌ی نمونه</span>
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="هویت بصری" subtitle="تصاویر از کتابخانه‌ی رسانه انتخاب می‌شوند." icon="image">
                    <div class="space-y-4">
                        @include('admin.dash.product-pages._image', ['name' => 'logo', 'id' => 'pp_logo', 'value' => old('logo', $page->logo), 'path' => 'logo', 'label' => 'لوگو', 'hint' => 'ترجیحاً PNG/SVG شفاف و مربعی.', 'size' => 'sm'])
                        @include('admin.dash.product-pages._image', ['name' => 'card_image', 'id' => 'pp_card_image', 'value' => old('card_image', $page->card_image), 'path' => 'card_image', 'label' => 'تصویر کارت', 'hint' => 'در فهرست /products نمایش داده می‌شود.', 'size' => 'sm'])
                        @include('admin.dash.product-pages._image', ['name' => 'og_image', 'id' => 'pp_og_image', 'value' => old('og_image', $page->og_image), 'path' => 'og_image', 'label' => 'تصویر اشتراک‌گذاری (OG)', 'hint' => 'پیشنهاد: ۱۲۰۰×۶۳۰.', 'size' => 'sm'])
                        @include('admin.dash.product-pages._icon-picker', ['name' => 'icon', 'id' => 'pp_icon', 'value' => $iconValue, 'path' => 'icon', 'label' => 'آیکن محصول', 'hint' => 'اگر لوگو ندارید، به‌جای آن نمایش داده می‌شود.', 'key' => null])
                    </div>
                </x-admin.card>

                <x-admin.card title="پیوندها و دکمه‌ها" subtitle="فقط پیوندهای پرشده در صفحه نمایش داده می‌شوند." icon="link">
                    <div class="space-y-3">
                        @foreach (Schema::linkTypes() as $lk => [$lLabel, $lIcon])
                            <x-admin.input :name="'links[' . $lk . ']'" :id="'pp_link_' . $lk" :label="$lLabel" ltr :icon="substr($lIcon, 3)" :value="($page->links ?? [])[$lk] ?? ''" placeholder="https://…" maxlength="500" />
                        @endforeach
                    </div>
                </x-admin.card>
            </aside>
        </div>

        {{-- نوار ذخیره‌ی چسبان --}}
        <div class="sticky bottom-3 z-30 mt-5">
            <div class="a-card flex flex-wrap items-center justify-between gap-3 !rounded-2xl px-4 py-3 shadow-adm-lift">
                <div class="flex min-w-0 items-center gap-2 text-xs font-bold text-slate-500"><span class="size-2 rounded-full bg-slate-300 transition" data-pp-dirty-dot></span><span data-pp-dirty-text>بدون تغییر</span></div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.button :href="route('panel.product-pages.index')" variant="ghost" size="sm">انصراف</x-admin.button>
                    <x-admin.button type="submit" size="sm" icon="check" loading>{{ $exists ? 'ذخیره‌ی تغییرات' : 'ایجاد صفحه' }}</x-admin.button>
                </div>
            </div>
        </div>
    </form>

    {{-- پاپ‌اور مشترک انتخاب آیکن --}}
    <div id="pp-icon-pop" class="fixed z-[70] hidden w-[19.5rem] max-w-[calc(100vw-1.5rem)] rounded-2xl border border-line bg-white p-3 shadow-adm-pop" role="dialog" aria-label="انتخاب آیکن">
        <button type="button" class="mb-2 flex w-full items-center gap-2 rounded-xl border border-dashed border-line px-3 py-2 text-xs font-bold text-slate-500 hover:border-aron-300 hover:text-aron-700" data-pp-icon-pick=""><i class="ph ph-prohibit text-base"></i>بدون آیکن</button>
        <div class="grid max-h-64 grid-cols-7 gap-1 overflow-y-auto pe-1">
            @foreach ($icons as $ic)
                <button type="button" class="grid aspect-square place-items-center rounded-xl text-xl text-slate-600 transition hover:bg-aron-50 hover:text-aron-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-aron-400 aria-pressed:bg-aron-600 aria-pressed:text-white" data-pp-icon-pick="{{ $ic }}" title="{{ substr($ic, 3) }}" aria-label="{{ substr($ic, 3) }}" aria-pressed="false"><i class="ph {{ $ic }}"></i></button>
            @endforeach
        </div>
    </div>

    @if ($exists)
        <form method="POST" action="{{ route('panel.product-pages.load-sample', $page) }}" id="pp-sample-form" class="hidden">@csrf</form>
    @endif

    <x-slot:scripts>
        <x-admin.media-config />
        <script>
            document.addEventListener('click', function (e) {
                var b = e.target.closest ? e.target.closest('[data-pp-confirm]') : null;
                if (b && !window.confirm(b.getAttribute('data-pp-confirm'))) { e.preventDefault(); }
            });
        </script>
    </x-slot:scripts>
</x-admin.layout>
