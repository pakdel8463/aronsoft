@php /** @var array<int, array{0:string,1:?string}> $items */ @endphp
<nav aria-label="مسیر صفحه" class="mb-5 text-sm text-slate-500">
    <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
        @foreach($items as $i => [$label, $href])
            <li class="flex items-center gap-1.5">
                @if($href && ! $loop->last)
                    <a href="{{ $href }}" class="inline-flex min-h-[32px] items-center hover:text-tech-blue">{{ $label }}</a>
                    <i class="ph ph-caret-left text-xs text-slate-400" aria-hidden="true"></i>
                @else
                    <span class="font-semibold text-slate-700" aria-current="page" dir="auto">{{ \Illuminate\Support\Str::limit($label, 70) }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
{!! \App\View\Seo::jsonLd(\App\View\Seo::breadcrumbs(array_map(fn ($b) => [$b[0], $b[1] ?? url()->current()], $items))) !!}
