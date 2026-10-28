{{-- CSS/JS مشترک بلوک‌های تعاملی (اسلایدر، اسلایدر کارت، تب) — فقط یک بار در صفحه --}}
@once
    <link rel="stylesheet" href="{{ asset('css/blocks.css') }}?v={{ @filemtime(public_path('css/blocks.css')) ?: 1 }}">
    <script src="{{ asset('js/blocks.js') }}?v={{ @filemtime(public_path('js/blocks.js')) ?: 1 }}" defer></script>
@endonce
