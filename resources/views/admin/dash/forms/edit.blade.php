@php
    $tabs = [
        'fields' => ['label' => 'فیلدها', 'icon' => 'stack'],
        'general' => ['label' => 'تنظیمات فرم', 'icon' => 'gear-six'],
        'confirmation' => ['label' => 'پیام پس از ارسال', 'icon' => 'check-circle'],
        'notifications' => ['label' => 'اعلان‌ها و ایمیل', 'icon' => 'bell'],
        'limits' => ['label' => 'محدودیت‌ها', 'icon' => 'lock'],
    ];
    $mergeTags = ['{field:کلید}' => 'مقدار یک فیلد (کلید را از تنظیمات فیلد بردارید)', '{all_fields}' => 'همه‌ی فیلدهای پرشده', '{form_title}' => 'عنوان فرم', '{entry_id}' => 'شماره‌ی ورودی', '{date}' => 'تاریخ ثبت'];
@endphp
<x-admin.layout :title="'فرم‌ساز: '.$form->title" subtitle="فیلدها را از ستون کناری اضافه کنید؛ روی هر فیلد کلیک کنید تا تنظیماتش باز شود."
                :breadcrumbs="[['label' => 'فرم‌ساز', 'url' => route('panel.forms.index')], ['label' => $form->title]]">
    <x-slot:head>
        <link rel="stylesheet" href="{{ asset('css/admin/form-builder.css') }}?v={{ @filemtime(public_path('css/admin/form-builder.css')) ?: 1 }}">
        <script src="{{ asset('vendor/sortable/Sortable.min.js') }}" defer></script>
        <script src="{{ asset('js/admin/form-builder.js') }}?v={{ @filemtime(public_path('js/admin/form-builder.js')) ?: 1 }}" defer></script>
    </x-slot:head>
    <x-slot:actions>
        <x-admin.button variant="secondary" icon="copy-simple" data-copy="{{ $form->shortcode() }}" title="کپی شورت‌کد"><span dir="ltr" class="text-xs">{{ $form->shortcode() }}</span></x-admin.button>
        <x-admin.button :href="route('panel.forms.preview', $form)" variant="secondary" icon="eye" target="_blank">پیش‌نمایش</x-admin.button>
        @can('forms.entries')<x-admin.button :href="route('panel.form-entries.index', ['form' => $form->id])" variant="secondary" icon="tray">ورودی‌ها</x-admin.button>@endcan
    </x-slot:actions>

    <form method="POST" action="{{ route('panel.forms.update', $form) }}" data-fb-form novalidate
          data-fields="{{ json_encode($form->fields ?? [], JSON_UNESCAPED_UNICODE) }}" data-types="{{ json_encode($types, JSON_UNESCAPED_UNICODE) }}" data-operators="{{ json_encode($operators, JSON_UNESCAPED_UNICODE) }}">
        @csrf @method('PUT')
        <input type="hidden" name="fields_json" value="" data-fb-json>
        <input type="hidden" name="_tab" value="fields" data-fb-tab>

        <x-admin.tabs :tabs="$tabs" default="fields">
            {{-- ============================== فیلدها --}}
            <x-admin.tab name="fields">
                <div class="fb-layout">
                    <aside class="a-card fb-palette p-4" aria-label="افزودن فیلد">
                        @foreach ($groups as $g => $gLabel)
                            <p class="mb-2 text-xs font-bold text-slate-400 {{ $loop->first ? '' : 'mt-4' }}">{{ $gLabel }}</p>
                            <div class="fb-palette-grid">
                                @foreach ($types as $type => [$label, $icon, $group])
                                    @continue($group !== $g)
                                    <button type="button" data-fb-add="{{ $type }}" class="flex items-center gap-2 rounded-xl border border-line bg-white px-2.5 py-2 text-start text-[13px] font-semibold text-slate-700 transition hover:border-aron-300 hover:bg-aron-50/60">
                                        <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-base text-slate-600"><i class="ph ph-{{ $icon }}"></i></span>{{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </aside>

                    <section class="a-card min-w-0 p-4 sm:p-5" aria-label="فیلدهای فرم">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-bold text-slate-700" data-fb-count></p>
                            <p class="text-xs text-slate-400">برای جابه‌جایی، دستگیره را بکشید.</p>
                        </div>
                        <div class="fb-empty" data-fb-empty>از ستون کناری یک فیلد اضافه کنید.</div>
                        <div class="fb-canvas" data-fb-canvas></div>
                    </section>

                    <aside class="a-card fb-settings p-5" data-fb-settings aria-label="تنظیمات فیلد"></aside>
                </div>
            </x-admin.tab>

            {{-- ============================== تنظیمات فرم --}}
            <x-admin.tab name="general">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-admin.card title="مشخصات فرم" icon="note-pencil">
                        <div class="space-y-4">
                            <x-admin.input name="title" label="عنوان فرم" :value="$form->title" required />
                            <x-admin.input name="slug" label="نامک (برای شورت‌کد با slug)" :value="$form->slug" ltr hint='مثلاً [form slug="contact"]' />
                            <x-admin.textarea name="description" label="توضیح داخلی (فقط در پنل)" :value="$form->description" rows="2" />
                            <x-admin.toggle name="is_active" label="فرم فعال است" hint="فرم غیرفعال به‌جای فیلدها پیام «بسته بودن فرم» را نشان می‌دهد." :checked="$form->is_active" />
                        </div>
                    </x-admin.card>
                    <x-admin.card title="ظاهر و دکمه‌ها" icon="paint-brush">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-admin.input name="settings[submit_label]" label="متن دکمه‌ی ارسال" :value="$settings['submit_label']" />
                            <x-admin.select name="settings[label_placement]" label="محل برچسب‌ها" :options="['top' => 'بالای فیلد', 'hidden' => 'پنهان (فقط متن داخل فیلد)']" :selected="$settings['label_placement']" />
                            <x-admin.select name="settings[progress]" label="نمایش پیشرفت (فرم چندمرحله‌ای)" :options="['steps' => 'مراحل شماره‌دار', 'bar' => 'نوار پیشرفت', 'none' => 'بدون نمایش']" :selected="$settings['progress']" />
                            <x-admin.input name="settings[css_class]" label="کلاس CSS فرم" :value="$settings['css_class']" ltr />
                            <x-admin.input name="settings[next_label]" label="متن دکمه‌ی مرحله بعد" :value="$settings['next_label']" />
                            <x-admin.input name="settings[prev_label]" label="متن دکمه‌ی مرحله قبل" :value="$settings['prev_label']" />
                        </div>
                    </x-admin.card>
                </div>
            </x-admin.tab>

            {{-- ============================== پیام پس از ارسال --}}
            <x-admin.tab name="confirmation">
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <x-admin.card title="بعد از ارسال موفق" icon="check-circle">
                        <div class="space-y-4">
                            <x-admin.select name="settings[confirmation_type]" label="چه اتفاقی بیفتد؟" :options="['message' => 'نمایش پیام', 'redirect' => 'انتقال به آدرس دیگر']" :selected="$settings['confirmation_type']" />
                            <x-admin.editor name="settings[confirmation_message]" label="متن پیام" :value="$settings['confirmation_message']" :height="260" />
                            <x-admin.input name="settings[redirect_url]" label="آدرس مقصد (برای حالت انتقال)" :value="$settings['redirect_url']" ltr placeholder="https://… یا /thanks" />
                            <x-admin.toggle name="settings[hide_after_submit]" label="بعد از ارسال، فرم پنهان شود" :checked="$settings['hide_after_submit']" />
                        </div>
                    </x-admin.card>
                    @include('admin.dash.forms._merge-tags')
                </div>
            </x-admin.tab>

            {{-- ============================== اعلان‌ها --}}
            <x-admin.tab name="notifications">
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <div class="space-y-5">
                        <x-admin.card title="اطلاع به مدیران" icon="bell">
                            <div class="space-y-4">
                                <x-admin.toggle name="settings[notify_admin]" label="برای هر ورودی جدید اعلان ارسال شود" hint="از طریق «تنظیمات اعلان‌ها»ی پنل (اعلان پنل، پیامک و ایمیل)." :checked="$settings['notify_admin']" />
                                <x-admin.input name="settings[notify_to]" label="ایمیل‌های اضافه برای همین فرم (اختیاری)" :value="$settings['notify_to']" ltr placeholder="a@aron-soft.com, b@aron-soft.com" />
                                <x-admin.input name="settings[notify_subject]" label="موضوع اعلان" :value="$settings['notify_subject']" />
                            </div>
                        </x-admin.card>
                        <x-admin.card title="ایمیل تأیید به کاربر" icon="envelope-simple">
                            <div class="space-y-4">
                                <x-admin.toggle name="settings[confirm_user]" label="برای کاربر ایمیل تأیید ارسال شود" hint="فرم باید یک فیلد «ایمیل» داشته باشد." :checked="$settings['confirm_user']" />
                                <x-admin.field label="فیلد ایمیل کاربر" name="settings.confirm_to">
                                    <select name="settings[confirm_to]" class="a-select" data-fb-email-select data-selected="{{ $settings['confirm_to'] }}"><option value="">—</option></select>
                                </x-admin.field>
                                <x-admin.input name="settings[confirm_subject]" label="موضوع" :value="$settings['confirm_subject']" />
                                <x-admin.textarea name="settings[confirm_body]" label="متن" :value="$settings['confirm_body']" rows="6" />
                            </div>
                        </x-admin.card>
                    </div>
                    @include('admin.dash.forms._merge-tags')
                </div>
            </x-admin.tab>

            {{-- ============================== محدودیت‌ها --}}
            <x-admin.tab name="limits">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-admin.card title="امنیت و دسترسی" icon="shield-check">
                        <div class="space-y-4">
                            <x-admin.toggle name="settings[captcha]" label="کپچای نامرئی (جلوگیری از ربات‌ها)" hint="بدون نیاز به تایپ کد؛ پیشنهاد می‌شود روشن بماند." :checked="$settings['captcha']" />
                            <x-admin.toggle name="settings[require_login]" label="فقط کاربران واردشده بتوانند فرم را پر کنند" :checked="$settings['require_login']" />
                            <x-admin.toggle name="settings[store_ip]" label="ذخیره‌ی IP ارسال‌کننده" :checked="$settings['store_ip']" />
                        </div>
                    </x-admin.card>
                    <x-admin.card title="ظرفیت و زمان‌بندی" icon="calendar-check">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-admin.input name="settings[limit_entries]" type="number" min="0" label="حداکثر تعداد ورودی (۰ = نامحدود)" :value="$settings['limit_entries']" ltr />
                            <x-admin.input name="settings[limit_message]" label="پیام تکمیل ظرفیت" :value="$settings['limit_message']" />
                            <x-admin.input name="settings[open_at]" type="datetime-local" label="شروع پذیرش" :value="$settings['open_at'] ? str_replace(' ', 'T', $settings['open_at']) : ''" ltr />
                            <x-admin.input name="settings[close_at]" type="datetime-local" label="پایان پذیرش" :value="$settings['close_at'] ? str_replace(' ', 'T', $settings['close_at']) : ''" ltr />
                            <div class="sm:col-span-2"><x-admin.input name="settings[closed_message]" label="پیام وقتی فرم بسته است" :value="$settings['closed_message']" /></div>
                        </div>
                    </x-admin.card>
                </div>
            </x-admin.tab>
        </x-admin.tabs>

        <div class="sticky bottom-3 z-20 mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white/95 p-3 shadow-adm-pop backdrop-blur">
            <span class="flex items-center gap-2 text-xs font-bold text-slate-500"><span class="size-2 rounded-full bg-slate-300" data-fb-dirty-dot></span><span data-fb-dirty-text>بدون تغییر</span></span>
            <x-admin.button type="submit" icon="floppy-disk">ذخیره‌ی فرم</x-admin.button>
        </div>
    </form>

    <x-slot:scripts>
        <script>
            document.addEventListener('click', (e) => {
                const b = e.target.closest('[data-copy]'); if (!b) return;
                const t = b.dataset.copy;
                (navigator.clipboard && window.isSecureContext ? navigator.clipboard.writeText(t) : Promise.reject()).then(
                    () => window.adminToast && adminToast('شورت‌کد کپی شد.'), () => window.prompt('شورت‌کد را کپی کنید:', t));
            });
        </script>
    </x-slot:scripts>
</x-admin.layout>
