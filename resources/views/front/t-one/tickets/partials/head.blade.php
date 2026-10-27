{{-- نوار بالای ناحیهٔ تیکت: ناوبری حساب + عنوان --}}
<div class="border-b border-slate-100 bg-gradient-to-b from-brand-50/60 to-white">
    <div class="container-x py-8 md:py-10">
        <nav class="mb-4 flex flex-wrap gap-2" aria-label="حساب کاربری">
            <a href="{{ route('dashboard') }}" class="chip-filter">داشبورد</a>
            <a href="{{ route('profile.edit') }}" class="chip-filter">پروفایل</a>
            <a href="{{ route('tickets.index') }}" class="chip-filter is-active" aria-current="page">تیکت‌ها</a>
        </nav>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <h1 class="h1-page">{{ $heading }}</h1>
                @isset($sub)<p class="lead mt-2">{{ $sub }}</p>@endisset
            </div>
            @isset($action)
                <a href="{{ $action['url'] }}" class="btn btn-primary"><i class="ph ph-{{ $action['icon'] ?? 'chat-centered-text' }}" aria-hidden="true"></i> {{ $action['label'] }}</a>
            @endisset
        </div>
    </div>
</div>
