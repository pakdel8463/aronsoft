<x-admin.card title="برچسب‌های قابل‌استفاده" icon="tag" class="h-max">
    <p class="mb-3 text-xs leading-6 text-slate-500">در متن پیام و ایمیل‌ها، این برچسب‌ها با مقدار واقعی جایگزین می‌شوند:</p>
    <ul class="space-y-2 text-xs">
        @foreach ($mergeTags as $tag => $desc)
            <li><code dir="ltr" class="rounded bg-slate-100 px-1.5 py-0.5 font-bold text-aron-700">{{ $tag }}</code> <span class="text-slate-500">{{ $desc }}</span></li>
        @endforeach
    </ul>
</x-admin.card>
