@php $fa = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); @endphp
<section id="sec-steps" style="max-width:1200px;margin:0 auto;padding:0 32px 104px">
  @if($s['title'])<h2 class="hd" style="margin:0 0 36px;font-size:clamp(34px,3.6vw,48px)">{{ $s['title'] }}</h2>@endif
  <div style="display:flex;gap:24px;flex-wrap:wrap">
    @foreach($s['items'] as $it)
    <div style="flex:1 1 260px;border-top:4px solid var(--t-ink);padding-top:18px"><b class="hd" style="font-size:34px;color:#16A34A">{{ $fa($loop->iteration) }}</b><b style="font-size:19px;display:block;margin:6px 0 8px">{{ $it['title'] }}</b><span style="font-size:15px;line-height:2;color:#3C5B52">{{ $it['text'] }}</span></div>
    @endforeach
  </div>
</section>
