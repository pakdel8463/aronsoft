@php $items = array_values(array_filter($s['items'] ?? [], fn ($m) => ($m['name'] ?? '') !== '')); @endphp
@if (count($items))
    @include('front.t-one.blocks._assets')
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            @include('front.t-one.blocks._head')
            <ul class="bk-team bk-team-{{ $s['columns'] }}">
                @foreach ($items as $m)
                    @php
                        $photo = \App\Support\ProductPageSchema::imageUrl($m['photo'] ?? '');
                        $links = array_filter(['ph-linkedin-logo' => ['لینکدین', $m['linkedin'] ?? ''], 'ph-github-logo' => ['گیت‌هاب', $m['github'] ?? ''], 'ph-instagram-logo' => ['اینستاگرام', $m['instagram'] ?? '']], fn ($l) => $l[1] !== '');
                    @endphp
                    <li class="card bk-member reveal">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $m['name'] }}" class="bk-member-photo" loading="lazy" decoding="async">
                        @else
                            <span class="bk-member-photo bk-member-initial" aria-hidden="true">{{ mb_substr($m['name'], 0, 1) }}</span>
                        @endif
                        <h3 class="bk-member-name">{{ $m['name'] }}</h3>
                        @if (($m['role'] ?? '') !== '')<p class="bk-member-role">{{ $m['role'] }}</p>@endif
                        @if (($m['bio'] ?? '') !== '')<p class="bk-member-bio">{{ $m['bio'] }}</p>@endif
                        @if ($links)
                            <div class="bk-member-links">
                                @foreach ($links as $icon => [$label, $href])
                                    <a href="{{ $href }}" target="_blank" rel="noopener" aria-label="{{ $label }} {{ $m['name'] }}"><i class="ph {{ $icon }}" aria-hidden="true"></i></a>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
