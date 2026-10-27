{{-- کارت یک بخش صفحه. ورودی: page, key, def, enabled, open --}}
@php
    $locked = ! empty($def['locked']);
    $bodyId = 'ppsec_' . $key . '_body';
    $titleId = 'ppsec_' . $key . '_title';
    $secVals = $page->section($key);
@endphp
<section class="a-card overflow-hidden transition" data-pp-section="{{ $key }}" @if ($locked) data-locked="1" @endif data-enabled="{{ $enabled ? '1' : '0' }}" aria-labelledby="{{ $titleId }}">
    <input type="hidden" name="layout[]" value="{{ $key }}">
    <header class="flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3 sm:px-5">
        <button type="button" class="flex min-w-0 flex-1 basis-56 items-center gap-3 rounded-2xl py-1 text-start focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100"
                data-pp-toggle aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $bodyId }}">
            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-aron-50 text-xl text-aron-600" data-pp-sec-icon><i class="ph ph-{{ $def['icon'] ?? 'squares-four' }}"></i></span>
            <span class="min-w-0">
                <span class="flex flex-wrap items-center gap-2">
                    <span id="{{ $titleId }}" class="text-sm font-extrabold text-ink">{{ $def['label'] }}</span>
                    @if ($locked)<x-admin.badge icon="lock-simple">ثابت</x-admin.badge>@endif
                    <span data-pp-off-badge class="{{ $enabled ? 'hidden' : '' }}"><x-admin.badge color="amber">خاموش</x-admin.badge></span>
                </span>
                @if (! empty($def['hint']))<span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ $def['hint'] }}</span>@endif
            </span>
            <i class="ph ph-caret-down ms-auto shrink-0 text-lg text-slate-400 transition" data-pp-caret></i>
        </button>
        <div class="flex shrink-0 items-center gap-2">
            @unless ($locked)
                <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-slate-500">
                    <input type="checkbox" class="a-switch" name="enabled[{{ $key }}]" value="1" data-pp-enabled @checked($enabled) aria-label="نمایش بخش «{{ $def['label'] }}» در صفحه">
                    <span class="hidden sm:inline">نمایش</span>
                </label>
                <span class="h-6 w-px bg-line" aria-hidden="true"></span>
                <button type="button" class="a-btn a-btn-ghost a-btn-icon a-btn-sm" data-pp-up title="انتقال به بالا" aria-label="انتقال «{{ $def['label'] }}» به بالا"><i class="ph ph-arrow-up"></i></button>
                <button type="button" class="a-btn a-btn-ghost a-btn-icon a-btn-sm" data-pp-down title="انتقال به پایین" aria-label="انتقال «{{ $def['label'] }}» به پایین"><i class="ph ph-arrow-down"></i></button>
            @endunless
        </div>
    </header>
    <div id="{{ $bodyId }}" class="border-t border-line p-4 sm:p-5 {{ $open ? '' : 'hidden' }}" data-pp-body>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($def['fields'] as $f)
                @php $wide = in_array($f['type'], ['textarea', 'lines', 'repeater'], true) || ($f['type'] === 'text' && ($f['max'] ?? 0) >= 80); @endphp
                <div class="min-w-0 {{ $wide ? 'sm:col-span-2' : '' }}">
                    @include('admin.dash.product-pages._field', [
                        'field' => $f,
                        'name' => "sections[{$key}][{$f['key']}]",
                        'path' => "sections.{$key}.{$f['key']}",
                        'stored' => $secVals[$f['key']] ?? null,
                        'tpl' => false,
                    ])
                </div>
            @endforeach
        </div>
    </div>
</section>
