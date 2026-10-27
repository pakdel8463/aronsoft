@props([
    'name' => 'body',
    'value' => '',
    'label' => null,
    'hint' => null,
    'height' => 480,
    'id' => null,
    'required' => false,
])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?: 'ed_' . str_replace(['.', '[', ']'], '_', $name);
    $val = old($key, $value);
@endphp
{{--
    ویرایشگر کامل (CKEditor 4 محلی + کتابخانه رسانه‌ی خودمان). استفاده:  <x-admin.editor name="body" :value="$post->body" />
    اگر جاوااسکریپت/CKEditor بارگذاری نشود، همان textarea ساده می‌ماند (بدون وابستگی به CDN).
    خروجی سمت سرور باید با App\Support\HtmlSanitizer::clean() پاک‌سازی شود.
--}}
<x-admin.media-config />
@once
    <link rel="stylesheet" href="{{ asset('admin-editor/editor-skin.css') }}?v={{ @filemtime(public_path('admin-editor/editor-skin.css')) }}">
@endonce
<x-admin.field :label="$label" :name="$name" :hint="$hint" :for="$id" :required="$required">
    <div class="aron-editor-wrap overflow-hidden rounded-2xl border border-line bg-white {{ $errors->has($key) ? 'border-coral-400' : '' }}" data-aron-editor-wrap>
        <textarea name="{{ $name }}" id="{{ $id }}" rows="14" dir="rtl" data-aron-editor
                  data-ck-base="{{ asset('ckeditor') }}/" data-assets="{{ asset('admin-editor') }}/" data-height="{{ (int) $height }}"
                  data-v="{{ @filemtime(public_path('admin-editor/editor-content.css')) }}"
                  data-upload-hint="{{ route('panel.media.store') }}"
                  class="a-textarea !rounded-none !border-0 font-mono text-sm" style="min-height: {{ (int) $height }}px">{{ $val }}</textarea>
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line bg-slate-50/80 px-4 py-2 text-xs font-bold text-slate-500" data-editor-statusbar>
            <span data-editor-stats>—</span>
            <span class="hidden items-center gap-1.5 text-slate-400 sm:inline-flex"><i class="ph ph-image"></i>تصویر را بکشید یا paste کنید تا آپلود شود</span>
        </div>
    </div>
</x-admin.field>
