{{-- name, label, type=text, icon, ac (autocomplete), ltr=false, placeholder, value, help --}}
@php $fid = 'f-'.$name; $ltr = $ltr ?? false; @endphp
<div>
    <label for="{{ $fid }}" class="form-label">{{ $label }}</label>
    <div class="relative">
        <i class="ph ph-{{ $icon ?? 'user' }} pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-lg text-slate-400" aria-hidden="true"></i>
        <input id="{{ $fid }}" type="{{ $type ?? 'text' }}" name="{{ $name }}" value="{{ $value ?? '' }}" required
               class="form-input !pr-11 {{ $ltr ? 'text-left' : '' }}" @if($ltr) dir="ltr" @endif
               autocomplete="{{ $ac ?? 'off' }}" placeholder="{{ $placeholder ?? '' }}"
               @error($name) aria-invalid="true" aria-describedby="{{ $fid }}-err" @enderror>
    </div>
    @error($name)<p id="{{ $fid }}-err" class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
</div>
