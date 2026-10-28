@if (trim($s['code']) !== '')
    <div id="{{ $anchor }}" @class(['container-x py-8' => $s['container']])>{!! $s['code'] !!}</div>
@elseif ($preview ?? false)
    <div id="{{ $anchor }}" class="container-x py-6"><p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">بلوک «کد HTML» خالی است.</p></div>
@endif
