@php
    $ext = fn ($u) => str_starts_with((string) $u, 'http') ? 'target="_blank" rel="noopener"' : '';
    $primaryUrl = $s['primary_url'] ?: ($page->link('android') ?: $page->primaryUrl());
    $secondaryUrl = $s['secondary_url'] ?: '#sec-diet';
    $logo = $page->logoUrl();
    $nav = [];
    foreach ($keys as $nk) {
        if ($nk === 'hero') { continue; }
        $label = trim((string) ($page->section($nk)['nav'] ?? ''));
        if ($label !== '') { $nav[] = ['#sec-'.$nk, $label]; }
    }
    $fa = fn ($v) => strtr((string) $v, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $num = fn ($v) => (int) preg_replace('/\D+/', '', strtr((string) $v, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']));
    $money = fn ($n) => $fa(number_format($n, 0, '', '٬'));
    $consumed = $num($s['ring_consumed']); $goal = $num($s['ring_goal']);
    $ratio = $goal > 0 ? max(0, min(1, $consumed / $goal)) : 0;
    $offset = (int) ceil(264 * (1 - $ratio));
    $tones = [
        'lime' => ['bg' => '#B6F23C', 'tile' => '#EAF8D6', 'lab' => '#4A6B30', 'stroke' => '#3E7A12'],
        'amber' => ['bg' => '#FFD27A', 'tile' => '#FFF1DD', 'lab' => '#8A5A1E', 'stroke' => '#B26B00'],
        'teal' => ['bg' => '#BFE9E4', 'tile' => '#DDF4F1', 'lab' => '#1F6B63', 'stroke' => '#0D9488'],
    ];
    $tn = fn ($t) => $tones[$t] ?? $tones['lime'];
    $initial = fn ($n) => mb_substr(trim((string) $n), 0, 1);
@endphp
@php
    $heroImg = \App\Support\ProductPageSchema::imageUrl($s['image'] ?? '');
    $useImg = ($s['visual'] ?? 'builtin') === 'image' && $heroImg;
    $frame = $s['frame'] ?: 'phone';
@endphp
<div id="sec-hero" style="background:var(--t-dark);color:#fff;border-radius:0 0 60px 60px;position:relative;overflow:hidden">
  <div style="position:absolute;top:-180px;right:-120px;width:560px;height:560px;border-radius:50%;background:radial-gradient(circle,rgba(182,242,60,.38),rgba(182,242,60,0) 68%)" aria-hidden="true"></div>
  <div style="position:absolute;bottom:-220px;left:-100px;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.4),rgba(20,184,166,0) 68%)" aria-hidden="true"></div>
  <header style="position:relative;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:22px 32px;max-width:1200px;margin:0 auto;flex-wrap:wrap">
    <a href="#sec-hero" style="display:flex;align-items:center;gap:10px" aria-label="{{ $page->name }}">
      @if($logo)
        <img src="{{ $logo }}" alt="" style="height:42px;width:auto;max-width:120px;object-fit:contain">
      @else
        <span style="width:42px;height:42px;border-radius:14px;background:var(--t-accent);display:grid;place-items:center">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--t-dark)" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h3M18 12h3M6 8v8M18 8v8M9 10v4M15 10v4M9 12h6"/></svg>
        </span>
      @endif
      <span class="hd" style="font-size:30px;line-height:1">{{ $page->name }}</span>
    </a>
    @if($nav)
    <nav aria-label="منوی صفحه" style="display:flex;gap:30px;font-size:15px;font-weight:500;color:#C9E6DA;flex-wrap:wrap">
      @foreach($nav as [$href, $label])<a href="{{ $href }}">{{ $label }}</a>@endforeach
    </nav>
    @endif
    @if($s['header_label'])
    <a href="{{ $primaryUrl }}" {!! $ext($primaryUrl) !!} class="lime" style="background:var(--t-accent);color:var(--t-dark);padding:12px 22px;border-radius:14px;font-size:14px;font-weight:800">{{ $s['header_label'] }}</a>
    @endif
  </header>

  <section class="c-hero-sec" style="position:relative;max-width:1200px;margin:0 auto;padding:36px 32px 100px;display:flex;flex-wrap:wrap;gap:40px;align-items:center">
    <div style="flex:1 1 420px;max-width:520px">
      <h1 class="hd" style="margin:0 0 20px;font-size:clamp(52px,7vw,96px);line-height:1.15;overflow-wrap:anywhere">{!! nl2br(e($s['title'] ?: $page->name)) !!}</h1>
      @if($s['subtitle'])<p style="margin:0 0 32px;font-size:18px;line-height:2;color:#C9E6DA">{{ $s['subtitle'] }}</p>@endif
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        @if($s['primary_label'])<a href="{{ $primaryUrl }}" {!! $ext($primaryUrl) !!} class="lime" style="background:var(--t-accent);color:var(--t-dark);padding:17px 30px;border-radius:16px;font-size:16px;font-weight:800">{{ $s['primary_label'] }}</a>@endif
        @if($s['secondary_label'])<a href="{{ $secondaryUrl }}" {!! $ext($secondaryUrl) !!} class="ghost" style="border:1px solid rgba(255,255,255,.4);padding:17px 28px;border-radius:16px;font-size:16px;font-weight:600">{{ $s['secondary_label'] }}</a>@endif
      </div>
    </div>

@if($useImg)
    <div style="flex:1.2 1 520px;position:relative;min-width:0;display:flex;justify-content:center">
      @if($frame === 'phone')
        <div style="width:300px;max-width:100%;background:#031A15;border-radius:46px;padding:9px;box-shadow:0 50px 90px -30px rgba(0,0,0,.8);transform:rotate(3deg)"><img src="{{ $heroImg }}" alt="{{ $s['image_alt'] }}" style="width:100%;height:auto;border-radius:38px;display:block"></div>
      @elseif($frame === 'browser')
        <div style="width:100%;max-width:640px;background:#031A15;border-radius:20px;overflow:hidden;box-shadow:0 50px 90px -30px rgba(0,0,0,.8)"><div style="display:flex;gap:6px;padding:12px 16px" aria-hidden="true"><i style="width:10px;height:10px;border-radius:50%;background:#B6F23C;display:block"></i><i style="width:10px;height:10px;border-radius:50%;background:#FFD27A;display:block"></i><i style="width:10px;height:10px;border-radius:50%;background:#14B8A6;display:block"></i></div><img src="{{ $heroImg }}" alt="{{ $s['image_alt'] }}" style="width:100%;height:auto;display:block"></div>
      @else
        <img src="{{ $heroImg }}" alt="{{ $s['image_alt'] }}" style="display:block;max-width:100%;height:auto;border-radius:28px;box-shadow:0 50px 90px -30px rgba(0,0,0,.8)">
      @endif
    </div>
@else
    <!-- two phones -->
    <div class="c-vis" style="flex:1.2 1 520px;position:relative;height:640px;min-width:0">
      <!-- back phone: feed -->
      <div class="c-ph1" style="position:absolute;left:0;top:50px;width:272px;height:560px;background:#031A15;border-radius:42px;padding:8px;box-sizing:border-box;box-shadow:0 40px 70px -30px rgba(0,0,0,.7);transform:rotate(-5deg)" role="img" aria-label="{{ $s['feed_title'] }}">
        <div style="width:100%;height:100%;background:var(--t-paper);border-radius:35px;overflow:hidden;color:var(--t-ink);padding:22px 16px;box-sizing:border-box">
          @if($s['feed_title'])<b style="font-size:15px;display:block;margin-bottom:12px">{{ $s['feed_title'] }}</b>@endif
          @foreach($s['posts'] as $p)
          <div style="background:#fff;border-radius:20px;padding:12px;@if(!$loop->last)margin-bottom:12px;@endif box-shadow:0 10px 20px -14px rgba(6,47,38,.5)">
            <div style="display:flex;align-items:center;gap:8px;@if($p['route'])margin-bottom:10px @endif"><span style="width:30px;height:30px;border-radius:50%;background:{{ $tn($p['tone'])['bg'] }};display:grid;place-items:center;font-weight:800;font-size:12px">{{ $initial($p['name']) }}</span><span><b style="font-size:12px;display:block">{{ $p['name'] }}</b><span style="font-size:10px;color:#6B8A80">{{ $p['sub'] }}</span></span></div>
            @if($p['route'])
            <div style="height:104px;border-radius:14px;background:linear-gradient(135deg,#14B8A6,#0D9488 55%,#065F55);position:relative;overflow:hidden"><svg viewBox="0 0 220 104" width="100%" height="104" aria-hidden="true" style="display:block"><path d="M10 80C40 80 50 30 80 40S120 90 150 56 190 20 212 28" fill="none" stroke="var(--t-accent)" stroke-width="4" stroke-linecap="round"/></svg></div>
            @if($p['stat1'] || $p['stat2'])<div style="display:flex;gap:14px;margin-top:10px;font-size:11px;color:#3C5B52">@if($p['stat1'])<span>{{ $p['stat1'] }}</span>@endif @if($p['stat2'])<span>{{ $p['stat2'] }}</span>@endif</div>@endif
            @endif
            @if($p['text'])<div style="margin-top:10px;font-size:12px;line-height:1.9;color:#3C5B52">{{ $p['text'] }}</div>@endif
          </div>
          @endforeach
        </div>
      </div>

      <!-- front phone: today -->
      <div class="c-ph2" style="position:absolute;right:10px;top:0;width:300px;height:612px;background:#031A15;border-radius:46px;padding:9px;box-sizing:border-box;box-shadow:0 50px 90px -30px rgba(0,0,0,.8);transform:rotate(3deg)" role="img" aria-label="{{ $s['today_title'] }}">
        <div style="width:100%;height:100%;background:#fff;border-radius:38px;overflow:hidden;color:var(--t-ink);display:flex;flex-direction:column">
          <div style="padding:24px 20px 6px;display:flex;justify-content:space-between;align-items:center"><span><span style="display:block;font-size:12px;color:#6B8A80">{{ $s['today_kicker'] }}</span><b style="font-size:17px">{{ $s['today_title'] }}</b></span>@if($s['coins'])<span class="pill" style="background:#FFF1CC;color:#8A5A00"><svg width="13" height="13" viewBox="0 0 24 24" fill="#F5A524" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>{{ $s['coins'] }}</span>@endif</div>
          <div style="margin:10px 16px;border-radius:26px;padding:18px;background:var(--t-dark);color:#fff;display:flex;align-items:center;gap:16px">
            <svg width="104" height="104" viewBox="0 0 104 104" aria-hidden="true" style="flex:none"><circle cx="52" cy="52" r="42" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="11"/><circle class="ring" cx="52" cy="52" r="42" fill="none" stroke="var(--t-accent)" stroke-width="11" stroke-linecap="round" stroke-dasharray="264" stroke-dashoffset="{{ $offset }}"/></svg>
            <span><b class="hd" style="font-size:34px;line-height:1;display:block">{{ $money($consumed) }}</b><span style="font-size:12px;color:#C9E6DA">از {{ $money($goal) }} {{ $s['ring_unit'] }}</span></span>
          </div>
          <div style="display:flex;gap:8px;padding:0 16px">
            @foreach($s['macros'] as $m)@php $mt = $tn($m['tone']); @endphp
            <span style="flex:1;background:{{ $mt['tile'] }};border-radius:16px;padding:10px;font-size:11px"><span style="color:{{ $mt['lab'] }};display:block">{{ $m['label'] }}</span><b style="font-size:15px">{{ $m['value'] }}</b></span>
            @endforeach
          </div>
          <div style="padding:14px 20px 0;flex:1">
            @if($s['meals_title'])<b style="font-size:13px;display:block;margin-bottom:4px">{{ $s['meals_title'] }}</b>@endif
            @foreach($s['meals'] as $m)@php $mt = $tn($m['tone']); @endphp
            <div class="row"><span style="width:34px;height:34px;border-radius:12px;background:{{ $mt['tile'] }};display:grid;place-items:center;flex:none"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $mt['stroke'] }}" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="8"/></svg></span><span style="flex:1"><b style="font-size:13px;display:block">{{ $m['title'] }}</b><span style="font-size:11px;color:#6B8A80">{{ $m['sub'] }}</span></span><b style="font-size:12px">{{ $m['kcal'] }}</b></div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
@endif
  </section>
</div>
