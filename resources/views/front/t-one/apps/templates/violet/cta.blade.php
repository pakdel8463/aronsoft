@php
    $ext = fn ($u) => str_starts_with((string) $u, 'http') ? ' target="_blank" rel="noopener"' : '';
    $pUrl = $s['primary_url'] ?: $page->primaryUrl();
    $sUrl = $s['secondary_url'] ?: ($page->link('android') ?: $page->primaryUrl());
    $form = ! empty($s['lead_form']);
    $uid = 'vlf-'.\Illuminate\Support\Str::random(5);
@endphp
<section id="lead" style="max-width:1200px;margin:0 auto;padding:0 32px 96px">
  <div id="sec-cta" class="v-cta v-dark" style="position:relative;overflow:hidden;border-radius:32px;padding:60px;color:#fff;background:radial-gradient(60% 90% at 100% 0%,rgba(255,255,255,.2),rgba(255,255,255,0) 60%),linear-gradient(140deg,#2B0F6B,var(--t-accent));display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between">
    <div style="flex:1 1 360px;max-width:560px">
      <h2 style="margin:0 0 14px;font-size:clamp(28px,3vw,40px);line-height:1.5;font-weight:900">{{ $s['title'] }}</h2>
      @if($s['text'] !== '')<p style="margin:0;font-size:17px;line-height:2;color:#E0D4FF">{{ $s['text'] }}</p>@endif
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      @if($s['primary_label'] !== '')<a href="{{ $pUrl }}"{!! $ext($pUrl) !!} style="background:#fff;color:var(--t-deep);padding:17px 32px;border-radius:16px;font-size:16px;font-weight:800">{{ $s['primary_label'] }}</a>@endif
      @if($s['secondary_label'] !== '')<a href="{{ $sUrl }}"{!! $ext($sUrl) !!} class="bg" style="border:1px solid rgba(255,255,255,.45);padding:17px 28px;border-radius:16px;font-size:16px;font-weight:600">{{ $s['secondary_label'] }}</a>@endif
    </div>
    @if($form)
    <form action="{{ route('leads.store') }}" method="POST" data-ajax-form data-hide-on-success novalidate id="{{ $uid }}" style="flex:1 1 100%;margin-top:4px;padding-top:28px;border-top:1px solid rgba(255,255,255,.22)">
      @csrf
      <input type="hidden" name="page" value="products/{{ $page->slug }}">
      <input type="hidden" name="request_type" value="محصول: {{ $page->name }}">
      <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div data-form-fields>
        @if($s['form_title'] !== '')<p style="margin:0 0 14px;font-size:17px;font-weight:700">{{ $s['form_title'] }}</p>@endif
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
          <div style="flex:1 1 240px;max-width:360px">
            <label for="{{ $uid }}-mobile" style="display:block;font-size:13px;font-weight:600;color:#E0D4FF;margin-bottom:6px">شماره موبایل</label>
            <input id="{{ $uid }}-mobile" type="tel" name="mobile" required dir="ltr" inputmode="tel" autocomplete="tel" placeholder="09123456789" style="display:block;width:100%;box-sizing:border-box;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.12);color:#fff;border-radius:14px;padding:15px 18px;font:inherit;font-size:16px;text-align:left">
          </div>
          <button type="submit" class="bw" style="border:0;cursor:pointer;background:#fff;color:var(--t-deep);padding:17px 32px;border-radius:16px;font:inherit;font-size:16px;font-weight:800">{{ $s['form_button'] !== '' ? $s['form_button'] : 'ارسال' }}</button>
        </div>
      </div>
      <p data-form-msg class="form-msg" hidden aria-live="polite" style="margin:14px 0 0"></p>
    </form>
    <style>#{{ $uid }} input::placeholder{color:rgba(255,255,255,.55)}#{{ $uid }} .form-msg{color:#1A1033;background:#fff}</style>
    @endif
  </div>
</section>
