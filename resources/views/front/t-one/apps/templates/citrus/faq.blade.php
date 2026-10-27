<section id="sec-faq" style="max-width:860px;margin:0 auto;padding:0 32px 96px">
  @if($s['title'])<h2 class="hd" style="margin:0 0 20px;font-size:clamp(34px,3.6vw,48px)">{{ $s['title'] }}</h2>@endif
  @foreach($s['items'] as $it)
  <details class="fq"><summary>{{ $it['q'] }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--t-ink)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex:none"><path d="M6 9l6 6 6-6"/></svg></summary><p>{{ $it['a'] }}</p></details>
  @endforeach
</section>
