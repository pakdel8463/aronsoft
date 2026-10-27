@php
    $days = max(0, min(7, (int) preg_replace('/\D+/', '', strtr((string) $s['plan_days'], ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7']))));
    $pct = max(0, min(100, (int) preg_replace('/\D+/', '', strtr((string) $s['protein_percent'], ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']))));
    $hasPlan = $s['badge'] || $s['plan_title'] || $s['plan_text'];
    $hasCal = $s['cal_title'] || $s['cal_text'] || $s['protein_label'] || $s['protein_value'];
@endphp
<section id="sec-diet" style="max-width:1200px;margin:0 auto;padding:104px 32px;display:flex;flex-wrap:wrap;gap:60px;align-items:center">
  <div style="flex:1 1 340px;max-width:440px">
    @if($s['title'])<h2 class="hd" style="margin:0 0 16px;font-size:clamp(38px,4.4vw,58px);line-height:1.25">{!! nl2br(e($s['title'])) !!}</h2>@endif
    @if($s['text'])<p style="margin:0;font-size:17px;line-height:2;color:#3C5B52">{{ $s['text'] }}</p>@endif
  </div>
  @if($hasPlan || $hasCal)
  <div style="flex:1.4 1 500px;min-width:0;display:flex;gap:16px;flex-wrap:wrap">
    @if($hasPlan)
    <div style="flex:1 1 220px;background:var(--t-dark);color:#fff;border-radius:28px;padding:26px">
      @if($s['badge'])<span class="pill" style="background:var(--t-accent);color:var(--t-dark)">{{ $s['badge'] }}</span>@endif
      <b class="hd" style="font-size:30px;display:block;margin:16px 0 6px;line-height:1.3">{{ $s['plan_title'] }}</b>
      <div style="font-size:14px;color:#C9E6DA;line-height:2">{{ $s['plan_text'] }}</div>
      <div style="display:flex;gap:6px;margin-top:18px" aria-hidden="true">
        @for($i = 1; $i <= 7; $i++)<i style="flex:1;height:44px;border-radius:12px;background:{{ $i <= $days ? 'var(--t-accent)' : ($i === $days + 1 ? 'rgba(255,255,255,.18)' : 'rgba(255,255,255,.1)') }};display:block"></i>@endfor
      </div>
    </div>
    @endif
    @if($hasCal)
    <div style="flex:1 1 220px;background:#FFE9B8;border-radius:28px;padding:26px;color:#4A3300">
      <b class="hd" style="font-size:30px;display:block;margin-bottom:12px;line-height:1.3">{{ $s['cal_title'] }}</b>
      <div style="font-size:14px;line-height:2">{{ $s['cal_text'] }}</div>
      <div style="margin-top:18px;background:#fff;border-radius:18px;padding:14px"><div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px"><span>{{ $s['protein_label'] }}</span><b>{{ $s['protein_value'] }}</b></div><div style="height:10px;border-radius:6px;background:#FFF3D6;overflow:hidden" role="img" aria-label="{{ $pct }}%"><i style="display:block;height:100%;width:{{ $pct }}%;background:#F59E0B;border-radius:6px"></i></div></div>
    </div>
    @endif
  </div>
  @endif
</section>
