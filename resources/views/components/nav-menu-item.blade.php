@props(['item', 'level' => 0])

@php
    $href = $item->href ?: '#';
    $hasChildren = $item->children->count() > 0;
    $path = trim((string) parse_url($href, PHP_URL_PATH), '/');
    $current = trim(request()->path(), '/');
    $isActive = $href !== '#' && ($path === '' ? $current === '' : ($current === $path || str_starts_with($current, $path.'/')));
    if (! $isActive && $hasChildren) {
        foreach ($item->children as $c) {
            $cp = trim((string) parse_url($c->href ?: '#', PHP_URL_PATH), '/');
            if ($cp !== '' && ($current === $cp || str_starts_with($current, $cp.'/'))) { $isActive = true; break; }
        }
    }
@endphp

@if($level === 0)
    <div class="group relative" @if($hasChildren) data-dropdown @endif>
        <a href="{{ $href }}" class="nav-link {{ $isActive ? 'is-active' : '' }}" @if($isActive && ! $hasChildren) aria-current="page" @endif
           @if($hasChildren) aria-haspopup="true" @endif>
            <span>{{ $item->display_title }}</span>
            @if($hasChildren)<i class="ph ph-caret-down text-xs transition-transform duration-200 group-hover:rotate-180 group-focus-within:rotate-180" aria-hidden="true"></i>@endif
        </a>
        @if($hasChildren)
            <div class="invisible absolute start-0 top-full z-50 pt-2 opacity-0 transition duration-150 group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                <ul class="min-w-[14rem] rounded-xl border border-slate-200 bg-white p-1.5 shadow-card">
                    @foreach($item->children as $child)
                        <li><a href="{{ $child->href ?: '#' }}" class="flex min-h-[44px] items-center whitespace-nowrap rounded-lg px-3 text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-tech-blue">{{ $child->display_title }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
