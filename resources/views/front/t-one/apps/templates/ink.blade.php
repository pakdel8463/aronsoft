@php
    /** @var \App\Models\ProductPage $page */
    /** @var array $keys */
    // پیوند امن: href + (target/rel برای آدرس بیرونی)
    $ln = function ($u) {
        $u = trim((string) $u);

        return new \Illuminate\Support\HtmlString('href="'.e($u !== '' ? $u : '#').'"'.(str_starts_with($u, 'http') ? ' target="_blank" rel="noopener"' : ''));
    };
    // عدد درصد (۰ تا ۱۰۰) از ورودی متنی
    $num = function ($v, $d = 0.0) {
        $v = str_replace(['٫', ','], '.', strtr(trim((string) $v), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']));

        return is_numeric($v) ? round(max(0.0, min(100.0, (float) $v)), 2) : (float) $d;
    };
@endphp
<div class="tpl-ink" style="{{ $page->paletteStyle() }}">
<style>
.tpl-ink{width:100%;overflow-x:clip;direction:rtl;font-family:'Vazirmatn',Tahoma,sans-serif;background:#F3F6F7;color:var(--t-ink);-webkit-font-smoothing:antialiased;line-height:normal;text-align:right}
.tpl-ink *,.tpl-ink *::before,.tpl-ink *::after{box-sizing:content-box}
.tpl-ink a{color:inherit;text-decoration:none}
.tpl-ink [hidden]{display:none!important}
.tpl-ink .tok{display:inline-flex;align-items:center;padding:6px 12px;border-radius:8px;font-size:14px;font-weight:600;border:1px solid #D8E1E5;background:#fff;color:var(--t-ink)}
.tpl-ink .tok-v{background:var(--t-soft);border-color:#B7DFE4;color:#075E68}
.tpl-ink .tok-a{background:var(--t-ink);border-color:var(--t-ink);color:#fff}
.tpl-ink .wk{display:grid;grid-template-columns:104px repeat(7,minmax(0,1fr));gap:6px;align-items:center}
.tpl-ink .pill{border-radius:8px;padding:9px 2px;text-align:center;font-size:12px;font-weight:700}
.tpl-ink .pm{background:var(--t-soft);color:#075E68}
.tpl-ink .pe{background:#E0E4FA;color:#3A49A8}
.tpl-ink .pn{background:#FCEBCF;color:#8A5410}
.tpl-ink .po{border:1px dashed #C3CFD5;color:#8A99A3;background:transparent;font-weight:500}
.tpl-ink .dh{font-size:12px;font-weight:700;color:#5A6A75;text-align:center}
.tpl-ink .rp{width:100%;border-collapse:collapse;font-size:14px}
.tpl-ink .rp th{font-weight:600;font-size:12px;color:#5A6A75;text-align:right;padding:12px 20px;border-bottom:1px solid #D8E1E5;background:#F8FAFB}
.tpl-ink .rp td{padding:14px 20px;border-bottom:1px solid #EAF0F2}
.tpl-ink .rp tr:last-child td{border-bottom:0}
.tpl-ink .fq{border-bottom:1px solid #D8E1E5}
.tpl-ink .fq summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:22px 0;font-size:17px;font-weight:700}
.tpl-ink .fq summary::-webkit-details-marker{display:none}
.tpl-ink .fq p{margin:0;padding:0 0 22px;font-size:15px;line-height:2;color:#4B5B66;max-width:760px}
.tpl-ink .fq[open] summary svg{transform:rotate(180deg)}
.tpl-ink .ink-btn:hover{background:#12304A}
.tpl-ink .ink-ghost:hover{background:#E6EDF0}
.tpl-ink a:focus-visible,.tpl-ink button:focus-visible,.tpl-ink input:focus-visible,.tpl-ink summary:focus-visible{outline:3px solid var(--t-accent);outline-offset:3px;border-radius:6px}
.tpl-ink .ink-dark a:focus-visible,.tpl-ink .ink-dark button:focus-visible,.tpl-ink .ink-dark input:focus-visible{outline-color:var(--t-bright)}
.tpl-ink .ink-lead .form-msg{flex:1 1 100%;margin:0;font-size:14px;border-radius:12px}
.tpl-ink .ink-lead .form-msg.is-ok{background:#E8F7EF;border-color:#B6E3C9;color:#17603A}
.tpl-ink .ink-lead .form-msg.is-err{background:#FDE4E4;border-color:#F4B9BB;color:#B42327}
</style>
@foreach($keys as $k)
    @includeIf('front.t-one.apps.templates.ink.'.$k, ['page' => $page, 's' => $page->section($k), 'keys' => $keys, 'ln' => $ln, 'num' => $num])
@endforeach
</div>
