{{-- Flash messages → toasts. session: success | error | status | warning | info, plus validation errors --}}
@php
    $toastRows = [];
    foreach (['success' => 'success', 'status' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type) {
        $v = session($key);
        if (is_string($v) && $v !== '') $toastRows[] = [$type, $v];
    }
    $styles = [
        'success' => ['check-circle', 'border-mint-200 bg-mint-50 text-mint-700'],
        'error' => ['warning-circle', 'border-coral-200 bg-coral-50 text-coral-700'],
        'warning' => ['warning', 'border-amber-200 bg-amber-50 text-amber-800'],
        'info' => ['info', 'border-aron-200 bg-aron-50 text-aron-700'],
    ];
@endphp
<div class="adm-toasts" data-adm-toasts>
    @foreach ($toastRows as [$type, $text])
        <div class="adm-toast flex items-start gap-3 rounded-2xl border p-4 text-sm font-semibold shadow-adm-pop {{ $styles[$type][1] }}" data-adm-toast role="status">
            <i class="ph-fill ph-{{ $styles[$type][0] }} mt-0.5 text-xl"></i>
            <span class="flex-1 leading-6">{{ $text }}</span>
            <button type="button" data-adm-toast-close aria-label="بستن" class="opacity-60 hover:opacity-100"><i class="ph ph-x"></i></button>
        </div>
    @endforeach
    @if ($errors->any())
        <div class="adm-toast rounded-2xl border border-coral-200 bg-coral-50 p-4 text-sm text-coral-700 shadow-adm-pop" data-adm-toast data-timeout="12000" role="alert">
            <div class="mb-2 flex items-center gap-2 font-extrabold">
                <i class="ph-fill ph-warning-circle text-xl"></i>
                <span class="flex-1">اطلاعات فرم را بررسی کنید</span>
                <button type="button" data-adm-toast-close aria-label="بستن" class="opacity-60 hover:opacity-100"><i class="ph ph-x"></i></button>
            </div>
            <ul class="list-disc space-y-1 ps-5 font-medium leading-6">
                @foreach (collect($errors->all())->unique()->take(8) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
