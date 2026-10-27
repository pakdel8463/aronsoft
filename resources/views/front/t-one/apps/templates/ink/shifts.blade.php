@php
    $lg = array_values($s['legend'] ?? []);
    $days = array_values($s['days'] ?? []);
    $cls = function ($c) use ($lg) {
        $i = array_search($c, $lg, true);

        return match ($i) { 0 => 'pm', 1 => 'pe', 2 => 'pn', default => 'po' };
    };
@endphp
<!-- shifts -->
<section id="sec-shifts" style="max-width:1200px;margin:0 auto;padding:96px 32px;display:flex;flex-wrap:wrap;gap:64px;align-items:center">
  <div id="shifts" style="flex:1.6 1 520px;min-width:0;background:#fff;border:1px solid #D8E1E5;border-radius:22px;padding:22px;box-shadow:0 40px 70px -48px rgba(12,26,38,.3)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px">
      <b style="font-size:15px">{{ $s['card_title'] ?? '' }}</b>
      <span style="display:flex;gap:14px;font-size:12px;font-weight:600;color:#43535E">
        @foreach($lg as $i => $l)
        <span style="display:flex;align-items:center;gap:6px"><i style="width:10px;height:10px;border-radius:3px;background:{{ ['var(--t-soft)', '#E0E4FA', '#FCEBCF'][$i] ?? '#E0E4FA' }};display:block"></i>{{ $l }}</span>
        @endforeach
      </span>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;overflow-x:auto">
      <div class="wk" style="min-width:520px"><span></span>@foreach($days as $d)<span class="dh">{{ $d }}</span>@endforeach</div>
      @foreach($s['rows'] ?? [] as $r)
      <div class="wk" style="min-width:520px"><b style="font-size:13px">{{ $r['name'] ?? '' }}</b>@foreach(array_slice($r['cells'] ?? [], 0, 7) as $c)<span class="pill {{ $cls($c) }}">{{ $c }}</span>@endforeach</div>
      @endforeach
    </div>
  </div>
  <div style="flex:1 1 340px;max-width:420px">
    @if(trim($s['title'] ?? '') !== '')<h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,38px);line-height:1.5;font-weight:800">{{ $s['title'] }}</h2>@endif
    @if(trim($s['text'] ?? '') !== '')<p style="margin:0;font-size:17px;line-height:2;color:#43535E">{{ $s['text'] }}</p>@endif
  </div>
</section>
