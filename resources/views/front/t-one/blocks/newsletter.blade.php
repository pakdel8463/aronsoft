@php $nid = 'nl-'.$anchor; @endphp
<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        <div class="card mx-auto max-w-xl p-6 text-center sm:p-8">
            <span class="icon-tile mx-auto mb-3"><i class="ph ph-envelope-simple" aria-hidden="true"></i></span>
            @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="text-xl font-extrabold text-slate-900">{{ $s['title'] }}</h2>@endif
            @if ($s['text'] !== '')<p class="mx-auto mb-5 mt-2 max-w-md text-sm leading-7 text-slate-600">{{ $s['text'] }}</p>@endif
            <form action="{{ route('newsletter.subscribe') }}" method="POST" data-ajax-form data-hide-on-success novalidate class="mx-auto max-w-sm space-y-1 text-start">
                @csrf
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div data-form-fields>
                    <label for="{{ $nid }}-email" class="form-label">ایمیل</label>
                    <input id="{{ $nid }}-email" type="email" name="email" required class="form-input mb-3 text-left" dir="ltr" autocomplete="email" placeholder="name@example.com">
                    <x-captcha />
                    <button type="submit" class="btn btn-primary btn-block">عضویت در خبرنامه</button>
                </div>
                <p data-form-msg class="form-msg" hidden aria-live="polite"></p>
            </form>
        </div>
    </div>
</section>
