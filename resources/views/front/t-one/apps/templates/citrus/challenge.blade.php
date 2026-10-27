@php
    $pct = max(0, min(100, (int) preg_replace('/\D+/', '', strtr((string) $s['percent'], ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']))));
    $bg = ['lime' => '#B6F23C', 'amber' => '#FFD27A', 'teal' => '#BFE9E4'];
    $fa = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $hasCard = $s['card_title'] || $s['badge'] || $s['ranking'];
@endphp
<section id="sec-challenge" style="background:var(--t-accent);border-radius:60px;color:var(--t-dark)">
  <div style="max-width:1200px;margin:0 auto;padding:100px 32px;display:flex;flex-wrap:wrap;gap:56px;align-items:center">
    <div style="flex:1 1 340px;max-width:440px">
      @if($s['title'])<h2 class="hd" style="margin:0 0 16px;font-size:clamp(38px,4.4vw,58px);line-height:1.25">{!! nl2br(e($s['title'])) !!}</h2>@endif
      @if($s['text'])<p style="margin:0;font-size:17px;line-height:2">{{ $s['text'] }}</p>@endif
    </div>
    @if($hasCard)
    <div style="flex:1.2 1 460px;min-width:0;background:#fff;border-radius:30px;padding:26px;box-shadow:0 40px 60px -40px rgba(6,47,38,.6);color:var(--t-ink)">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px"><b class="hd" style="font-size:26px">{{ $s['card_title'] }}</b>@if($s['badge'])<span class="pill" style="background:#EAF8D6;color:#2F5F0C">{{ $s['badge'] }}</span>@endif</div>
      <div style="height:12px;border-radius:8px;background:#EAF8D6;overflow:hidden;margin-bottom:20px" role="img" aria-label="{{ $pct }}%"><i style="display:block;height:100%;width:{{ $pct }}%;background:#16A34A;border-radius:8px"></i></div>
      @foreach($s['ranking'] as $r)
      <div class="row"><b class="hd" style="font-size:22px;width:26px;color:{{ $loop->first ? '#16A34A' : '#6B8A80' }}">{{ $fa($loop->iteration) }}</b><span style="width:34px;height:34px;border-radius:50%;background:{{ $bg[$r['tone']] ?? $bg['lime'] }};display:grid;place-items:center;font-weight:800;font-size:13px;flex:none">{{ mb_substr(trim($r['name']), 0, 1) }}</span><b style="flex:1;font-size:14px">{{ $r['name'] }}</b><span style="font-size:13px;color:#3C5B52">{{ $r['stat'] }}</span></div>
      @endforeach
    </div>
    @endif
  </div>
</section>
