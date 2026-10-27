{{-- Admin pagination:  {{ $items->links('vendor.pagination.admin') }}  or  <x-admin.pagination :paginator="$items" /> --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="صفحه‌بندی" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-xs font-semibold text-slate-400">
            @if ($paginator->firstItem())
                نمایش {{ $paginator->firstItem() }} تا {{ $paginator->lastItem() }} از {{ $paginator->total() }} مورد
            @else
                {{ $paginator->count() }} مورد
            @endif
        </p>

        <ul class="flex flex-wrap items-center justify-center gap-1.5">
            {{-- Previous (RTL: arrow points right) --}}
            @if ($paginator->onFirstPage())
                <li><span class="grid size-9 place-items-center rounded-xl border border-line bg-white text-slate-300" aria-disabled="true"><i class="ph ph-caret-right"></i></span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="قبلی" class="grid size-9 place-items-center rounded-xl border border-line bg-white text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"><i class="ph ph-caret-right"></i></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="grid h-9 min-w-6 place-items-center px-1 text-sm text-slate-400">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span aria-current="page" class="grid h-9 min-w-9 place-items-center rounded-xl bg-brand-600 px-2 text-sm font-extrabold text-white shadow-brand">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" aria-label="صفحه {{ $page }}" class="grid h-9 min-w-9 place-items-center rounded-xl border border-line bg-white px-2 text-sm font-bold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next (RTL: arrow points left) --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="بعدی" class="grid size-9 place-items-center rounded-xl border border-line bg-white text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"><i class="ph ph-caret-left"></i></a></li>
            @else
                <li><span class="grid size-9 place-items-center rounded-xl border border-line bg-white text-slate-300" aria-disabled="true"><i class="ph ph-caret-left"></i></span></li>
            @endif
        </ul>
    </nav>
@endif
