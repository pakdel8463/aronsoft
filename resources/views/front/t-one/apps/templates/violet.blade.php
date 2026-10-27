{{-- قالب violet: بدنه‌ی صفحه‌ی محصول. $page, $keys, $preview --}}
<div class="tpl-violet" style="{{ $page->paletteStyle() }}">
<style>
.tpl-violet{display:block;width:100%;overflow-x:clip;direction:rtl;text-align:start;font-family:'Vazirmatn',Tahoma,sans-serif;line-height:normal;background:#fff;color:var(--t-ink,#1A1033);-webkit-font-smoothing:antialiased}
.tpl-violet *,.tpl-violet *::before,.tpl-violet *::after{box-sizing:content-box}
.tpl-violet i,.tpl-violet em{font-style:normal}
.tpl-violet a{color:inherit;text-decoration:none}
.tpl-violet h1,.tpl-violet h2,.tpl-violet h3,.tpl-violet p,.tpl-violet b,.tpl-violet summary{color:inherit;font-family:inherit;letter-spacing:inherit}
.tpl-violet h1{letter-spacing:-0.01em}
.tpl-violet img{max-width:100%}
.tpl-violet .tx{display:flex;align-items:center;gap:12px;padding:14px 0;border-top:1px solid #EEE8FB}
.tpl-violet .tx:first-child{border-top:0}
.tpl-violet .ic{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;flex:none}
.tpl-violet .ch{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#F1EBFF;color:var(--t-deep,#4C1D95)}
.tpl-violet .dt{width:8px;height:8px;border-radius:50%;display:inline-block}
.tpl-violet .lr{display:flex;align-items:center;gap:16px;padding:16px 20px;border-top:1px solid #EEE8FB;font-size:15px}
.tpl-violet .lr:first-child{border-top:0}
.tpl-violet .bar{height:10px;border-radius:6px;background:#EFE9FD;overflow:hidden}
.tpl-violet .bar>i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,#6D28D9,#A66BFF)}
.tpl-violet .fq{border-bottom:1px solid #E6DEFA}
.tpl-violet .fq summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:22px 0;font-size:17px;font-weight:700}
.tpl-violet .fq summary::-webkit-details-marker{display:none}
.tpl-violet .fq summary svg{flex:none}
.tpl-violet .fq p{margin:0;padding:0 0 22px;font-size:15px;line-height:2;color:#5B4E7C;max-width:760px}
.tpl-violet .fq[open] summary svg{transform:rotate(180deg)}
.tpl-violet .bw:hover{background:#F1EBFF}
.tpl-violet .bg:hover{background:rgba(255,255,255,.12)}
.tpl-violet a:focus-visible,.tpl-violet summary:focus-visible,.tpl-violet button:focus-visible,.tpl-violet input:focus-visible{outline:3px solid #A66BFF;outline-offset:3px;border-radius:8px}
.tpl-violet .v-dark a:focus-visible,.tpl-violet .v-dark button:focus-visible,.tpl-violet .v-dark input:focus-visible{outline-color:#fff}
@media (prefers-reduced-motion:no-preference){
.tpl-violet .bw,.tpl-violet .bg{transition:background-color .15s}
.tpl-violet .fq summary svg{transition:transform .2s}
}
.tpl-violet .v-hero-vis{flex:1.3 1 560px;position:relative;height:650px;min-width:0}
.tpl-violet .v-hero-vis.v-img{height:auto;display:flex;justify-content:center;align-items:center}
@media (max-width:1140px){
.tpl-violet .v-hero-vis{flex-basis:100%}
}
@media (max-width:640px){
.tpl-violet .v-hero-vis:not(.v-img){height:auto;display:flex;flex-direction:column;align-items:center;gap:28px}
.tpl-violet .v-browser{position:static!important;width:100%!important;min-width:0!important}
.tpl-violet .v-phone{position:static!important;order:-1;max-width:100%}
.tpl-violet .v-cta{padding:40px 24px!important}
.tpl-violet .lr{flex-wrap:wrap;gap:6px 12px;padding:14px 16px}
.tpl-violet .lr .v-lr-d{width:auto!important}
.tpl-violet .lr .v-lr-t{flex:1 1 140px!important}
.tpl-violet .lr .v-lr-a{width:auto!important;margin-inline-start:auto}
.tpl-violet .v-aud-n{width:auto!important;flex:1 1 100%}
}
</style>
@foreach($keys as $k)
    @includeIf('front.t-one.apps.templates.violet.'.$k, ['page' => $page, 's' => $page->section($k)])
@endforeach
</div>
