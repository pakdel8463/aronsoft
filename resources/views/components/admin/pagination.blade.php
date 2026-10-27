@props(['paginator'])
{{-- <x-admin.pagination :paginator="$items" />  ==  {{ $items->links('vendor.pagination.admin') }} --}}
@if (method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div {{ $attributes->class(['mt-4']) }}>{{ $paginator->links('vendor.pagination.admin') }}</div>
@endif
