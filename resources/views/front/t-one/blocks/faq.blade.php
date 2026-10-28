<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        @include('front.t-one.blocks._head')
        <div class="faq mx-auto max-w-3xl space-y-3">
            @foreach ($s['items'] as $i => $item)
                <details class="reveal" @if ($i === 0) open @endif>
                    <summary>{{ $item['question'] }} <i class="ph ph-caret-down" aria-hidden="true"></i></summary>
                    <p>{!! nl2br(e($item['answer'])) !!}</p>
                </details>
            @endforeach
        </div>
    </div>
    @if ($s['schema'] && count($s['items']))
        {!! \App\View\Seo::jsonLd(['@type' => 'FAQPage', 'mainEntity' => collect($s['items'])->map(fn ($q) => [
            '@type' => 'Question', 'name' => $q['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q['answer']],
        ])->all()]) !!}
    @endif
</section>
