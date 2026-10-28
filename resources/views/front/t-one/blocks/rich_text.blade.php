<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}">
    <div class="container-x">
        @include('front.t-one.blocks._head', ['start' => $s['width'] === 'wide'])
        @if (trim(strip_tags($s['body'], '<img><iframe><video>')) !== '')
            <div class="rich {{ $s['width'] === 'narrow' ? 'mx-auto max-w-3xl' : '' }}">{!! \App\Forms\Shortcodes::render($s['body']) !!}</div>
        @endif
    </div>
</section>
