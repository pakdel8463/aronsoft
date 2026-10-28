@php
    $seo = fn ($k) => (string) ($page->settings[$k] ?? '');
@endphp
<x-admin.layout :title="($page->is_template ? 'تنظیمات قالب: ' : 'تنظیمات صفحه: ').$page->title" width="max-w-5xl"
                :breadcrumbs="[['label' => 'صفحه‌ساز', 'url' => route('panel.pages.index', $page->is_template ? ['tab' => 'templates'] : [])], ['label' => $page->title]]">
    <x-slot:actions>
        <x-admin.button :href="$page->previewUrl()" variant="secondary" icon="eye" target="_blank">پیش‌نمایش</x-admin.button>
        <x-admin.button :href="route('panel.pages.builder', $page)" icon="layout">صفحه‌ساز</x-admin.button>
    </x-slot:actions>

    @include('admin.dash.pages._tabs')
    <x-admin.media-config />

    <form method="POST" action="{{ route('panel.pages.update', $page) }}" class="space-y-5">
        @csrf @method('PUT')
        <x-admin.card title="اطلاعات صفحه" icon="note-pencil">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="title" label="عنوان" required :value="$page->title" />
                @if ($page->is_template)
                    <x-admin.textarea name="description" label="توضیح قالب" hint="در پنجره‌ی «صفحه‌ی جدید» نمایش داده می‌شود." :value="$page->description" rows="2" />
                @else
                    <x-admin.field label="آدرس صفحه" name="slug" required hint="حروف انگلیسی کوچک، عدد و خط تیره (فارسی هم مجاز است).">
                        <div class="flex items-stretch overflow-hidden rounded-xl border border-line focus-within:border-aron-400 focus-within:ring-4 focus-within:ring-aron-100" dir="ltr">
                            <span class="flex items-center bg-slate-50 px-3 text-xs font-bold text-slate-400">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'aron-soft.com' }}/</span>
                            <input name="slug" id="slug" value="{{ old('slug', $page->slug) }}" required class="min-w-0 flex-1 border-0 px-3 py-2.5 text-sm focus:ring-0" dir="ltr">
                        </div>
                    </x-admin.field>
                    <div class="sm:col-span-2">
                        <x-admin.toggle name="is_active" label="منتشر شود" hint="تا وقتی خاموش است فقط مدیران (با پیش‌نمایش) صفحه را می‌بینند." :checked="old('is_active', $page->is_active)" />
                    </div>
                @endif
            </div>
        </x-admin.card>

        @unless ($page->is_template)
            <x-admin.card title="سئو و اشتراک‌گذاری" icon="magnifying-glass">
                <div class="grid gap-4">
                    <x-admin.input name="meta_title" label="عنوان سئو" hint="خالی = عنوان صفحه. بهتر است کمتر از ۶۰ کاراکتر باشد." :value="$seo('meta_title')" maxlength="190" />
                    <x-admin.textarea name="meta_description" label="توضیح سئو (meta description)" hint="۱۲۰ تا ۱۶۰ کاراکتر؛ در نتایج گوگل زیر عنوان نمایش داده می‌شود." :value="$seo('meta_description')" rows="2" maxlength="400" />
                    @include('admin.dash.product-pages._image', ['name' => 'og_image', 'id' => 'og_image', 'value' => old('og_image', $seo('og_image')), 'path' => 'og_image', 'label' => 'تصویر اشتراک‌گذاری (Open Graph)', 'hint' => 'هنگام اشتراک لینک در تلگرام، واتساپ و شبکه‌های اجتماعی نمایش داده می‌شود.'])
                    <x-admin.toggle name="noindex" label="این صفحه در گوگل نمایش داده نشود (noindex)" :checked="old('noindex', (bool) ($page->settings['noindex'] ?? false))" />
                </div>
            </x-admin.card>
        @endunless

        <div class="flex justify-end">
            <x-admin.button type="submit" icon="floppy-disk" loading>ذخیره‌ی تنظیمات</x-admin.button>
        </div>
    </form>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-card)] border border-coral-100 bg-coral-50/40 p-4">
        <p class="text-sm text-slate-600">{{ $page->is_template ? 'حذف قالب روی صفحه‌هایی که از آن ساخته شده‌اند اثری ندارد.' : 'با حذف صفحه، آدرس آن خطای ۴۰۴ می‌دهد.' }}</p>
        <x-admin.confirm-delete :action="route('panel.pages.destroy', $page)" :message="'«'.$page->title.'» و همه‌ی بلوک‌هایش حذف شود؟'" :label="$page->is_template ? 'حذف قالب' : 'حذف صفحه'" size="md" />
    </div>
</x-admin.layout>
