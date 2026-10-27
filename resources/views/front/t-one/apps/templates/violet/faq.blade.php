<section id="sec-faq" style="max-width:860px;margin:0 auto;padding:0 32px 96px">
  <h2 style="margin:0 0 24px;font-size:clamp(26px,2.6vw,34px);font-weight:900">{{ $s['title'] }}</h2>
  @foreach((array) $s['items'] as $it)
  <details class="fq"><summary>{{ $it['q'] }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7A6C9B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></summary><p>{!! nl2br(e($it['a'])) !!}</p></details>
  @endforeach
</section>
