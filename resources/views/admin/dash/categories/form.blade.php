@php
    $exists = $category->exists;
    $action = $exists ? route('panel.categories.update', $category) : route('panel.categories.store');
    $img = old('image', $category->image);
    $type = old('type', $category->type ?: 'blog');
@endphp
<x-admin.layout :title="$exists ? 'ویرایش دسته‌بندی' : 'دسته‌ی جدید'"
                :breadcrumbs="[['label' => 'دسته‌بندی‌ها', 'url' => route('panel.categories.index', ['type' => $type])], ['label' => $exists ? 'ویرایش' : 'جدید']]" width="max-w-5xl">
    <form method="POST" action="{{ $action }}" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
        @csrf
        @if ($exists) @method('PUT') @endif

        <div class="space-y-5">
            <x-admin.card title="اطلاعات دسته" icon="folder">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="name" label="نام" :value="$category->name" required class="sm:col-span-2" />
                    <x-admin.input name="slug" label="پیوند یکتا (slug)" hint="خالی = ساخت خودکار از نام (فارسی هم پشتیبانی می‌شود)" :value="$category->slug" dir="auto" />
                    <x-admin.select name="type" label="نوع" :selected="$type" :options="['blog' => 'وبلاگ', 'portfolio' => 'نمونه‌کار']" hint="زیرمجموعه همیشه هم‌نوع والد است." />
                    <x-admin.select name="parent_id" label="دسته‌ی والد" class="sm:col-span-2" :selected="$category->parent_id" placeholder="— بدون والد (دسته‌ی اصلی) —">
                        @foreach ($parents as $row)
                            <option value="{{ $row['model']->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $row['model']->id)>{{ str_repeat('— ', $row['depth']) }}{{ $row['model']->name }}</option>
                        @endforeach
                    </x-admin.select>
                    <x-admin.textarea name="description" label="توضیحات" :value="$category->description" rows="4" class="sm:col-span-2" />
                </div>
            </x-admin.card>

            <x-admin.card title="سئو" subtitle="در صفحه‌ی دسته‌ی سایت استفاده می‌شود" icon="magnifying-glass">
                <div class="grid gap-4">
                    <x-admin.input name="meta_title" label="عنوان سئو" :value="$category->meta_title" maxlength="255" />
                    <x-admin.textarea name="meta_description" label="توضیحات متا" :value="$category->meta_description" rows="3" maxlength="500" />
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-5">
            <x-admin.card title="وضعیت" icon="toggle-left">
                <x-admin.toggle name="is_active" label="فعال" hint="دسته‌ی غیرفعال در سایت نمایش داده نمی‌شود." :checked="(bool) old('is_active', $exists ? $category->is_active : true)" unchecked="0" />
            </x-admin.card>
            <x-admin.card title="تصویر دسته" icon="image-square">
                <input type="hidden" name="image" id="cat_image" value="{{ $img }}">
                <img id="cat_preview" src="{{ $img ? asset(ltrim($img, '/')) : '' }}" alt="" class="{{ $img ? '' : 'hidden' }} mb-3 aspect-video w-full rounded-2xl border border-line object-cover">
                <div class="flex gap-2">
                    <x-admin.button type="button" variant="secondary" size="sm" icon="image" data-media-pick data-type="image" data-target="#cat_image" data-preview="#cat_preview">انتخاب از کتابخانه</x-admin.button>
                    <x-admin.button type="button" variant="ghost" size="sm" icon="x" data-media-clear data-target="#cat_image" data-preview="#cat_preview">حذف</x-admin.button>
                </div>
            </x-admin.card>
            @if ($exists)
                <p class="px-1 text-xs leading-6 text-slate-400">ایجاد: {{ jdate($category->created_at)->format('Y/m/d') }} · پیوند عمومی: <span dir="ltr">/blog/category/{{ $category->slug }}</span></p>
            @endif
            <div class="flex gap-2">
                <x-admin.button type="submit" icon="floppy-disk" loading class="flex-1">ذخیره</x-admin.button>
                <x-admin.button :href="route('panel.categories.index', ['type' => $type])" variant="secondary">انصراف</x-admin.button>
            </div>
        </div>
    </form>
    <x-slot:scripts><x-admin.media-config /></x-slot:scripts>
</x-admin.layout>
