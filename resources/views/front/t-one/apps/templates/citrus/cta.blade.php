@php
    $ext = fn ($u) => str_starts_with((string) $u, 'http') ? 'target="_blank" rel="noopener"' : '';
    $primaryUrl = $s['primary_url'] ?: ($page->link('android') ?: $page->primaryUrl());
    $secondaryUrl = $s['secondary_url'] ?: ($page->link('website') ?: $page->primaryUrl());
    $uid = 'cl-'.\Illuminate\Support\Str::random(5);
@endphp
<section id="lead" style="max-width:1200px;margin:0 auto;padding:0 32px 96px">
  <div class="c-cta" style="position:relative;overflow:hidden;border-radius:36px;padding:60px;background:var(--t-dark);color:#fff;display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between">
    <div style="position:absolute;top:-140px;left:-80px;width:380px;height:380px;border-radius:50%;background:radial-gradient(circle,rgba(182,242,60,.4),rgba(182,242,60,0) 68%)" aria-hidden="true"></div>
    <div style="position:relative;flex:1 1 360px;max-width:560px">
      <h2 class="hd" style="margin:0 0 12px;font-size:clamp(38px,4.4vw,58px);line-height:1.25">{!! nl2br(e($s['title'] ?: $page->name)) !!}</h2>
      @if($s['text'])<p style="margin:0;font-size:17px;line-height:2;color:#C9E6DA">{{ $s['text'] }}</p>@endif
    </div>
    <div style="position:relative;display:flex;gap:12px;flex-wrap:wrap">
      @if($s['primary_label'])<a href="{{ $primaryUrl }}" {!! $ext($primaryUrl) !!} class="lime" style="background:var(--t-accent);color:var(--t-dark);padding:17px 32px;border-radius:16px;font-size:16px;font-weight:800">{{ $s['primary_label'] }}</a>@endif
      @if($s['secondary_label'])<a href="{{ $secondaryUrl }}" {!! $ext($secondaryUrl) !!} class="ghost" style="border:1px solid rgba(255,255,255,.45);padding:17px 28px;border-radius:16px;font-size:16px;font-weight:600">{{ $s['secondary_label'] }}</a>@endif
    </div>
    @if($s['lead_form'])
    <div class="c-lead" style="position:relative;flex:1 1 100%;border-top:1px solid rgba(255,255,255,.18);padding-top:28px">
      <form action="{{ route('leads.store') }}" method="POST" data-ajax-form data-hide-on-success novalidate id="{{ $uid }}">
        @csrf
        <input type="hidden" name="page" value="products/{{ $page->slug }}">
        <input type="hidden" name="request_type" value="محصول: {{ $page->name }}">
        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div data-form-fields style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
          <div style="flex:1 1 260px;max-width:420px">
            <label for="{{ $uid }}-mobile" style="display:block;font-size:14px;font-weight:700;margin-bottom:8px;color:#C9E6DA">شماره موبایل</label>
            <input class="c-in" id="{{ $uid }}-mobile" type="tel" name="mobile" required dir="ltr" inputmode="tel" autocomplete="tel" placeholder="09123456789">
          </div>
          <button type="submit" class="lime" style="background:var(--t-accent);color:var(--t-dark);padding:17px 32px;border-radius:16px;font-size:16px;font-weight:800;border:0;cursor:pointer;font-family:inherit">{{ $s['lead_cta'] ?: 'درخواست تماس' }}</button>
        </div>
        <p data-form-msg class="form-msg" hidden aria-live="polite"></p>
      </form>
    </div>
    @endif
  </div>
</section>
