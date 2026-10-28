{{-- یک فیلد سطح اول بلوک. نوع‌های editor/number/code اینجا؛ بقیه با همان قطعه‌های ویرایشگر صفحات محصول. --}}
@php
    $fkey = $field['key'];
    $name = 'data['.$fkey.']';
    $path = 'data.'.$fkey;
    $fid = 'pb_'.$fkey;
    $stored = session()->hasOldInput() && ! in_array($field['type'], ['repeater', 'toggle', 'image', 'icon'], true) ? old($path, $value) : $value;
@endphp
@switch ($field['type'])
    @case('editor')
        <x-admin.editor :name="$name" :id="$fid" :label="$field['label']" :hint="$field['hint'] ?? null" :value="$stored" :height="$field['height'] ?? 360" />
        @break
    @case('number')
        <x-admin.input :name="$name" :id="$fid" type="number" :label="$field['label']" :hint="$field['hint'] ?? null" :value="$stored" :min="$field['min'] ?? null" :max="$field['max'] ?? null" ltr />
        @break
    @case('code')
        <x-admin.textarea :name="$name" :id="$fid" :label="$field['label']" :hint="$field['hint'] ?? null" :value="$stored" rows="8" dir="ltr" class="font-mono text-xs" spellcheck="false" />
        @break
    @case('form')
        @php $formOptions = \App\Models\Form::orderBy('title')->pluck('title', 'id')->all(); @endphp
        <x-admin.select :name="$name" :id="$fid" :label="$field['label']" :options="$formOptions" :selected="$stored" placeholder="— انتخاب فرم —" :hint="$field['hint'] ?? null" />
        <p class="mt-1.5 text-xs">
            <a href="{{ route('panel.forms.index') }}" target="_blank" class="font-bold text-aron-700 hover:underline">مدیریت فرم‌ها در فرم‌ساز ↗</a>
            @if ($stored) · <a href="{{ route('panel.forms.edit', $stored) }}" target="_blank" class="font-bold text-aron-700 hover:underline">ویرایش همین فرم ↗</a>@endif
        </p>
        @break
    @default
        @include('admin.dash.product-pages._field', ['field' => $field, 'name' => $name, 'path' => $path, 'stored' => $value, 'tpl' => false])
@endswitch
