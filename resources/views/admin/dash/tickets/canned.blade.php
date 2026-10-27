@php
    $openBag = collect(['create'] + $responses->mapWithKeys(fn ($r) => [$r->id => 'edit-'.$r->id])->all())->first(fn ($b) => $errors->hasBag($b) && $errors->getBag($b)->any());
@endphp
<x-admin.layout title="پاسخ‌های آماده" subtitle="متن‌های از پیش نوشته‌شده برای پاسخ سریع‌تر به تیکت‌ها" :breadcrumbs="[['label' => 'تیکت‌ها', 'url' => route('panel.tickets.index')], ['label' => 'پاسخ‌های آماده']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.tickets.departments')" variant="secondary" icon="folders">دپارتمان‌ها</x-admin.button>
        <x-admin.button type="button" icon="plus" data-modal-open="canned-create">پاسخ جدید</x-admin.button>
    </x-slot:actions>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($responses as $r)
            <x-admin.card :title="$r->title" :subtitle="$r->department?->name ?? 'همهٔ دپارتمان‌ها'">
                <p class="line-clamp-4 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $r->body }}</p>
                <x-slot:footer>
                    <div class="flex items-center justify-between gap-2">
                        <x-admin.badge :color="$r->is_active ? 'mint' : 'slate'" dot>{{ $r->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge>
                        <div class="flex gap-1.5">
                            <x-admin.button type="button" size="sm" variant="soft" icon="pencil-simple" data-modal-open="canned-edit-{{ $r->id }}">ویرایش</x-admin.button>
                            <x-admin.confirm-delete :action="route('panel.tickets.canned.destroy', $r)" icon-only message="این پاسخ آماده حذف شود؟" />
                        </div>
                    </div>
                </x-slot:footer>
            </x-admin.card>
        @empty
            <div class="md:col-span-2 xl:col-span-3"><x-admin.card><x-admin.empty-state icon="chat-text" title="پاسخ آماده‌ای ندارید" description="یک پاسخ بسازید تا هنگام پاسخ‌دادن با یک کلیک درج شود." /></x-admin.card></div>
        @endforelse
    </div>

    @foreach(array_merge([null], $responses->all()) as $r)
        @php
            $bag = $r ? 'edit-'.$r->id : 'create';
            $err = $errors->getBag($bag);
            $old = fn ($k, $def = '') => $openBag === $bag ? old($k, $def) : $def;
        @endphp
        <x-admin.modal :name="$r ? 'canned-edit-'.$r->id : 'canned-create'" :title="$r ? 'ویرایش پاسخ آماده' : 'پاسخ آمادهٔ جدید'" max-width="xl" :show="$openBag === $bag">
            <form method="POST" action="{{ $r ? route('panel.tickets.canned.update', $r) : route('panel.tickets.canned.store') }}" id="form-c-{{ $bag }}" class="space-y-4">
                @csrf @if($r) @method('PUT') @endif
                <div><label class="a-label">عنوان <span class="text-coral-500">*</span></label><input name="title" required maxlength="120" class="a-input {{ $err->has('title') ? 'has-error' : '' }}" value="{{ $old('title', $r->title ?? '') }}">@if($err->has('title'))<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $err->first('title') }}</p>@endif</div>
                <div><label class="a-label">متن <span class="text-coral-500">*</span></label><textarea name="body" rows="7" required maxlength="5000" class="a-textarea {{ $err->has('body') ? 'has-error' : '' }}">{{ $old('body', $r->body ?? '') }}</textarea>@if($err->has('body'))<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $err->first('body') }}</p>@endif</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="a-label">دپارتمان</label>
                        <select name="department_id" class="a-select"><option value="">همه</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected((string) $old('department_id', $r->department_id ?? '') === (string) $d->id)>{{ $d->name }}</option>@endforeach</select></div>
                    <div class="flex items-end"><label class="flex cursor-pointer items-center gap-3"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="a-switch" @checked((bool) $old('is_active', $r ? $r->is_active : true))><span class="text-sm font-bold text-slate-700">فعال</span></label></div>
                </div>
            </form>
            <x-slot:footer>
                <x-admin.button type="button" variant="ghost" data-modal-close>انصراف</x-admin.button>
                <button type="submit" form="form-c-{{ $bag }}" class="a-btn a-btn-primary">ذخیره</button>
            </x-slot:footer>
        </x-admin.modal>
    @endforeach
</x-admin.layout>
