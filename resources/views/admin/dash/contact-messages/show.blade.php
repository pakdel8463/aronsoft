@php
    $tone = ['new' => 'brand', 'reviewed' => 'amber', 'contacted' => 'sky', 'closed' => 'mint'];
    $row = fn ($l, $v, $ltr = false) => $v ? '<div class="flex items-start justify-between gap-4 border-b border-line/70 py-3 last:border-0"><dt class="shrink-0 text-sm text-slate-500">'.e($l).'</dt><dd class="min-w-0 break-words text-sm font-bold text-ink" '.($ltr ? 'dir="ltr"' : '').'>'.e($v).'</dd></div>' : '';
    $subject = 'Re: '.($message->subject ?: 'پیام شما در آرون سافت');
    $mailto = $message->email ? 'mailto:'.$message->email.'?'.http_build_query(['subject' => $subject, 'body' => "سلام {$message->name} عزیز،\n\n\n\n---\nپیام شما:\n".$message->message], '', '&', PHP_QUERY_RFC3986) : null;
@endphp
<x-admin.layout :title="$message->subject ?: 'پیام از '.$message->name" :subtitle="'از '.$message->name.' · '.jdate($message->created_at)->format('Y/m/d H:i')" :breadcrumbs="[['label' => 'پیام‌های تماس', 'url' => route('panel.contact-messages.index')], ['label' => '#'.$message->id]]">
    <x-slot:actions>
        @if ($mailto)<x-admin.button :href="$mailto" icon="paper-plane-tilt">پاسخ با ایمیل</x-admin.button>@endif
        @if ($message->mobile)<x-admin.button :href="'tel:'.$message->mobile" variant="secondary" icon="phone">تماس</x-admin.button>@endif
    </x-slot:actions>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card title="متن پیام" icon="chat-text">
                <div class="mb-4 flex flex-wrap gap-2">@if ($message->is_spam)<x-admin.badge color="coral" dot>اسپم</x-admin.badge>@endif<x-admin.badge :color="$tone[$message->status] ?? 'slate'" dot>{{ $message->status_label }}</x-admin.badge></div>
                <p class="whitespace-pre-line text-sm leading-8 text-slate-700">{{ $message->message }}</p>
            </x-admin.card>
        </div>
        <div class="space-y-6">
            <x-admin.card title="وضعیت" icon="flag">
                <form method="POST" action="{{ route('panel.contact-messages.status', $message) }}" class="flex gap-2">@csrf @method('PATCH')
                    <select name="status" class="a-select flex-1">@foreach ($statuses as $k => $l)<option value="{{ $k }}" @selected($message->status === $k)>{{ $l }}</option>@endforeach</select>
                    <x-admin.button type="submit" variant="soft" icon="check" icon-only title="ثبت" aria-label="ثبت" />
                </form>
                <div class="mt-3 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('panel.contact-messages.spam', $message) }}">@csrf @method('PATCH')
                        <x-admin.button type="submit" :variant="$message->is_spam ? 'secondary' : 'danger-soft'" size="sm" :icon="$message->is_spam ? 'arrow-counter-clockwise' : 'prohibit'">{{ $message->is_spam ? 'خارج کردن از اسپم' : 'علامت اسپم' }}</x-admin.button></form>
                    <x-admin.confirm-delete :action="route('panel.contact-messages.destroy', $message)" message="این پیام حذف شود؟" label="حذف" />
                </div>
            </x-admin.card>
            <x-admin.card title="مشخصات فرستنده" icon="identification-card">
                <dl>{!! $row('نام', $message->name) !!}{!! $row('ایمیل', $message->email, true) !!}{!! $row('موبایل', $message->mobile, true) !!}{!! $row('بودجه', $message->budget) !!}
                    {!! $row('روش تماس', $message->preferred_contact_method) !!}{!! $row('زمان تماس', $message->preferred_contact_time) !!}{!! $row('صفحه', $message->page_url, true) !!}{!! $row('ارجاع‌دهنده', $message->referrer, true) !!}
                    {!! $row('منبع', collect([$message->utm_source, $message->utm_medium, $message->utm_campaign])->filter()->implode(' / '), true) !!}{!! $row('IP', $message->ip_address, true) !!}</dl>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
