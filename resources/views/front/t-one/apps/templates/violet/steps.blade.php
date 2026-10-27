<section id="sec-steps" style="max-width:1200px;margin:0 auto;padding:0 32px 104px">
  <h2 style="margin:0 0 40px;font-size:clamp(26px,2.8vw,36px);font-weight:900">{{ $s['title'] }}</h2>
  <div style="display:flex;gap:24px;flex-wrap:wrap">
    @foreach((array) $s['items'] as $i => $it)
    <div style="flex:1 1 260px"><span style="width:52px;height:52px;border-radius:18px;background:linear-gradient(145deg,#8B5CF6,#4C1BC4);color:#fff;display:grid;place-items:center;font-size:22px;font-weight:900;margin-bottom:18px">{{ strtr((string) ($i + 1), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) }}</span><b style="font-size:19px;display:block;margin-bottom:8px">{{ $it['title'] }}</b><span style="font-size:15px;line-height:2;color:#5B4E7C">{{ $it['text'] }}</span></div>
    @endforeach
  </div>
</section>
