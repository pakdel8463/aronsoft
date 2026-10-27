@php
    $fa = fn ($v) => \App\Helpers\Fa::toFa($v);
    $openBag = collect(['create'] + $departments->mapWithKeys(fn ($d) => [$d->id => 'edit-'.$d->id])->all())->first(fn ($b) => $errors->hasBag($b) && $errors->getBag($b)->any());
@endphp
<x-admin.layout title="دپارتمان‌های تیکت" subtitle="دسته‌بندی درخواست‌ها و ایمیل اعلان هر دپارتمان" :breadcrumbs="[['label' => 'تیکت‌ها', 'url' => route('panel.tickets.index')], ['label' => 'دپارتمان‌ها']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.tickets.canned')" variant="secondary" icon="chat-text">پاسخ‌های آماده</x-admin.button>
        <x-admin.button type="button" icon="plus" data-modal-open="dept-create">دپارتمان جدید</x-admin.button>
    </x-slot:actions>

    <x-admin.card :padding="false">
        <x-admin.table flush>
            <x-slot:head><x-admin.th>نام</x-admin.th><x-admin.th>ایمیل اعلان</x-admin.th><x-admin.th>تیکت‌ها</x-admin.th><x-admin.th>ترتیب</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
            @forelse($departments as $d)
                <tr>
                    <x-admin.td><span class="font-extrabold text-ink">{{ $d->name }}</span>@if($d->description)<span class="block text-xs text-slate-400">{{ $d->description }}</span>@endif</x-admin.td>
                    <x-admin.td><span dir="ltr" class="text-slate-500">{{ $d->email ?: '— (ایمیل پیش‌فرض)' }}</span></x-admin.td>
                    <x-admin.td>{{ $fa($d->tickets_count) }}</x-admin.td>
                    <x-admin.td>{{ $fa($d->sort_order) }}</x-admin.td>
                    <x-admin.td><x-admin.badge :color="$d->is_active ? 'mint' : 'slate'" dot>{{ $d->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge></x-admin.td>
                    <x-admin.td class="text-end" nowrap>
                        <div class="inline-flex gap-1.5">
                            <x-admin.button type="button" size="sm" variant="soft" icon="pencil-simple" data-modal-open="dept-edit-{{ $d->id }}">ویرایش</x-admin.button>
                            <x-admin.confirm-delete :action="route('panel.tickets.departments.destroy', $d)" icon-only message="این دپارتمان حذف شود؟" />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="folders" title="دپارتمانی تعریف نشده" /></td></tr>
            @endforelse
        </x-admin.table>
    </x-admin.card>

    @foreach(array_merge([null], $departments->all()) as $d)
        @php
            $bag = $d ? 'edit-'.$d->id : 'create';
            $err = $errors->getBag($bag);
            $old = fn ($k, $def = '') => $openBag === $bag ? old($k, $def) : $def;
        @endphp
        <x-admin.modal :name="$d ? 'dept-edit-'.$d->id : 'dept-create'" :title="$d ? 'ویرایش دپارتمان' : 'دپارتمان جدید'" :show="$openBag === $bag">
            <form method="POST" action="{{ $d ? route('panel.tickets.departments.update', $d) : route('panel.tickets.departments.store') }}" id="form-{{ $bag }}" class="space-y-4">
                @csrf @if($d) @method('PUT') @endif
                <div><label class="a-label">نام <span class="text-coral-500">*</span></label><input name="name" required class="a-input {{ $err->has('name') ? 'has-error' : '' }}" value="{{ $old('name', $d->name ?? '') }}">@if($err->has('name'))<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $err->first('name') }}</p>@endif</div>
                <div><label class="a-label">ایمیل اعلان</label><input name="email" type="email" dir="ltr" class="a-input {{ $err->has('email') ? 'has-error' : '' }}" value="{{ $old('email', $d->email ?? '') }}" placeholder="support@example.com">@if($err->has('email'))<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $err->first('email') }}</p>@else<p class="mt-1.5 text-xs text-slate-400">خالی = ایمیل پیش‌فرض مدیر سایت</p>@endif</div>
                <div><label class="a-label">توضیح</label><input name="description" class="a-input" maxlength="255" value="{{ $old('description', $d->description ?? '') }}"></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="a-label">ترتیب نمایش</label><input name="sort_order" type="number" min="0" dir="ltr" class="a-input" value="{{ $old('sort_order', $d->sort_order ?? 0) }}"></div>
                    <div class="flex items-end"><label class="flex cursor-pointer items-center gap-3"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="a-switch" @checked((bool) $old('is_active', $d ? $d->is_active : true))><span class="text-sm font-bold text-slate-700">فعال</span></label></div>
                </div>
            </form>
            <x-slot:footer>
                <x-admin.button type="button" variant="ghost" data-modal-close>انصراف</x-admin.button>
                <button type="submit" form="form-{{ $bag }}" class="a-btn a-btn-primary">ذخیره</button>
            </x-slot:footer>
        </x-admin.modal>
    @endforeach
</x-admin.layout>
