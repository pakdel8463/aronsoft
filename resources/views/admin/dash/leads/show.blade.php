@php($row = fn ($l, $v, $ltr = false) => $v ? '<div class="flex items-start justify-between gap-4 border-b border-line/70 py-3 last:border-0"><dt class="shrink-0 text-sm text-slate-500">'.e($l).'</dt><dd class="min-w-0 break-words text-sm font-bold text-ink" '.($ltr ? 'dir="ltr"' : '').'>'.e($v).'</dd></div>' : '')
<x-admin.layout :title="$lead->full_name ?: 'لید #'.$lead->id" subtitle="ثبت‌شده در {{ jdate($lead->created_at)->format('Y/m/d H:i') }}" :breadcrumbs="[['label' => 'لیدها', 'url' => route('panel.leads.index')], ['label' => $lead->full_name ?: '#'.$lead->id]]">
    <x-slot:actions>
        <form method="POST" action="{{ route('panel.leads.status', $lead) }}">@csrf<input type="hidden" name="is_contacted" value="{{ $lead->is_contacted ? 0 : 1 }}">
            <x-admin.button type="submit" :variant="$lead->is_contacted ? 'secondary' : 'success'" :icon="$lead->is_contacted ? 'arrow-counter-clockwise' : 'check'">{{ $lead->is_contacted ? 'نیاز به پیگیری' : 'تماس گرفته شد' }}</x-admin.button></form>
        <x-admin.confirm-delete :action="route('panel.leads.destroy', $lead)" message="این لید حذف شود؟" label="حذف" />
    </x-slot:actions>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card title="پیام" icon="chat-text">
                <p class="whitespace-pre-line text-sm leading-8 text-slate-700">{{ $lead->message ?: 'پیامی ثبت نشده است.' }}</p>
            </x-admin.card>
            <x-admin.card title="یادداشت داخلی" icon="note-pencil">
                <form method="POST" action="{{ route('panel.leads.note', $lead) }}" class="space-y-3">@csrf @method('PATCH')
                    <x-admin.textarea name="admin_note" label="متن یادداشت" rows="4" :value="$lead->admin_note" placeholder="نتیجه تماس، پیگیری بعدی…" maxlength="5000" />
                    <x-admin.button type="submit" icon="floppy-disk" loading>ذخیره یادداشت</x-admin.button>
                </form>
            </x-admin.card>
        </div>
        <div class="space-y-6">
            <x-admin.card title="مشخصات" icon="identification-card">
                <div class="mb-3">@if ($lead->is_contacted)<x-admin.badge color="mint" dot>تماس گرفته شد</x-admin.badge>@else<x-admin.badge color="amber" dot>نیاز به پیگیری</x-admin.badge>@endif</div>
                <dl>
                    {!! $row('نام', $lead->full_name) !!}{!! $row('شرکت', $lead->company) !!}{!! $row('موبایل', $lead->mobile, true) !!}{!! $row('ایمیل', $lead->email, true) !!}
                    {!! $row('نوع درخواست', $lead->request_type) !!}{!! $row('صفحه فرم', $lead->page, true) !!}{!! $row('ارجاع‌دهنده', $lead->referrer, true) !!}
                    {!! $row('منبع', collect([$lead->utm_source, $lead->utm_medium, $lead->utm_campaign])->filter()->implode(' / '), true) !!}{!! $row('IP', $lead->ip, true) !!}
                </dl>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($lead->mobile)<x-admin.button :href="'tel:'.$lead->mobile" variant="secondary" size="sm" icon="phone">تماس</x-admin.button>@endif
                    @if ($lead->email)<x-admin.button :href="'mailto:'.$lead->email" variant="secondary" size="sm" icon="envelope-simple">ایمیل</x-admin.button>@endif
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
