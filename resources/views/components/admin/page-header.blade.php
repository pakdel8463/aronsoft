@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => [],
])
<div {{ $attributes->class(['mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if (count($breadcrumbs))
            <nav aria-label="breadcrumb" class="mb-2 flex flex-wrap items-center gap-1.5 text-xs font-semibold text-slate-400">
                <a href="{{ route('panel.home') }}" class="hover:text-aron-600"><i class="ph ph-house"></i></a>
                @foreach ($breadcrumbs as $crumb)
                    <i class="ph ph-caret-left text-[10px]"></i>
                    @if (! empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="hover:text-aron-600">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-slate-600">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="text-xl font-extrabold leading-9 text-ink sm:text-2xl">{{ $title }}</h1>
        @if ($subtitle)<p class="mt-1 text-sm leading-6 text-slate-500">{{ $subtitle }}</p>@endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
