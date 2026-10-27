{{-- قالب citrus: ورزش و سلامت (سبز تیره + لیمویی). $page, $keys, $preview --}}
<div class="tpl-citrus" style="{{ $page->paletteStyle() }}">
<style>
@font-face{font-family:'Lalezar';src:url('{{ asset('fonts/lalezar/Lalezar-Regular.woff') }}') format('woff');font-display:swap}
.tpl-citrus{width:100%;overflow-x:clip;direction:rtl;text-align:right;font-family:'Vazirmatn',Tahoma,sans-serif;background:var(--t-paper);color:var(--t-ink);-webkit-font-smoothing:antialiased;line-height:normal}
.tpl-citrus *,.tpl-citrus *::before,.tpl-citrus *::after{box-sizing:border-box}
.tpl-citrus a{color:inherit;text-decoration:none}
.tpl-citrus h1,.tpl-citrus h2,.tpl-citrus h3,.tpl-citrus p,.tpl-citrus b{color:inherit;letter-spacing:normal;text-wrap:wrap}
.tpl-citrus img,.tpl-citrus svg{display:inline-block;max-width:none}
.tpl-citrus [id^="sec-"]{scroll-margin-top:84px}
.tpl-citrus .hd{font-family:'Lalezar','Vazirmatn',sans-serif;font-weight:400}
.tpl-citrus .pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700}
.tpl-citrus .ring{transform:rotate(-90deg);transform-origin:50% 50%}
.tpl-citrus .fq{border-bottom:2px solid var(--t-ink)}
.tpl-citrus .fq summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:22px 0;font-size:18px;font-weight:800}
.tpl-citrus .fq summary::-webkit-details-marker{display:none}
.tpl-citrus .fq summary::marker{content:''}
.tpl-citrus .fq p{margin:0;padding:0 0 22px;font-size:15px;line-height:2;color:#3C5B52;max-width:760px}
.tpl-citrus .fq[open] summary svg{transform:rotate(180deg)}
.tpl-citrus .lime:hover{background:#D2FF5E}
.tpl-citrus .ghost:hover{background:rgba(255,255,255,.1)}
.tpl-citrus .row{display:flex;align-items:center;gap:12px;padding:12px 0;border-top:1px solid #E3EFDC}
.tpl-citrus .row:first-child{border-top:0}
.tpl-citrus :focus-visible{outline:3px solid var(--t-ink);outline-offset:3px;box-shadow:0 0 0 3px var(--t-accent)}
.tpl-citrus input.c-in{width:100%;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.08);color:#fff;border-radius:16px;padding:16px 18px;font:inherit;font-size:16px;direction:ltr;text-align:left}
.tpl-citrus input.c-in::placeholder{color:#8FB5A8}
.tpl-citrus .c-lead .form-msg{margin-top:12px;font-size:14px}
.tpl-citrus .c-lead [hidden]{display:none}
.tpl-citrus .c-ph1,.tpl-citrus .c-ph2{max-width:100%}
@media (max-width:620px){
  .tpl-citrus .c-vis{height:auto !important;display:flex;flex-direction:column;align-items:center;gap:28px}
  .tpl-citrus .c-ph1,.tpl-citrus .c-ph2{position:relative !important;left:auto !important;right:auto !important;top:auto !important;transform:none !important}
  .tpl-citrus .c-ph2{order:-1}
  .tpl-citrus .c-hero-sec{padding-bottom:64px !important}
  .tpl-citrus .c-pad{padding-left:20px !important;padding-right:20px !important}
  .tpl-citrus .c-cta{padding:36px 24px !important}
}
@media (prefers-reduced-motion:reduce){.tpl-citrus *{transition:none !important;animation:none !important;scroll-behavior:auto !important}}
</style>
@foreach($keys as $k)
    @includeIf('front.t-one.apps.templates.citrus.'.$k, ['page' => $page, 's' => $page->section($k)])
@endforeach
</div>
