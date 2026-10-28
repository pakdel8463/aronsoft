{{-- ماکاپ داشبورد هیرو (HTML/CSS، بدون تصویر). در ستون کناری هیرو قرار می‌گیرد. --}}
<div class="relative lg:col-span-6" aria-hidden="true" dir="ltr">
    <div class="mock-window mx-auto max-w-[34rem]">
        <div class="mock-bar">
            <i></i><i></i><i></i>
            <span class="ms-3 flex-1 truncate rounded-md bg-white px-3 py-1 text-[11px] text-slate-400">app.your-business.com/dashboard</span>
        </div>
        <div class="grid grid-cols-[3.25rem_1fr] sm:grid-cols-[9rem_1fr]">
            <div class="space-y-2 border-e border-slate-100 bg-slate-50/70 p-3">
                @foreach(['ph-squares-four', 'ph-shopping-bag', 'ph-users', 'ph-chart-line-up', 'ph-gear'] as $k => $ic)
                    <div class="flex items-center gap-2 rounded-lg px-2 py-2 {{ $k === 0 ? 'bg-brand-50 text-tech-blue' : 'text-slate-400' }}">
                        <i class="ph {{ $ic }} text-base"></i><span class="hidden h-2 flex-1 rounded bg-current opacity-30 sm:block"></span>
                    </div>
                @endforeach
            </div>
            <div class="space-y-3 p-4">
                <div class="grid grid-cols-3 gap-2.5">
                    @foreach([['ph-receipt', 'bg-brand-50 text-tech-blue'], ['ph-package', 'bg-emerald-50 text-emerald-600'], ['ph-lightning', 'bg-amber-50 text-amber-600']] as [$ic, $cl])
                        <div class="rounded-xl border border-slate-100 p-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $cl }}"><i class="ph {{ $ic }}"></i></span>
                            <span class="mt-2.5 block h-2.5 w-3/4 rounded bg-slate-200"></span>
                            <span class="mt-1.5 block h-2 w-1/2 rounded bg-slate-100"></span>
                        </div>
                    @endforeach
                </div>
                <div class="rounded-xl border border-slate-100 p-3">
                    <div class="mb-2 flex items-center justify-between"><span class="h-2.5 w-24 rounded bg-slate-200"></span><span class="chip chip-blue !text-[10px]">Live</span></div>
                    <div class="flex h-24 items-end gap-1.5">
                        @foreach([38, 52, 44, 66, 58, 78, 70, 92, 84, 100] as $h)
                            <span class="flex-1 rounded-t bg-gradient-to-t from-tech-blue/80 to-tech-blue/30" style="height: {{ $h }}%"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- کارت‌های شناور: دو مورد (نه بیشتر) --}}
    <div class="absolute -bottom-5 -start-1 hidden items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-card sm:flex">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i class="ph-bold ph-check text-base"></i></span>
        <span class="text-start"><span class="block text-[11px] font-bold text-slate-800">Deploy succeeded</span><span class="block text-[10px] text-slate-400">CI/CD pipeline</span></span>
    </div>
    <div class="absolute -top-4 -end-1 hidden items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-card sm:flex">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-tech-blue"><i class="ph ph-shield-check text-base"></i></span>
        <span class="text-start"><span class="block text-[11px] font-bold text-slate-800">Secure by design</span><span class="block text-[10px] text-slate-400">OWASP checklist</span></span>
    </div>
</div>
