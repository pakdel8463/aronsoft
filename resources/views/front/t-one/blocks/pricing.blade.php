@php $n = count($s['plans']); @endphp
<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        @include('front.t-one.blocks._head')
        <div class="mx-auto grid max-w-6xl items-stretch gap-5 md:grid-cols-2 {{ $n >= 4 ? 'xl:grid-cols-4' : ($n === 3 ? 'lg:grid-cols-3' : '') }}">
            @foreach ($s['plans'] as $plan)
                <div class="reveal relative flex flex-col rounded-2xl p-6 sm:p-7 {{ $plan['featured'] ? 'border-2 border-tech-blue bg-white shadow-card' : 'card' }}">
                    @if ($plan['featured'])<span class="absolute -top-3 start-6 rounded-full bg-tech-blue px-3 py-1 text-xs font-bold text-white">پیشنهاد ما</span>@endif
                    <h3 class="text-lg font-extrabold text-slate-900">{{ $plan['name'] }}</h3>
                    @if ($plan['description'] !== '')<p class="mt-1 text-sm text-slate-600">{{ $plan['description'] }}</p>@endif
                    @if ($plan['price'] !== '')
                        <p class="mt-5 flex items-baseline gap-1.5"><span class="num text-3xl font-extrabold text-slate-900">{{ $plan['price'] }}</span><span class="text-sm text-slate-500">{{ $plan['period'] }}</span></p>
                    @endif
                    @if (! empty($plan['features']))
                        <ul class="mt-5 flex-1 space-y-2.5 border-t border-slate-100 pt-5 text-sm text-slate-700">
                            @foreach ($plan['features'] as $f)
                                <li class="flex items-start gap-2"><i class="ph-bold ph-check mt-1 text-tech-blue" aria-hidden="true"></i> {{ $f }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="flex-1"></div>
                    @endif
                    @if ($plan['button_label'] !== '')
                        <a href="{{ $plan['button_url'] ?: '#' }}" class="btn {{ $plan['featured'] ? 'btn-primary' : 'btn-secondary' }} btn-block mt-6">{{ $plan['button_label'] }}</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
