{{-- ویژوال تزئینی (کد/ترمینال). $v = ['title'=>..., 'lines'=>[ [indent, html-safe text, class?] ], 'chips'=>[...] ] --}}
<div class="relative mx-auto max-w-md">
    <div class="mock-window" dir="ltr">
        <div class="mock-bar"><span></span><span></span><span></span><b class="ms-3 text-xs font-medium text-slate-400">{{ $v['title'] }}</b></div>
        <pre class="code-block !m-0 overflow-hidden !rounded-none !border-0 text-[12.5px] leading-7"><code>@foreach($v['lines'] as $ln)<span class="block {{ $ln[2] ?? '' }}" style="padding-left:{{ ($ln[0] ?? 0) * 1.1 }}rem">{{ $ln[1] }}</span>@endforeach</code></pre>
    </div>
    @foreach(($v['chips'] ?? []) as $i => [$ic, $label])
        <div class="absolute {{ $i ? '-bottom-4 -end-3 sm:-end-6' : '-top-4 -start-3 sm:-start-6' }} flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-card">
            <i class="ph-fill {{ $ic }} text-lg text-tech-blue"></i>{{ $label }}
        </div>
    @endforeach
</div>
