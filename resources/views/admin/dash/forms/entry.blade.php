@php
    use App\Models\FormEntry;
    use App\Forms\FormSchema;
    $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v);
    $form = $entry->form;
@endphp
<x-admin.layout :title="$entry->title()" :subtitle="'فرم «'.$form->title.'» — ورودی #'.$fa($entry->id)" width="max-w-6xl"
                :breadcrumbs="[['label' => 'فرم‌ساز', 'url' => route('panel.forms.index')], ['label' => 'ورودی‌ها', 'url' => route('panel.form-entries.index', ['form' => $form->id])], ['label' => '#'.$fa($entry->id)]]">
    <x-slot:actions>
        @if ($prev)<x-admin.button :href="route('panel.form-entries.show', $prev)" variant="secondary" icon="caret-right">قبلی</x-admin.button>@endif
        @if ($next)<x-admin.button :href="route('panel.form-entries.show', $next)" variant="secondary">بعدی <i class="ph ph-caret-left"></i></x-admin.button>@endif
    </x-slot:actions>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <x-admin.card title="اطلاعات ارسال‌شده" icon="list-checks" :padding="false">
            <dl class="divide-y divide-line">
                @foreach ($form->fields ?? [] as $f)
                    @if ($f['type'] === 'section')
                        <div class="bg-slate-50/70 px-5 py-2.5 text-xs font-extrabold text-slate-500 sm:px-6">{{ $f['label'] }}</div>
                    @elseif (FormSchema::isInput($f['type']))
                        @php $value = $entry->data[$f['key']] ?? null; @endphp
                        @continue(! array_key_exists($f['key'], $entry->data ?? []))
                        <div class="grid gap-1 px-5 py-3 sm:grid-cols-[12rem_minmax(0,1fr)] sm:px-6">
                            <dt class="text-sm font-bold text-slate-500">{{ $f['label'] }}</dt>
                            <dd class="min-w-0 whitespace-pre-line break-words text-sm text-ink">
                                @if ($f['type'] === 'file' && is_array($value))
                                    <a href="{{ route('panel.form-entries.file', [$entry, $f['key']]) }}" class="inline-flex items-center gap-1.5 font-bold text-aron-700 hover:underline"><i class="ph ph-paperclip"></i>{{ $value['name'] ?? 'دانلود فایل' }}</a>
                                    <span class="text-xs text-slate-400">({{ $fa(number_format(($value['size'] ?? 0) / 1024)) }} KB)</span>
                                @elseif ($f['type'] === 'email' && $value)
                                    <a href="mailto:{{ $value }}" dir="ltr" class="font-bold text-aron-700 hover:underline">{{ $value }}</a>
                                @elseif (in_array($f['type'], ['mobile', 'tel'], true) && $value)
                                    <a href="tel:{{ $value }}" dir="ltr" class="font-bold text-aron-700 hover:underline">{{ $value }}</a>
                                @elseif ($f['type'] === 'url' && $value)
                                    <a href="{{ $value }}" target="_blank" rel="noopener nofollow" dir="ltr" class="font-bold text-aron-700 hover:underline">{{ $value }}</a>
                                @else
                                    {{ $entry->display($f) !== '' ? $entry->display($f) : '—' }}
                                @endif
                            </dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </x-admin.card>

        <div class="space-y-5">
            <x-admin.card title="پیگیری" icon="clipboard-text">
                <form method="POST" action="{{ route('panel.form-entries.update', $entry) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <x-admin.select name="status" label="وضعیت" :options="FormEntry::STATUSES" :selected="$entry->status" />
                    <x-admin.toggle name="is_starred" label="ستاره‌دار" :checked="$entry->is_starred" />
                    <x-admin.textarea name="admin_note" label="یادداشت داخلی" :value="$entry->admin_note" rows="4" />
                    <x-admin.button type="submit" icon="floppy-disk" class="w-full">ذخیره</x-admin.button>
                </form>
            </x-admin.card>
            <x-admin.card title="جزئیات" icon="info">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">تاریخ ثبت</dt><dd class="font-bold text-ink">{{ jdate($entry->created_at)->format('Y/m/d H:i') }}</dd></div>
                    @if ($entry->user)<div class="flex justify-between gap-3"><dt class="text-slate-500">کاربر</dt><dd class="font-bold text-ink">{{ $entry->user->name }}</dd></div>@endif
                    @if ($entry->handler)<div class="flex justify-between gap-3"><dt class="text-slate-500">آخرین پیگیری</dt><dd class="font-bold text-ink">{{ $entry->handler->name }}</dd></div>@endif
                    @if ($entry->ip)<div class="flex justify-between gap-3"><dt class="text-slate-500">IP</dt><dd dir="ltr" class="font-mono text-xs text-ink">{{ $entry->ip }}</dd></div>@endif
                    @if ($entry->source_url)<div><dt class="text-slate-500">ارسال از صفحه</dt><dd dir="ltr" class="mt-1 truncate text-xs"><a href="{{ $entry->source_url }}" target="_blank" class="text-aron-700 hover:underline">{{ urldecode($entry->source_url) }}</a></dd></div>@endif
                </dl>
            </x-admin.card>
            <x-admin.confirm-delete :action="route('panel.form-entries.destroy', $entry)" message="این ورودی و فایل‌هایش حذف شود؟" label="حذف ورودی" size="md" class="w-full" />
        </div>
    </div>
</x-admin.layout>
