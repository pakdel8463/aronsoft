<!-- rules -->
<section id="sec-rules" style="background:#fff;border-top:1px solid #D8E1E5;border-bottom:1px solid #D8E1E5">
  <div id="rules" style="max-width:1200px;margin:0 auto;padding:96px 32px;display:flex;flex-wrap:wrap;gap:64px;align-items:center">
    <div style="flex:1 1 340px;max-width:420px">
      @if(trim($s['title'] ?? '') !== '')<h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,38px);line-height:1.5;font-weight:800">{{ $s['title'] }}</h2>@endif
      @if(trim($s['text'] ?? '') !== '')<p style="margin:0;font-size:17px;line-height:2;color:#43535E">{{ $s['text'] }}</p>@endif
    </div>
    <div style="flex:1.6 1 520px;min-width:0;display:flex;flex-direction:column;gap:14px">
      @foreach($s['rules'] ?? [] as $r)
      <div style="background:#F3F6F7;border:1px solid #E1E9EC;border-radius:16px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:14px;color:#43535E">
          @if(trim($r['before'] ?? '') !== ''){{ $r['before'] }} @endif
          @if(trim($r['neutral'] ?? '') !== '')<span class="tok">{{ $r['neutral'] }}</span>@endif
          @if(trim($r['mid'] ?? '') !== ''){{ $r['mid'] }} @endif
          @if(trim($r['violet'] ?? '') !== '')<span class="tok tok-v">{{ $r['violet'] }}</span>@endif
          @if(trim($r['after'] ?? '') !== ''){{ $r['after'] }} @endif
          @if(!empty($r['actions']))
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6A7B86" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg>
          @foreach($r['actions'] as $a)<span class="tok tok-a">{{ $a }}</span>@endforeach
          @endif
        </div>
        @if(!empty($r['on']))
        <span style="width:40px;height:22px;border-radius:12px;background:var(--t-accent);position:relative;flex:none" aria-hidden="true"><i style="position:absolute;top:3px;right:3px;width:16px;height:16px;border-radius:50%;background:#fff;display:block"></i></span>
        @else
        <span style="width:40px;height:22px;border-radius:12px;background:#C3CFD5;position:relative;flex:none" aria-hidden="true"><i style="position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;display:block"></i></span>
        @endif
      </div>
      @endforeach
      @if(trim($s['add_label'] ?? '') !== '')
      <div style="border:1.5px dashed #C3CFD5;border-radius:16px;padding:16px 20px;font-size:14px;font-weight:600;color:#5A6A75;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        {{ $s['add_label'] }}
      </div>
      @endif
    </div>
  </div>
</section>
