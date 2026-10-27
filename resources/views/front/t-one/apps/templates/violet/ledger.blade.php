@php
    $dot = ['green' => '#1FC9A0', 'pink' => '#FF6B8A', 'violet' => '#8B5CF6', 'amber' => '#F5A524'];
    $tabs = (array) ($s['tabs'] ?? []);
@endphp
<section id="sec-ledger" style="max-width:1200px;margin:0 auto;padding:104px 32px;display:flex;flex-wrap:wrap;gap:64px;align-items:center">
  <div style="flex:1 1 340px;max-width:430px">
    <h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,40px);line-height:1.5;font-weight:900">{{ $s['title'] }}</h2>
    @if($s['text'] !== '')<p style="margin:0;font-size:17px;line-height:2;color:#5B4E7C">{{ $s['text'] }}</p>@endif
  </div>
  <div style="flex:1.5 1 520px;min-width:0;background:#fff;border:1px solid #E6DEFA;border-radius:26px;box-shadow:0 40px 70px -50px rgba(76,27,196,.5);overflow:hidden">
    @if($tabs)
    <div style="display:flex;gap:8px;padding:16px 20px;border-bottom:1px solid #EEE8FB;background:#FBF9FF;flex-wrap:wrap">
      @foreach($tabs as $i => $tab)
        @if($i === 0)<span style="background:#6D28D9;color:#fff;padding:7px 16px;border-radius:999px;font-size:13px;font-weight:700">{{ $tab }}</span>
        @else<span style="padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;color:#5B4E7C;background:#F1EBFF">{{ $tab }}</span>@endif
      @endforeach
    </div>
    @endif
    @foreach((array) $s['rows'] as $r)
      @php $in = ($r['kind'] ?? 'in') === 'in'; @endphp
      <div class="lr"><span class="v-lr-d" style="color:#7A6C9B;font-size:13px;width:56px">{{ $r['date'] }}</span><b class="v-lr-t" style="flex:1">{{ $r['title'] }}</b>@if($r['tag'] !== '')<span class="ch"><i class="dt" style="background:{{ $dot[$r['tag_color']] ?? $dot['green'] }}"></i>{{ $r['tag'] }}</span>@endif<b class="v-lr-a" style="color:{{ $in ? '#12A383' : '#E5456B' }};width:110px;text-align:left">{{ $in ? '+' : '−' }}{{ $r['amount'] }}</b></div>
    @endforeach
  </div>
</section>
