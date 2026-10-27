@php
    $isEdit = $proforma->exists;
    $oldItems = old('items');
    $rows = $oldItems ?? ($isEdit ? $proforma->items : [['title' => '', 'description' => '', 'price' => '']]);
    $rows = array_values($rows ?: [['title' => '', 'description' => '', 'price' => '']]);
    $rowHtml = function ($i, $r) {
        return view('admin.dash.proformas._row', ['i' => $i, 'r' => $r])->render();
    };
@endphp
<form method="POST" action="{{ $isEdit ? route('panel.proformas.update', $proforma) : route('panel.proformas.store') }}" class="grid gap-6 lg:grid-cols-3" data-proforma-builder>
    @csrf @if ($isEdit) @method('PUT') @endif
    <div class="min-w-0 space-y-6 lg:col-span-2">
        <x-admin.card title="اطلاعات پروژه و مشتری" icon="user">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="project_title" label="عنوان پروژه" required class="" :value="$proforma->project_title" placeholder="مثلاً: طراحی فروشگاه اینترنتی" />
                <x-admin.input name="client_name" label="نام مشتری / شرکت" required :value="$proforma->client_name" />
                <x-admin.input name="client_phone" type="tel" label="شماره تماس" :value="$proforma->client_phone" placeholder="0912…" />
                <x-admin.input name="client_email" type="email" label="ایمیل" :value="$proforma->client_email" hint="برای ارسال خودکار پیش‌فاکتور. ایمیل یا شماره تماس یکی لازم است." />
            </div>
        </x-admin.card>

        <x-admin.card title="اقلام پیش‌فاکتور" icon="list-checks" :padding="false">
            <x-slot:actions>
                <select data-catalog class="a-select !h-9 w-56 text-xs" aria-label="افزودن از کاتالوگ">
                    <option value="">+ افزودن از کاتالوگ خدمات…</option>
                    @foreach ($catalog as $g)<optgroup label="{{ $g['label'] }}">@foreach ($g['items'] as $it)<option value="1" data-title="{{ $it['title'] }}" data-price="{{ $it['price'] }}">{{ $it['title'] }} — {{ number_format($it['price']) }}</option>@endforeach</optgroup>@endforeach
                </select>
                <x-admin.button type="button" variant="soft" size="sm" icon="plus" data-add-row>افزودن ردیف</x-admin.button>
            </x-slot:actions>
            <div class="overflow-x-auto px-3 pb-3 pt-1 sm:px-5">
                <table class="a-table min-w-[560px]">
                    <thead><tr><th>شرح خدمت</th><th class="w-44">قیمت (تومان)</th><th class="w-28"></th></tr></thead>
                    <tbody data-rows>
                        @foreach ($rows as $i => $r){!! $rowHtml($i, $r) !!}@endforeach
                    </tbody>
                </table>
                <template data-row-tpl>{!! $rowHtml('__I__', ['title' => '', 'description' => '', 'price' => '']) !!}</template>
            </div>
            @error('items')<p class="px-5 pb-4 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
        </x-admin.card>
    </div>

    <div class="min-w-0 space-y-6">
        <div class="a-card overflow-hidden bg-gradient-to-br from-aron-600 to-aron-800 p-5 text-white lg:sticky lg:top-24">
            <div class="text-xs font-bold text-white/70">جمع کل پیش‌فاکتور</div>
            <div class="mt-2 text-3xl font-black tracking-tight"><span data-total>۰</span> <span class="text-sm font-bold text-white/70">تومان</span></div>
            <div class="mt-1 text-xs text-white/60"><span data-row-count>1</span> ردیف</div>
            <div class="mt-5 space-y-3 rounded-2xl bg-white p-4 text-ink">
                <x-admin.select name="status" label="وضعیت" :options="$statuses" :selected="$proforma->status ?: 'sent'" hint="«پیش‌نویس» ایمیل نمی‌فرستد." />
                <x-admin.button type="submit" class="w-full" icon="paper-plane-tilt" loading>{{ $isEdit ? 'ذخیره تغییرات' : 'صدور پیش‌فاکتور' }}</x-admin.button>
                <x-admin.button :href="route('panel.proformas.index')" variant="secondary" class="w-full">انصراف</x-admin.button>
            </div>
        </div>
        @if ($isEdit)
            <x-admin.card title="اشتراک‌گذاری" icon="share-network">
                <div class="space-y-2">
                    <x-admin.button type="button" variant="secondary" size="sm" icon="link" class="w-full" :data-copy="$proforma->publicUrl()">کپی لینک عمومی</x-admin.button>
                    <x-admin.button :href="$proforma->publicUrl()" variant="secondary" size="sm" icon="eye" class="w-full" target="_blank" rel="noopener">مشاهده نسخه مشتری</x-admin.button>
                    @if ($proforma->client_email)<x-admin.button type="submit" form="proforma-send" variant="soft" size="sm" icon="envelope-simple" class="w-full">ارسال دوباره ایمیل</x-admin.button>@endif
                </div>
            </x-admin.card>
        @endif
    </div>
</form>
@if ($isEdit && $proforma->client_email)<form id="proforma-send" method="POST" action="{{ route('panel.proformas.send', $proforma) }}" data-confirm="ایمیل پیش‌فاکتور برای {{ $proforma->client_email }} ارسال شود؟">@csrf</form>@endif
