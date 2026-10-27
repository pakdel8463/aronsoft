<x-admin.layout title="تاریخچه اعلان‌ها" subtitle="وضعیت ارسال پیامک و ایمیل">
    <div class="rounded-2xl border border-line bg-white p-5"><a class="mb-4 inline-block text-blue-600" href="{{ route('panel.notifications.settings') }}">تنظیمات اعلان‌ها</a>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b"><th class="p-3 text-right">تاریخ</th><th class="p-3 text-right">رویداد</th><th class="p-3 text-right">کانال</th><th class="p-3 text-right">گیرنده</th><th class="p-3 text-right">وضعیت</th><th class="p-3 text-right">خطا</th></tr></thead><tbody>
            @forelse($deliveries as $item)<tr class="border-b"><td class="p-3" dir="ltr">{{ $item->created_at }}</td><td class="p-3">{{ $item->event }}</td><td class="p-3">{{ $item->channel }}</td><td class="p-3" dir="ltr">{{ $item->recipient }}</td><td class="p-3">{{ $item->status }}</td><td class="p-3 text-red-600">{{ $item->error }}</td></tr>@empty<tr><td colspan="6" class="p-6 text-center">هنوز اعلانی ارسال نشده است.</td></tr>@endforelse
        </tbody></table></div><div class="mt-4">{{ $deliveries->links() }}</div>
    </div>
</x-admin.layout>
