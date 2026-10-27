<section id="sec-audience" style="max-width:1200px;margin:0 auto;padding:0 32px 104px">
  <h2 style="margin:0 0 28px;font-size:clamp(26px,2.8vw,36px);font-weight:900">{{ $s['title'] }}</h2>
  <div style="border-top:1px solid #E6DEFA">
    @foreach((array) $s['items'] as $it)
    <div style="display:flex;gap:24px;align-items:baseline;padding:26px 0;border-bottom:1px solid #E6DEFA;flex-wrap:wrap"><b class="v-aud-n" style="font-size:24px;width:300px;color:var(--t-deep)">{{ $it['name'] }}</b><span style="font-size:16px;color:#5B4E7C;flex:1 1 280px">{{ $it['text'] }}</span></div>
    @endforeach
  </div>
</section>
