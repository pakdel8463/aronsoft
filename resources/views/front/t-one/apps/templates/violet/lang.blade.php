<section id="sec-lang" style="max-width:1200px;margin:0 auto;padding:104px 32px;display:flex;flex-wrap:wrap;gap:64px;align-items:center">
  <div style="flex:1.4 1 480px;min-width:0;display:flex;gap:20px;flex-wrap:wrap">
    @foreach((array) $s['cards'] as $c)
      @php $ltr = ($c['direction'] ?? 'rtl') === 'ltr'; @endphp
      <div @if($ltr)dir="ltr" @endif style="flex:1 1 220px;background:#fff;border:1px solid #E6DEFA;border-radius:24px;padding:24px;box-shadow:0 30px 50px -40px rgba(76,27,196,.6){{ $ltr ? ';text-align:left' : '' }}">
        @if($c['badge'] !== '')<span class="ch" style="margin-bottom:16px">{{ $c['badge'] }}</span>@endif
        <div style="font-size:13px;color:#7A6C9B">{{ $c['caption'] }}</div>
        <div style="margin-top:6px"><b style="font-size:26px;font-weight:900">{{ $c['value'] }}</b> <span style="font-size:13px;color:#7A6C9B">{{ $c['unit'] }}</span></div>
        @if($c['button'] !== '')<div style="margin-top:18px;background:linear-gradient(135deg,#6D28D9,#4C1BC4);color:#fff;text-align:center;padding:12px;border-radius:14px;font-weight:700;font-size:14px">{{ $c['button'] }}</div>@endif
      </div>
    @endforeach
  </div>
  <div style="flex:1 1 340px;max-width:430px">
    <h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,40px);line-height:1.5;font-weight:900">{{ $s['title'] }}</h2>
    @if($s['text'] !== '')<p style="margin:0;font-size:17px;line-height:2;color:#5B4E7C">{{ $s['text'] }}</p>@endif
  </div>
</section>
