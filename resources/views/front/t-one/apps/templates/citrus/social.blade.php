@php
    $tones = [
        'teal' => ['card' => '#fff', 'tile' => '#DDF4F1', 'stroke' => '#0D9488', 'text' => '#3C5B52'],
        'amber' => ['card' => '#FFF1CC', 'tile' => '#fff', 'stroke' => '#B26B00', 'text' => '#5C4510'],
        'lime' => ['card' => '#EAF8D6', 'tile' => '#fff', 'stroke' => '#16A34A', 'text' => '#2F5F0C'],
    ];
    $svg = [
        'ph-users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 5a3.5 3.5 0 010 6.5M18 14.8c2 .6 3.2 2.3 3.5 5.2"/>',
        'ph-coins' => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v9M9.5 10c0-1 1-1.8 2.5-1.8s2.5.8 2.5 1.8-1 1.5-2.5 2-2.5 1-2.5 2 1 1.8 2.5 1.8 2.5-.8 2.5-1.8"/>',
        'ph-chart-bar' => '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
    ];
@endphp
<section id="sec-social" style="max-width:1200px;margin:0 auto;padding:104px 32px">
  @if($s['title'])<h2 class="hd" style="margin:0 0 36px;font-size:clamp(38px,4.4vw,58px);line-height:1.25;max-width:720px">{!! nl2br(e($s['title'])) !!}</h2>@endif
  <div style="display:flex;gap:20px;flex-wrap:wrap">
    @foreach($s['items'] as $it)@php $t = $tones[$it['tone']] ?? $tones['teal']; @endphp
    <div style="flex:1 1 300px;background:{{ $t['card'] }};border:2px solid var(--t-ink);border-radius:28px;padding:28px">
      @if($it['icon'])
      <span style="width:50px;height:50px;border-radius:16px;background:{{ $t['tile'] }};display:grid;place-items:center;margin-bottom:18px">
        @if(isset($svg[$it['icon']]))<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $t['stroke'] }}" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $svg[$it['icon']] !!}</svg>
        @else<i class="ph {{ $it['icon'] }}" style="font-size:24px;line-height:1;color:{{ $t['stroke'] }}" aria-hidden="true"></i>@endif
      </span>
      @endif
      <b class="hd" style="font-size:28px;display:block;margin-bottom:8px">{{ $it['title'] }}</b>
      <span style="font-size:15px;line-height:2;color:{{ $t['text'] }}">{{ $it['text'] }}</span>
    </div>
    @endforeach
  </div>
</section>
