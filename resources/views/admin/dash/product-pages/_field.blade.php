{{-- رندر بازگشتی یک فیلد اسکیما. پارامترها: field, name (bracket), path (dot), stored (مقدار ذخیره‌شده), tpl (ردیف قالب) --}}
@php
    $tpl = $tpl ?? false;
    $type = $field['type'];
    $fkey = $field['key'];
    $fid = 'pp_' . preg_replace('/[^A-Za-z0-9_]+/', '_', $path);
    $label = $field['label'];
    $hint = $field['hint'] ?? null;
    $max = (int) ($field['max'] ?? 0);
    $hasOld = ! $tpl && session()->hasOldInput();
    if ($stored === null) {
        $stored = \App\Support\ProductPageSchema::cleanValue(null, $field);
    }
@endphp
@switch ($type)
    @case('text')
    @case('url')
        <x-admin.input :name="$name" :id="$fid" :type="$type === 'url' ? 'url' : 'text'" :label="$label" :hint="$hint" :value="$stored"
                       :required="! empty($field['required'])" :ltr="$type === 'url' ? true : null" :maxlength="$type === 'url' ? 500 : ($max ?: 255)"
                       :placeholder="$type === 'url' ? 'https://…' : null" data-pp-f="{{ $fkey }}" autocomplete="off" />
        @break

    @case('textarea')
        <x-admin.textarea :name="$name" :id="$fid" :label="$label" :hint="$hint" :value="$stored" :rows="$field['rows'] ?? 3" :maxlength="$max ?: 1000" data-pp-f="{{ $fkey }}" />
        @break

    @case('lines')
        @php
            $linesVal = is_array($stored) ? implode("\n", $stored) : (string) $stored;
            $linesLabel = trim(preg_replace('/\s*[\(（]\s*هر خط یک مورد\s*[\)）]/u', '', $label));
            $linesHint = ($hint ? $hint . ' · ' : '') . 'هر خط یک مورد' . (! empty($field['max_items']) ? ' (حداکثر ' . \App\Helpers\Fa::toFa((string) $field['max_items']) . ' مورد)' : '');
        @endphp
        <x-admin.textarea :name="$name" :id="$fid" :label="$linesLabel" :hint="$linesHint" :value="$linesVal" :rows="$field['rows'] ?? 4" data-pp-f="{{ $fkey }}" />
        @break

    @case('select')
        <x-admin.select :name="$name" :id="$fid" :label="$label" :hint="$hint" :options="$field['options'] ?? []" :selected="$stored ?: ($field['default'] ?? null)" data-pp-f="{{ $fkey }}" />
        @break

    @case('toggle')
        @php
            $on = $tpl ? (bool) $stored : ($hasOld ? filter_var(old($path, 0), FILTER_VALIDATE_BOOLEAN) : (bool) $stored);
        @endphp
        <div class="flex min-h-11 items-center">
            <label for="{{ $fid }}" class="flex cursor-pointer items-start gap-3">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" class="a-switch mt-0.5" name="{{ $name }}" id="{{ $fid }}" value="1" data-pp-f="{{ $fkey }}" @checked($on)>
                <span class="min-w-0">
                    <span class="block text-sm font-bold leading-6 text-slate-700">{{ $label }}</span>
                    @if ($hint)<span class="block text-xs leading-5 text-slate-400">{{ $hint }}</span>@endif
                    @if ($errors->has($path))<span class="block text-xs font-semibold text-coral-600">{{ $errors->first($path) }}</span>@endif
                </span>
            </label>
        </div>
        @break

    @case('image')
        @include('admin.dash.product-pages._image', ['name' => $name, 'id' => $fid, 'value' => $tpl ? '' : ($hasOld ? old($path, '') : $stored), 'path' => $path, 'label' => $label, 'hint' => $hint, 'key' => $fkey])
        @break

    @case('icon')
        @include('admin.dash.product-pages._icon-picker', ['name' => $name, 'id' => $fid, 'value' => $tpl ? '' : ($hasOld ? old($path, '') : $stored), 'path' => $path, 'label' => $label, 'hint' => $hint, 'key' => $fkey])
        @break

    @case('repeater')
        @include('admin.dash.product-pages._repeater')
        @break
@endswitch
