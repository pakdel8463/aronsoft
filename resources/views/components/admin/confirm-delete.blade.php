@props([
    'action',
    'method' => 'DELETE',
    'message' => 'آیا از حذف این مورد مطمئن هستید؟ این عمل قابل بازگشت نیست.',
    'label' => 'حذف',
    'icon' => 'trash',
    'iconOnly' => false,
    'variant' => 'danger-soft',
    'size' => 'sm',
])
{{-- Form + native confirm() (handled by admin shell JS via data-confirm). Put it inside table cells / button groups. --}}
<form method="POST" action="{{ $action }}" data-confirm="{{ $message }}" class="inline-flex">
    @csrf
    @method($method)
    <x-admin.button type="submit" :variant="$variant" :size="$size" :icon="$icon" :icon-only="$iconOnly" {{ $attributes->merge(['title' => $label, 'aria-label' => $label]) }}>@unless ($iconOnly){{ $label }}@endunless</x-admin.button>
</form>
