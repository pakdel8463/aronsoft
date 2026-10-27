@if ($paginator->hasPages())
    <nav role="navigation" aria-label="صفحه‌بندی" class="mt-12">
        <div class="pager">
            @if ($paginator->onFirstPage())
                <span class="page is-disabled" aria-disabled="true" aria-label="صفحه قبل"><i class="ph ph-caret-right" aria-hidden="true"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="صفحه قبل"><i class="ph ph-caret-right" aria-hidden="true"></i></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page is-disabled" aria-hidden="true">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page is-current num" aria-current="page">{{ \App\Helpers\Fa::toFa($page) }}</span>
                        @else
                            <a href="{{ $url }}" class="num" aria-label="صفحه {{ \App\Helpers\Fa::toFa($page) }}">{{ \App\Helpers\Fa::toFa($page) }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="صفحه بعد"><i class="ph ph-caret-left" aria-hidden="true"></i></a>
            @else
                <span class="page is-disabled" aria-disabled="true" aria-label="صفحه بعد"><i class="ph ph-caret-left" aria-hidden="true"></i></span>
            @endif
        </div>
    </nav>
@endif
