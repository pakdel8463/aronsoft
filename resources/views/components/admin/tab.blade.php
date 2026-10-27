@props(['name'])
{{-- Panel for <x-admin.tabs>. Hidden panels stay in the DOM, so their inputs are still submitted with the form. --}}
<div data-tab-panel="{{ $name }}" role="tabpanel" {{ $attributes }}>{{ $slot }}</div>
