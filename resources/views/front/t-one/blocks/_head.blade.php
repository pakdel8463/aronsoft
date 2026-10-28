{{-- سرتیتر مشترک بلوک‌ها. ورودی: $s (eyebrow, title, description)، $anchor، اختیاری $start (راست‌چین به‌جای وسط‌چین) --}}
@if (($s['eyebrow'] ?? '') !== '' || ($s['title'] ?? '') !== '' || ($s['description'] ?? '') !== '')
    <div class="section-head {{ ! empty($start) ? 'is-start' : '' }} reveal">
        @if (($s['eyebrow'] ?? '') !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
        @if (($s['title'] ?? '') !== '')<h2 id="{{ $anchor }}-title" class="h2-section">{{ $s['title'] }}</h2>@endif
        @if (($s['description'] ?? '') !== '')<p class="lead mt-3">{{ $s['description'] }}</p>@endif
    </div>
@endif
