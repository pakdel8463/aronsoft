<section id="{{ $anchor }}" class="section" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        <div class="grid items-center gap-8 overflow-hidden rounded-3xl bg-slate-900 p-6 sm:p-10 lg:grid-cols-2 lg:gap-12 lg:p-14">
            <div class="text-center lg:text-start">
                @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section !text-white">{{ $s['title'] }}</h2>@endif
                @if ($s['description'] !== '')<p class="mt-3 leading-8 text-slate-300">{{ $s['description'] }}</p>@endif
                @if ($s['primary_label'] !== '' || $s['secondary_label'] !== '')
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
                        @if ($s['primary_label'] !== '')<a href="{{ $s['primary_url'] ?: '#' }}" class="btn btn-lg bg-white text-slate-900 hover:bg-slate-100">{{ $s['primary_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>@endif
                        @if ($s['secondary_label'] !== '')<a href="{{ $s['secondary_url'] ?: '#' }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">{{ $s['secondary_label'] }}</a>@endif
                    </div>
                @endif
            </div>
            <div class="rounded-2xl bg-white p-5 sm:p-6">
                @if ($s['form_title'] !== '')<h3 class="mb-1 text-base font-bold text-slate-900">{{ $s['form_title'] }}</h3>@endif
                @if ($s['form_text'] !== '')<p class="mb-4 text-sm text-slate-600">{{ $s['form_text'] }}</p>@endif
                @include('front.t-one.partials.lead-form', [
                    'page' => $page->is_home ? 'home' : $page->slug,
                    'type' => $s['request_type'] ?: 'درخواست مشاوره',
                    'cta' => $s['form_button'] ?: 'درخواست مشاوره',
                    'withName' => (bool) $s['with_name'],
                    'id' => 'lead-'.$anchor,
                ])
            </div>
        </div>
    </div>
</section>
