@php
    $pct = fn ($v) => max(0, min(100, (float) strtr((string) $v, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٫'=>'.','٪'=>'','%'=>''])));
@endphp
<section id="sec-reports" style="background:var(--t-soft);border-radius:56px">
  <div style="max-width:1200px;margin:0 auto;padding:104px 32px">
    <div style="max-width:620px;margin-bottom:44px">
      <h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,40px);line-height:1.5;font-weight:900">{{ $s['title'] }}</h2>
      @if($s['text'] !== '')<p style="margin:0;font-size:17px;line-height:2;color:#5B4E7C">{{ $s['text'] }}</p>@endif
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:24px">
      <div style="flex:1.5 1 480px;min-width:0;background:#fff;border-radius:26px;padding:28px">
        <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:18px;flex-wrap:wrap;gap:8px"><b style="font-size:16px">{{ $s['card_title'] }}</b><span style="font-size:13px;color:#7A6C9B">{{ $s['card_period'] }}</span></div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:22px"><b style="font-size:40px;font-weight:900;color:var(--t-deep)">{{ $s['balance'] }}</b><span style="color:#7A6C9B">{{ $s['balance_unit'] }}</span></div>
        <div style="display:flex;gap:28px;flex-wrap:wrap">
          <span><span style="display:flex;align-items:center;gap:8px;font-size:13px;color:#5B4E7C"><i class="dt" style="background:#1FC9A0"></i>{{ $s['income_label'] }}</span><b style="font-size:20px">{{ $s['income'] }}</b></span>
          <span><span style="display:flex;align-items:center;gap:8px;font-size:13px;color:#5B4E7C"><i class="dt" style="background:#FF6B8A"></i>{{ $s['expense_label'] }}</span><b style="font-size:20px">{{ $s['expense'] }}</b></span>
        </div>
      </div>
      <div style="flex:1 1 360px;min-width:0;background:#fff;border-radius:26px;padding:28px">
        <b style="font-size:16px;display:block;margin-bottom:22px">{{ $s['cats_title'] }}</b>
        <div style="display:flex;flex-direction:column;gap:20px">
          @foreach((array) $s['cats'] as $c)
          <div><div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px"><span>{{ $c['name'] }}</span><b>{{ $c['amount'] }}</b></div><div class="bar"><i style="width:{{ $pct($c['percent']) }}%"></i></div></div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
