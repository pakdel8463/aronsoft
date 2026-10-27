@component('front.t-one.layouts.content')
    <section class="relative pt-40 pb-32 min-h-[70vh] bg-white dark:bg-darkbg-950 overflow-hidden">
        <div class="absolute inset-0 bg-dot-pattern text-gray-300 dark:text-tech-cyan/10 pointer-events-none"></div>
        <div class="relative z-10 max-w-lg mx-auto px-6 text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full {{ ($ok ?? true) ? 'bg-tech-green/10 text-tech-green' : 'bg-red-50 dark:bg-red-500/10 text-tech-red' }}">
                <i class="ph-fill {{ ($ok ?? true) ? 'ph-check-circle' : 'ph-warning-circle' }} text-5xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4">{{ $title }}</h1>
            <p class="text-gray-500 dark:text-gray-400 leading-loose mb-10">{{ $message }}</p>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-7 py-3 bg-tech-blue text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/20">
                بازگشت به صفحه اصلی <i class="ph ph-arrow-left"></i>
            </a>
        </div>
    </section>
@endcomponent
