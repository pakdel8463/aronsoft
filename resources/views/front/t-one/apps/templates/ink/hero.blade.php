@php
    $tones = [
        'teal' => ['var(--t-soft)', '#075E68'],
        'indigo' => ['#E0E4FA', '#3A49A8'],
        'amber' => ['#FCEBCF', '#8A5410'],
    ];
    $pUrl = trim($s['primary_url'] ?? '') !== '' ? $s['primary_url'] : $page->primaryUrl();
    $sUrl = trim($s['secondary_url'] ?? '') !== '' ? $s['secondary_url'] : $page->primaryUrl();
    $logo = $page->logoUrl();
    $isImage = ($s['visual'] ?? 'builtin') === 'image' && trim($s['image'] ?? '') !== '';
    $frame = $s['frame'] ?? 'browser';
    $times = array_values($s['board_times'] ?? []);
    $tn = count($times);
    $tabs = array_values($s['board_tabs'] ?? []);
    $legend = array_values($s['board_legend'] ?? []) + ['', '', ''];
    $rows = $s['board_rows'] ?? [];
    $nowPos = $num($s['board_now_pos'] ?? '', 59.4);
    $grid = 'repeating-linear-gradient(to left,transparent 0,transparent calc(25% - 1px),#EEF2F4 calc(25% - 1px),#EEF2F4 25%)';
    $chk = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--t-accent)" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>';
@endphp
<div id="sec-hero">
<!-- header -->
<header style="display:flex;align-items:center;justify-content:space-between;gap:24px;padding:20px 32px;max-width:1200px;margin:0 auto;flex-wrap:wrap">
  <a href="#sec-hero" style="display:flex;align-items:center;gap:12px">
    @if($logo)
      <img src="{{ $logo }}" alt="" width="40" height="40" style="width:40px;height:40px;border-radius:12px;object-fit:contain">
    @else
      <span style="width:40px;height:40px;border-radius:12px;background:var(--t-ink);display:grid;place-items:center">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--t-bright)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      </span>
    @endif
    <span style="font-weight:800;font-size:21px">{{ $page->name }}</span>
    @if($page->name_en)<span style="font-size:13px;color:#5A6A75;direction:ltr">{{ $page->name_en }}</span>@endif
  </a>
  <nav aria-label="{{ $page->name }}" style="display:flex;gap:30px;font-size:15px;font-weight:500;color:#33444F;flex-wrap:wrap">
    @foreach($keys as $nk)
      @continue(in_array($nk, ['hero', 'cta'], true))
      @php $nl = trim((string) ($page->section($nk)['nav'] ?? '')); @endphp
      @if($nl !== '')<a href="#sec-{{ $nk }}">{{ $nl }}</a>@endif
    @endforeach
  </nav>
  @if(trim($s['primary_label'] ?? '') !== '')
  <a {{ $ln($pUrl) }} class="ink-btn" style="background:var(--t-ink);color:#fff;padding:12px 22px;border-radius:12px;font-size:14px;font-weight:700">{{ $s['primary_label'] }}</a>
  @endif
</header>

<!-- hero -->
<section style="max-width:1200px;margin:0 auto;padding:48px 32px 104px;display:flex;flex-wrap:wrap;gap:56px;align-items:center">
  <div style="flex:1 1 420px;max-width:500px">
    <h1 style="margin:0 0 22px;font-size:clamp(36px,4.3vw,58px);line-height:1.38;font-weight:800;letter-spacing:-0.01em">{!! nl2br(e($s['title'] ?? '')) !!}</h1>
    @if(trim($s['subtitle'] ?? '') !== '')
    <p style="margin:0 0 32px;font-size:18px;line-height:2;color:#43535E">{{ $s['subtitle'] }}</p>
    @endif
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      @if(trim($s['primary_label'] ?? '') !== '')
      <a {{ $ln($pUrl) }} class="ink-btn" style="background:var(--t-ink);color:#fff;padding:16px 28px;border-radius:14px;font-size:16px;font-weight:700;display:inline-flex;align-items:center;gap:10px">{{ $s['primary_label'] }}
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg>
      </a>
      @endif
      @if(trim($s['secondary_label'] ?? '') !== '')
      <a {{ $ln($sUrl) }} class="ink-ghost" style="border:1px solid #C9D5DA;padding:16px 26px;border-radius:14px;font-size:16px;font-weight:600">{{ $s['secondary_label'] }}</a>
      @endif
    </div>
    @if(!empty($s['bullets']))
    <div style="margin-top:28px;display:flex;flex-direction:column;gap:10px;font-size:15px;color:#43535E">
      @foreach($s['bullets'] as $b)
      <span style="display:flex;align-items:center;gap:10px">{!! $chk !!}{{ $b }}</span>
      @endforeach
    </div>
    @endif
  </div>

  @if($isImage)
  <div style="flex:1.5 1 560px;min-width:0;position:relative">
    @if($frame === 'phone')
      <div style="max-width:300px;margin:0 auto;background:var(--t-ink);border:10px solid var(--t-ink);border-radius:38px;box-shadow:0 40px 70px -40px rgba(12,26,38,.5);overflow:hidden">
        <img src="{{ \App\Support\ProductPageSchema::imageUrl($s['image']) }}" alt="{{ $page->name }}" style="display:block;width:100%;height:auto;border-radius:28px">
      </div>
    @elseif($frame === 'plain')
      <img src="{{ \App\Support\ProductPageSchema::imageUrl($s['image']) }}" alt="{{ $page->name }}" style="display:block;width:100%;height:auto;border:1px solid #D8E1E5;border-radius:22px;box-shadow:0 40px 70px -40px rgba(12,26,38,.35)">
    @else
      <div style="background:#fff;border:1px solid #D8E1E5;border-radius:22px;box-shadow:0 40px 70px -40px rgba(12,26,38,.35);overflow:hidden">
        <div style="display:flex;gap:6px;padding:14px 18px;background:#F8FAFB;border-bottom:1px solid #E6EDF0" aria-hidden="true">
          <i style="width:10px;height:10px;border-radius:50%;background:#D8E1E5;display:block"></i><i style="width:10px;height:10px;border-radius:50%;background:#D8E1E5;display:block"></i><i style="width:10px;height:10px;border-radius:50%;background:#D8E1E5;display:block"></i>
        </div>
        <img src="{{ \App\Support\ProductPageSchema::imageUrl($s['image']) }}" alt="{{ $page->name }}" style="display:block;width:100%;height:auto">
      </div>
    @endif
  </div>
  @else
  <!-- shift board -->
  <div style="flex:1.5 1 560px;min-width:0;position:relative">
    <div style="background:#fff;border:1px solid #D8E1E5;border-radius:22px;box-shadow:0 40px 70px -40px rgba(12,26,38,.35);overflow:hidden">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #E6EDF0;gap:12px;flex-wrap:wrap">
        <div>
          <div style="font-size:15px;font-weight:800">{{ $s['board_date'] ?? '' }}</div>
          <div style="font-size:12px;color:#5A6A75;margin-top:2px">{{ $s['board_sub'] ?? '' }}</div>
        </div>
        @if($tabs)
        <div style="display:flex;gap:6px;background:#F0F4F6;padding:4px;border-radius:10px;font-size:12px;font-weight:700">
          @foreach($tabs as $i => $t)
          @if($i === 0)<span style="background:var(--t-ink);color:#fff;padding:6px 14px;border-radius:7px">{{ $t }}</span>@else<span style="padding:6px 14px;color:#5A6A75">{{ $t }}</span>@endif
          @endforeach
        </div>
        @endif
      </div>

      @if($tn)
      <div style="padding:14px 20px 4px;display:flex">
        <div style="width:168px;flex:none"></div>
        <div style="flex:1;position:relative;height:18px;direction:rtl;font-size:11px;color:#6A7B86;font-feature-settings:'tnum'">
          @foreach($times as $i => $t)
            @if($i === 0)<span style="position:absolute;right:0">{{ $t }}</span>
            @elseif($i === $tn - 1)<span style="position:absolute;left:0">{{ $t }}</span>
            @else<span style="position:absolute;right:{{ round($i * 100 / max(1, $tn - 1), 2) }}%;transform:translateX(50%)">{{ $t }}</span>@endif
          @endforeach
        </div>
      </div>
      @endif

      <div style="position:relative;padding:0 20px 8px">
        <!-- now line -->
        @if(trim($s['board_now_label'] ?? '') !== '')
        <div style="position:absolute;top:0;bottom:0;left:20px;right:188px;pointer-events:none">
          <div style="position:absolute;top:0;bottom:0;right:{{ $nowPos }}%;width:2px;background:#E5484D"></div>
          <div style="position:absolute;top:-6px;right:{{ $nowPos }}%;transform:translateX(50%);background:#E5484D;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px">{{ $s['board_now_label'] }}</div>
        </div>
        @endif

        @foreach($rows as $r)
          @php
            $tc = $tones[$r['tone'] ?? 'teal'] ?? $tones['teal'];
            $st = $num($r['start'] ?? '');
            $ln_ = $num($r['len'] ?? '', 50);
            $pr = $num($r['present'] ?? '');
            $lt = $num($r['late'] ?? '');
            if ($lt > $pr) { $lt = $pr; }
          @endphp
        <div style="display:flex;align-items:center;height:54px;border-top:1px solid #EEF2F4">
          <div style="width:168px;flex:none;display:flex;align-items:center;gap:10px">
            <span style="width:30px;height:30px;border-radius:50%;background:{{ $tc[0] }};color:{{ $tc[1] }};display:grid;place-items:center;font-size:12px;font-weight:800">{{ mb_substr($r['name'] ?? '', 0, 1) }}</span>
            <span><span style="display:block;font-size:13px;font-weight:700">{{ $r['name'] ?? '' }}</span><span style="display:block;font-size:11px;color:#6A7B86">{{ $r['role'] ?? '' }}</span></span>
          </div>
          <div style="flex:1;position:relative;height:54px;background:{{ $grid }}">
            @if($pr > 0)
              <div style="position:absolute;top:10px;height:20px;right:{{ $st }}%;width:{{ $ln_ }}%;background:var(--t-soft);border-radius:6px"></div>
              @if($lt > 0)
                <div style="position:absolute;top:34px;height:7px;right:{{ $st }}%;width:{{ $lt }}%;background:#E0901F;border-radius:4px"></div>
                <div style="position:absolute;top:34px;height:7px;right:{{ round($st + $lt, 2) }}%;width:{{ round($pr - $lt, 2) }}%;background:var(--t-accent);border-radius:4px"></div>
                @if(trim($r['late_label'] ?? '') !== '')
                <span style="position:absolute;top:8px;right:{{ round($st + $ln_ + 1, 2) }}%;background:#FCEBCF;color:#8A5410;font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;white-space:nowrap">{{ $r['late_label'] }}</span>
                @endif
              @else
                <div style="position:absolute;top:34px;height:7px;right:{{ $st }}%;width:{{ $pr }}%;background:var(--t-accent);border-radius:4px"></div>
              @endif
            @else
              <div style="position:absolute;top:10px;height:20px;right:{{ $st }}%;width:{{ $ln_ }}%;border:1.5px dashed #E5484D;border-radius:6px;box-sizing:border-box"></div>
              @if(trim($r['state'] ?? '') !== '')
              <span style="position:absolute;top:34px;right:{{ $st }}%;background:#FDE4E4;color:#B42327;font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;white-space:nowrap">{{ $r['state'] }}</span>
              @endif
            @endif
          </div>
        </div>
        @endforeach
      </div>

      <div style="display:flex;gap:22px;padding:14px 20px;border-top:1px solid #E6EDF0;background:#F8FAFB;font-size:12px;font-weight:600;color:#43535E;flex-wrap:wrap">
        @if($legend[0] !== '')<span style="display:flex;align-items:center;gap:7px"><i style="width:10px;height:10px;border-radius:3px;background:var(--t-soft);display:block"></i>{{ $legend[0] }}</span>@endif
        @if($legend[1] !== '')<span style="display:flex;align-items:center;gap:7px"><i style="width:10px;height:4px;border-radius:2px;background:var(--t-accent);display:block"></i>{{ $legend[1] }}</span>@endif
        @if($legend[2] !== '')<span style="display:flex;align-items:center;gap:7px"><i style="width:10px;height:4px;border-radius:2px;background:#E0901F;display:block"></i>{{ $legend[2] }}</span>@endif
      </div>
    </div>

    @if(trim($s['toast_title'] ?? '') !== '' || trim($s['toast_text'] ?? '') !== '')
    <div style="position:absolute;left:-18px;bottom:-34px;background:var(--t-ink);color:#fff;border-radius:16px;padding:14px 18px;display:flex;gap:12px;align-items:center;box-shadow:0 20px 40px -16px rgba(12,26,38,.6);max-width:320px">
      <span style="width:34px;height:34px;border-radius:10px;background:#E0901F;display:grid;place-items:center;flex:none">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--t-ink)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3L4 14h7l-1 7 9-11h-7z"/></svg>
      </span>
      <span style="font-size:13px;line-height:1.8">@if(trim($s['toast_title'] ?? '') !== '')<b style="display:block;font-size:14px">{{ $s['toast_title'] }}</b>@endif{{ $s['toast_text'] ?? '' }}</span>
    </div>
    @endif
  </div>
  @endif
</section>
</div>
