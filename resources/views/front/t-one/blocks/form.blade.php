@php $theForm = $s['form'] ? \App\Models\Form::find($s['form']) : null; @endphp
@if ($theForm)
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            @if ($s['layout'] === 'split')
                <div class="grid items-start gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-14">
                    <div class="reveal lg:sticky lg:top-24">
                        @include('front.t-one.blocks._head', ['start' => true])
                        @if (trim(strip_tags($s['side_text'])) !== '')<div class="rich">{!! $s['side_text'] !!}</div>@endif
                    </div>
                    <div>@include('front.t-one.forms.form', ['form' => $theForm])</div>
                </div>
            @else
                @include('front.t-one.blocks._head')
                <div class="mx-auto max-w-3xl">@include('front.t-one.forms.form', ['form' => $theForm])</div>
            @endif
        </div>
    </section>
@elseif ($preview ?? false)
    <div id="{{ $anchor }}" class="container-x py-6"><p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">بلوک «فرم»: هنوز فرمی انتخاب نشده است.</p></div>
@endif
