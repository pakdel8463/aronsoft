@php
    $uid = 'ink-'.\Illuminate\Support\Str::random(5);
    $cu = trim($s['primary_url'] ?? '') !== '' ? $s['primary_url'] : $page->primaryUrl();
    $label = trim($s['primary_label'] ?? '') !== '' ? $s['primary_label'] : 'درخواست تماس';
@endphp
<!-- cta -->
<section id="sec-cta" style="max-width:1200px;margin:0 auto;padding:0 32px 96px">
  <div id="lead" class="ink-dark" style="background:var(--t-ink);color:#fff;border-radius:28px;padding:56px;display:flex;flex-wrap:wrap;gap:40px;align-items:center;justify-content:space-between">
    <div style="flex:1 1 360px;max-width:520px">
      @if(trim($s['title'] ?? '') !== '')<h2 style="margin:0 0 14px;font-size:clamp(26px,2.8vw,36px);line-height:1.5;font-weight:800">{{ $s['title'] }}</h2>@endif
      @if(trim($s['text'] ?? '') !== '')<p style="margin:0;font-size:16px;line-height:2;color:#B5C4CD">{{ $s['text'] }}</p>@endif
    </div>
    @if(!empty($s['lead_form']))
    <form action="{{ route('leads.store') }}" method="POST" data-ajax-form data-hide-on-success novalidate class="ink-lead" id="{{ $uid }}" style="flex:1 1 360px;max-width:460px;display:flex;gap:10px;flex-wrap:wrap;position:relative">
      @csrf
      <input type="hidden" name="page" value="products/{{ $page->slug }}">
      <input type="hidden" name="request_type" value="محصول: {{ $page->name }}">
      <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div data-form-fields style="flex:1 1 100%;display:flex;gap:10px;flex-wrap:wrap;position:relative">
        <label for="{{ $uid }}-phone" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">{{ trim($s['input_label'] ?? '') !== '' ? $s['input_label'] : 'شماره موبایل' }}</label>
        <input id="{{ $uid }}-phone" type="tel" name="mobile" required dir="ltr" inputmode="tel" autocomplete="tel" placeholder="{{ $s['placeholder'] ?? '' }}" style="flex:1 1 200px;min-width:0;height:54px;border-radius:14px;border:1px solid #33505F;background:#12283A;color:#fff;padding:0 18px;font-family:inherit;font-size:16px;direction:ltr;text-align:right">
        <button type="submit" style="height:54px;padding:0 26px;border-radius:14px;border:0;background:var(--t-bright);color:var(--t-ink);font-family:inherit;font-size:16px;font-weight:800;cursor:pointer">{{ $label }}</button>
      </div>
      <p data-form-msg class="form-msg" hidden aria-live="polite"></p>
    </form>
    @else
    <div style="flex:1 1 360px;max-width:460px;display:flex;gap:10px;flex-wrap:wrap">
      <a {{ $ln($cu) }} style="display:inline-flex;align-items:center;height:54px;padding:0 26px;border-radius:14px;background:var(--t-bright);color:var(--t-ink);font-size:16px;font-weight:800">{{ $label }}</a>
    </div>
    @endif
  </div>
</section>
