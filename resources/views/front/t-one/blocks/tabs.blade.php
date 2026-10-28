@php $items = array_values(array_filter($s['items'] ?? [], fn ($t) => ($t['tab'] ?? '') !== '')); @endphp
@if (count($items))
    @include('front.t-one.blocks._assets')
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            @include('front.t-one.blocks._head')
            <div class="bk-tabs bk-tabs-{{ $s['style'] }}" data-tabs-block>
                <div class="bk-tablist" role="tablist" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @else aria-label="تب‌ها" @endif>
                    @foreach ($items as $i => $t)
                        <button type="button" role="tab" class="bk-tab" id="{{ $anchor }}-tab-{{ $i }}" aria-controls="{{ $anchor }}-panel-{{ $i }}"
                                aria-selected="{{ $i === 0 ? 'true' : 'false' }}" tabindex="{{ $i === 0 ? 0 : -1 }}">
                            @if (($t['icon'] ?? '') !== '')<i class="ph {{ $t['icon'] }}" aria-hidden="true"></i>@endif
                            <span>{{ $t['tab'] }}</span>
                        </button>
                    @endforeach
                </div>
                @foreach ($items as $i => $t)
                    @php $img = \App\Support\ProductPageSchema::imageUrl($t['image'] ?? ''); @endphp
                    <div class="bk-tabpanel {{ $img ? 'has-image' : '' }}" role="tabpanel" id="{{ $anchor }}-panel-{{ $i }}" aria-labelledby="{{ $anchor }}-tab-{{ $i }}" tabindex="0" @if ($i > 0) hidden @endif>
                        <div class="bk-tabpanel-text">
                            @if (($t['title'] ?? '') !== '')<h3 class="bk-tabpanel-title">{{ $t['title'] }}</h3>@endif
                            @if (($t['text'] ?? '') !== '')<p class="bk-tabpanel-p">{!! nl2br(e($t['text'])) !!}</p>@endif
                            @if (! empty($t['bullets']))
                                <ul class="bk-checks">
                                    @foreach ($t['bullets'] as $b)<li><i class="ph ph-check-circle" aria-hidden="true"></i><span>{{ $b }}</span></li>@endforeach
                                </ul>
                            @endif
                            @if (($t['button_label'] ?? '') !== '')
                                <a href="{{ ($t['button_url'] ?? '') ?: '#' }}" class="btn btn-primary mt-6">{{ $t['button_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                            @endif
                        </div>
                        @if ($img)
                            <div class="bk-tabpanel-media"><img src="{{ $img }}" alt="{{ $t['title'] ?: $t['tab'] }}" loading="lazy" decoding="async"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
