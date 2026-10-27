@php
    /**
     * ویرایشگر متن غنی (نقطه‌ی تعویض واحد). ورودی‌ها: $name, $value, $label, $hint, $rows
     * اگر کامپوننت <x-admin.editor> وجود داشته باشد از آن استفاده می‌شود، وگرنه textarea ساده.
     */
    $rows = $rows ?? 12;
@endphp
@if (view()->exists('components.admin.editor'))
    <x-admin.editor :name="$name" :value="old($name, $value)" :label="$label ?? null" :hint="$hint ?? null" />
@else
    <x-admin.textarea :name="$name" :value="$value" :label="$label ?? null" :hint="$hint ?? 'می‌توانید از تگ‌های HTML ساده (h2، p، ul، a، img) استفاده کنید.'" :rows="$rows" class="font-mono text-[13px] leading-7" />
@endif
