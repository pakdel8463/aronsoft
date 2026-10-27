@php
    $isEdit = $landing->exists;
    $action = $isEdit ? route('panel.landing.update', $landing) : route('panel.landing.store');
@endphp
<form id="aron-landing-editor" method="POST" action="{{ $action }}" class="grid items-start gap-5 lg:grid-cols-3">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="grid gap-5 lg:col-span-2">
        <x-admin.card title="اطلاعات صفحه" icon="browser">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="title" label="عنوان" required :value="$landing->title" class="" />
                <x-admin.input name="slug" label="نامک (Slug)" required :value="$landing->slug" ltr hint="فقط حروف لاتین، عدد و خط تیره" />
                <x-admin.select name="template" label="قالب" required :options="$templates + ['cms'=>'لندینگ محتوایی']" :selected="$landing->template" placeholder="انتخاب قالب…" class="sm:col-span-2" />
            </div>
        </x-admin.card>
        @include('admin.dash.landing._editor')
        <x-admin.card title="سئو" icon="magnifying-glass">
            <div class="grid gap-4">
                <x-admin.input name="seo_title" label="عنوان سئو" :value="$landing->seo_title" />
                <x-admin.textarea name="seo_description" label="توضیحات سئو" :value="$landing->seo_description" rows="3" hint="حداکثر ۲۵۵ نویسه" />
            </div>
        </x-admin.card>
    </div>
    <div class="grid gap-5">
        <x-admin.card title="انتشار" icon="paper-plane-tilt">
            <div class="grid gap-4">
                <x-admin.toggle name="is_active" label="فعال" :checked="old('is_active', $landing->is_active ?? true)" />
                <div class="flex flex-wrap gap-2">
                    <x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'بروزرسانی' : 'ذخیره' }}</x-admin.button>
                    <x-admin.button :href="route('panel.landing.index')" variant="secondary">انصراف</x-admin.button>
                </div>
            </div>
        </x-admin.card>
        @if($isEdit && $landing->template === 'cms')
        <div class="flex flex-wrap gap-2"><a class="rounded-xl bg-indigo-50 px-4 py-3 text-indigo-700" target="_blank" href="{{ route('panel.landing.preview',$landing) }}">پیش‌نمایش پیش‌نویس</a><button type="submit" form="publish-landing" class="rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white">انتشار نسخه ذخیره‌شده</button></div>
        @endif
        <div class="rounded-2xl bg-amber-50 p-4 text-xs leading-6 text-amber-800">
            <b>توجه:</b> برای قالب‌های خدماتی قدیمی محتوا همچنان از Blade خوانده می‌شود. برای قالب «لندینگ محتوایی»، سکشن‌ها را از ویرایشگر مدیریت کنید.
        </div>
    </div>
</form>

@if($isEdit)<form id="publish-landing" method="POST" action="{{ route('panel.landing.publish',$landing) }}">@csrf</form>@endif
