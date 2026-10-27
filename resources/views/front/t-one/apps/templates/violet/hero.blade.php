@php
    $digits = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $num = fn ($v) => max(0.0, min(100.0, (float) strtr((string) $v, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٫'=>'.','٬'=>'',','=>'.','/'=>'.'])));
    $ext = fn ($u) => str_starts_with((string) $u, 'http') ? ' target="_blank" rel="noopener"' : '';
    $pUrl = $s['primary_url'] ?: $page->primaryUrl();
    $sUrl = $s['secondary_url'] ?: $page->primaryUrl();
    $logo = $page->logoUrl();
    $nav = [];
    foreach ($keys as $nk) {
        if (in_array($nk, ['hero', 'cta'], true)) { continue; }
        $lbl = trim((string) ($page->section($nk)['nav'] ?? ''));
        if ($lbl !== '') { $nav[] = [$nk, $lbl]; }
    }
    $badges = (array) ($s['badges'] ?? []);
    $bn = count($badges);
    $letter = $s['biz_letter'] !== '' ? $s['biz_letter'] : mb_substr((string) $s['biz_name'], 0, 1);
    $visual = $s['visual'] === 'image' ? 'image' : 'builtin';
    $img = \App\Support\ProductPageSchema::imageUrl($s['image'] ?? '');
    $chart = array_slice((array) ($s['chart'] ?? []), 0, 6);
@endphp
<div id="sec-hero" class="v-dark" style="background:radial-gradient(60% 70% at 15% 20%,rgba(166,107,255,.55),rgba(166,107,255,0) 70%),radial-gradient(50% 60% at 95% 100%,rgba(255,107,170,.28),rgba(255,107,170,0) 70%),linear-gradient(150deg,#1B0B45 0%,#33128F 52%,var(--t-accent) 100%);color:#fff;border-radius:0 0 56px 56px">
  <header style="display:flex;align-items:center;justify-content:space-between;gap:24px;padding:22px 32px;max-width:1200px;margin:0 auto;flex-wrap:wrap">
    <a href="#sec-hero" style="display:flex;align-items:center;gap:12px">
      @if($logo)
        <img src="{{ $logo }}" alt="" style="height:42px;width:auto;max-width:160px;object-fit:contain;border-radius:14px;display:block">
      @else
        <span style="width:42px;height:42px;border-radius:14px;background:linear-gradient(145deg,#fff,#E3D4FF);display:grid;place-items:center">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--t-accent)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 18V8l8 7 8-7v10"/></svg>
        </span>
      @endif
      <span style="font-weight:900;font-size:22px">{{ $page->name }}</span>
    </a>
    @if($nav)
    <nav aria-label="{{ $page->name }}" style="display:flex;gap:30px;font-size:15px;font-weight:500;color:#DCCFFF;flex-wrap:wrap">
      @foreach($nav as [$nk, $lbl])<a href="#sec-{{ $nk }}">{{ $lbl }}</a>@endforeach
    </nav>
    @endif
    @if($s['primary_label'] !== '')
    <a href="{{ $pUrl }}"{!! $ext($pUrl) !!} style="background:#fff;color:var(--t-deep);padding:12px 22px;border-radius:14px;font-size:14px;font-weight:800">{{ $s['primary_label'] }}</a>
    @endif
  </header>

  <section style="max-width:1200px;margin:0 auto;padding:40px 32px 96px;display:flex;flex-wrap:wrap;gap:40px;align-items:center">
    <div style="flex:1 1 420px;max-width:500px">
      <h1 style="margin:0 0 22px;font-size:clamp(40px,5vw,68px);line-height:1.3;font-weight:900;letter-spacing:-0.01em">{!! nl2br(e($s['title'])) !!}</h1>
      @if($s['subtitle'] !== '')<p style="margin:0 0 34px;font-size:18px;line-height:2;color:#E5DBFF">{{ $s['subtitle'] }}</p>@endif
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        @if($s['primary_label'] !== '')<a href="{{ $pUrl }}"{!! $ext($pUrl) !!} style="background:#fff;color:var(--t-deep);padding:17px 30px;border-radius:16px;font-size:16px;font-weight:800;box-shadow:0 18px 30px -14px rgba(0,0,0,.45)">{{ $s['primary_label'] }}</a>@endif
        @if($s['secondary_label'] !== '')<a href="{{ $sUrl }}"{!! $ext($sUrl) !!} class="bg" style="border:1px solid rgba(255,255,255,.4);padding:17px 28px;border-radius:16px;font-size:16px;font-weight:600">{{ $s['secondary_label'] }}</a>@endif
      </div>
      @if($bn)
      <div style="margin-top:34px;display:flex;gap:0;font-size:14px;color:#D4C5FF;flex-wrap:wrap">
        @foreach($badges as $i => $b)
          @php
            $st = $bn === 1 ? '' : ($i === 0 ? 'padding-inline-end:18px' : ($i === $bn - 1 ? 'padding-inline-start:18px;border-inline-start:1px solid rgba(255,255,255,.28)' : 'padding:0 18px;border-inline-start:1px solid rgba(255,255,255,.28)'));
          @endphp
          <span style="{{ $st }}">{{ $b }}</span>
        @endforeach
      </div>
      @endif
    </div>

@if($visual === 'image')
  @if($img)
    <div class="v-hero-vis v-img">
      @if($s['frame'] === 'phone')
        <div style="width:296px;max-width:100%;background:#0E0724;border-radius:46px;padding:9px;box-shadow:0 50px 90px -30px rgba(8,0,36,.85)">
          <img src="{{ $img }}" alt="{{ $s['image_alt'] }}" style="display:block;width:100%;height:auto;border-radius:38px">
        </div>
      @elseif($s['frame'] === 'browser')
        <div style="width:100%;max-width:640px;background:#fff;border-radius:22px;box-shadow:0 40px 80px -30px rgba(10,0,40,.7);overflow:hidden;color:var(--t-ink)">
          <div style="display:flex;align-items:center;gap:7px;padding:12px 16px;background:#F5F0FF;border-bottom:1px solid #E8DFFB"><i style="width:9px;height:9px;border-radius:50%;background:#D5C6F5;display:block"></i><i style="width:9px;height:9px;border-radius:50%;background:#D5C6F5;display:block"></i><i style="width:9px;height:9px;border-radius:50%;background:#D5C6F5;display:block"></i></div>
          <img src="{{ $img }}" alt="{{ $s['image_alt'] }}" style="display:block;width:100%;height:auto">
        </div>
      @else
        <img src="{{ $img }}" alt="{{ $s['image_alt'] }}" style="display:block;width:100%;max-width:640px;height:auto;border-radius:26px;box-shadow:0 40px 80px -30px rgba(10,0,40,.7)">
      @endif
    </div>
  @endif
@else
    <!-- phone + dashboard -->
    <div class="v-hero-vis">
      <!-- browser -->
      <div class="v-browser" style="position:absolute;left:0;top:96px;width:calc(100% - 190px);min-width:340px;background:#fff;border-radius:22px;box-shadow:0 40px 80px -30px rgba(10,0,40,.7);overflow:hidden;color:var(--t-ink)">
        <div style="display:flex;align-items:center;gap:7px;padding:12px 16px;background:#F5F0FF;border-bottom:1px solid #E8DFFB"><i style="width:9px;height:9px;border-radius:50%;background:#D5C6F5;display:block"></i><i style="width:9px;height:9px;border-radius:50%;background:#D5C6F5;display:block"></i><i style="width:9px;height:9px;border-radius:50%;background:#D5C6F5;display:block"></i></div>
        <div style="padding:22px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px"><b style="font-size:15px">{{ $s['chart_title'] }}</b>@if($s['chart_unit'] !== '')<span class="ch">{{ $s['chart_unit'] }}</span>@endif</div>
          <svg viewBox="0 0 520 226" style="width:100%;height:auto;display:block;font-family:Vazirmatn,sans-serif" role="img" aria-label="{{ $s['chart_alt'] ?: $s['chart_title'] }}">
            <g stroke="#EEE8FB" stroke-width="1"><path d="M0 190H520M0 130H520M0 70H520M0 10H520"/></g>
            <g fill="#1FC9A0">@foreach($chart as $i => $c)@php $h = (int) round($num($c['income']) * 1.6); @endphp<rect x="{{ 458 - 84 * $i }}" y="{{ 190 - $h }}" width="18" height="{{ $h }}" rx="5"/>@endforeach</g>
            <g fill="#FF6B8A">@foreach($chart as $i => $c)@php $h = (int) round($num($c['expense']) * 1.6); @endphp<rect x="{{ 484 - 84 * $i }}" y="{{ 190 - $h }}" width="18" height="{{ $h }}" rx="5"/>@endforeach</g>
            <g font-size="11" fill="#7A6C9B" text-anchor="middle">@foreach($chart as $i => $c)<text x="{{ 480 - 84 * $i }}" y="212">{{ $c['label'] }}</text>@endforeach</g>
          </svg>
          <div style="display:flex;gap:18px;margin-top:8px;font-size:12px;font-weight:600;color:#5B4E7C">
            <span style="display:flex;align-items:center;gap:7px"><i class="dt" style="background:#1FC9A0"></i>{{ $s['legend_income'] }}</span>
            <span style="display:flex;align-items:center;gap:7px"><i class="dt" style="background:#FF6B8A"></i>{{ $s['legend_expense'] }}</span>
          </div>
        </div>
      </div>

      <!-- phone -->
      <div class="v-phone" style="position:absolute;right:0;top:0;width:296px;height:610px;background:#0E0724;border-radius:46px;padding:9px;box-shadow:0 50px 90px -30px rgba(8,0,36,.85);box-sizing:border-box">
        <div style="width:100%;height:100%;background:var(--t-soft);border-radius:38px;overflow:hidden;color:var(--t-ink);display:flex;flex-direction:column">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:24px 20px 12px">
            <span><span style="display:block;font-size:12px;color:#7A6C9B">{{ $s['biz_label'] }}</span><b style="font-size:16px">{{ $s['biz_name'] }}</b></span>
            <span style="width:36px;height:36px;border-radius:50%;background:#E3D4FF;color:var(--t-deep);display:grid;place-items:center;font-weight:800;font-size:14px">{{ $letter }}</span>
          </div>
          <div style="margin:0 16px;border-radius:24px;padding:20px;color:#fff;position:relative;overflow:hidden;background:linear-gradient(140deg,#7A3CF0 0%,#4C1BC4 55%,#2B0F6B 100%);box-shadow:0 24px 36px -20px rgba(76,27,196,.8)">
            <div style="position:absolute;top:-60px;left:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.32),rgba(255,255,255,0) 70%)"></div>
            <div style="position:relative">
              <div style="font-size:12px;color:#DCCBFF">{{ $s['balance_label'] }}</div>
              <div style="margin:6px 0 16px;display:flex;align-items:baseline;gap:6px"><b style="font-size:30px;font-weight:900">{{ $s['balance'] }}</b><span style="font-size:13px;color:#DCCBFF">{{ $s['balance_unit'] }}</span></div>
              <div style="display:flex;gap:8px">
                <span style="flex:1;background:rgba(255,255,255,.16);border-radius:12px;padding:8px 10px;font-size:11px"><span style="color:#B8F5E4;display:block">{{ $s['income_label'] }}</span><b style="font-size:13px">{{ $s['income'] }}</b></span>
                <span style="flex:1;background:rgba(255,255,255,.16);border-radius:12px;padding:8px 10px;font-size:11px"><span style="color:#FFC2D0;display:block">{{ $s['expense_label'] }}</span><b style="font-size:13px">{{ $s['expense'] }}</b></span>
              </div>
            </div>
          </div>
          <div style="padding:16px 20px 0;display:flex;justify-content:space-between;align-items:center"><b style="font-size:14px">{{ $s['recent_title'] }}</b><span style="font-size:12px;color:#7A5FD0;font-weight:700">{{ $s['recent_all'] }}</span></div>
          <div style="padding:0 20px;flex:1;overflow:hidden">
            @foreach(array_slice((array) $s["txs"], 0, 3) as $t)
              @php $in = ($t['kind'] ?? 'in') === 'in'; @endphp
              <div class="tx"><span class="ic" style="background:{{ $in ? '#DDF8F0' : '#FFE3EA' }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $in ? '#12A383' : '#E5456B' }}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">@if($in)<path d="M12 19V5"/><path d="M6 11l6-6 6 6"/>@else<path d="M12 5v14"/><path d="M6 13l6 6 6-6"/>@endif</svg></span><span style="flex:1"><b style="display:block;font-size:13px">{{ $t['title'] }}</b><span style="font-size:11px;color:#7A6C9B">{{ $t['when'] }}</span></span><b style="font-size:13px;color:{{ $in ? '#12A383' : '#E5456B' }}">{{ $in ? '+' : '−' }}{{ $t['amount'] }}</b></div>
            @endforeach
          </div>
          <div style="display:flex;justify-content:space-around;align-items:center;padding:12px 16px 16px;background:#fff;border-top:1px solid #EEE8FB">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6D28D9" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11l8-7 8 7v9H4z"/></svg>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#A99BCB" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 20V10M12 20V4M19 20v-7"/></svg>
            <span style="width:46px;height:46px;border-radius:16px;background:linear-gradient(145deg,#8B5CF6,var(--t-accent));display:grid;place-items:center;margin-top:-26px;box-shadow:0 12px 20px -8px rgba(91,33,201,.8)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#A99BCB" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#A99BCB" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/></svg>
          </div>
        </div>
      </div>
    </div>
@endif
  </section>
</div>
