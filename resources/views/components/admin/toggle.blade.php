@props(['name', 'label' => null, 'hint' => null, 'value' => '1', 'checked' => false, 'unchecked' => '0', 'id' => null])
{{-- Toggle switch = <x-admin.checkbox switch>. Posts "1" when on and "0" when off by default. --}}
<x-admin.checkbox :name="$name" :label="$label" :hint="$hint" :value="$value" :checked="$checked" :unchecked="$unchecked" :id="$id" :switch="true" {{ $attributes }} />
