@php
    $cols = array_values($s['columns'] ?? []);
    $n = max(1, count($cols));
    $tpl = 'grid-template-columns:200px repeat('.$n.',1fr)';
    $minw = 200 + $n * 220;
    $hasCta = collect($cols)->contains(fn ($c) => trim($c['cta_label'] ?? '') !== '');
@endphp
<!-- plans -->
<section id="sec-plans" style="max-width:1200px;margin:0 auto;padding:96px 32px">
  <div id="plans" style="max-width:620px;margin-bottom:40px">
    @if(trim($s['title'] ?? '') !== '')<h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,38px);line-height:1.5;font-weight:800">{{ $s['title'] }}</h2>@endif
    @if(trim($s['text'] ?? '') !== '')<p style="margin:0;font-size:17px;line-height:2;color:#43535E">{{ $s['text'] }}</p>@endif
  </div>
  <div style="border:1px solid #C9D5DA;border-radius:22px;overflow:hidden;background:#fff;overflow-x:auto">
    <div style="min-width:{{ $minw }}px">
      <div style="display:grid;{{ $tpl }};background:var(--t-ink);color:#fff">
        <div style="padding:22px 24px"></div>
        @foreach($cols as $c)
        <div style="padding:22px 24px;border-inline-start:1px solid #243948"><b style="font-size:18px">{{ $c['title'] ?? '' }}</b>@if(trim($c['sub'] ?? '') !== '')<div style="font-size:13px;color:#9FB2BE;margin-top:4px">{{ $c['sub'] }}</div>@endif</div>
        @endforeach
      </div>
      @foreach($s['rows'] ?? [] as $r)
      @php $vals = array_values($r['values'] ?? []); @endphp
      <div style="display:grid;{{ $tpl }};border-top:1px solid #E6EDF0;font-size:15px">
        <div style="padding:20px 24px;color:#5A6A75;font-weight:600">{{ $r['label'] ?? '' }}</div>
        @for($i = 0; $i < $n; $i++)
        <div style="padding:20px 24px;border-inline-start:1px solid #E6EDF0">{{ $vals[$i] ?? '' }}</div>
        @endfor
      </div>
      @endforeach
      @if($hasCta)
      <div style="display:grid;{{ $tpl }};border-top:1px solid #E6EDF0;font-size:15px;background:#F8FAFB">
        <div style="padding:22px 24px;color:#5A6A75;font-weight:600">{{ $s['cta_row_label'] ?? '' }}</div>
        @foreach($cols as $c)
        @php $cu = trim($c['cta_url'] ?? '') !== '' ? $c['cta_url'] : $page->primaryUrl(); @endphp
        <div style="padding:22px 24px;border-inline-start:1px solid #E6EDF0">
          @if(trim($c['cta_label'] ?? '') !== '')
            @if(($c['cta_style'] ?? 'solid') === 'outline')
            <a {{ $ln($cu) }} style="display:inline-block;border:1px solid #9FB2BE;padding:11px 22px;border-radius:12px;font-weight:700;font-size:14px">{{ $c['cta_label'] }}</a>
            @else
            <a {{ $ln($cu) }} style="display:inline-block;background:var(--t-accent);color:#fff;padding:12px 22px;border-radius:12px;font-weight:700;font-size:14px">{{ $c['cta_label'] }}</a>
            @endif
          @endif
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
</section>
