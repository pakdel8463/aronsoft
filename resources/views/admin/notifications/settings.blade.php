<x-admin.layout title="تنظیمات اعلان‌ها" subtitle="قاصدک، ایمیل و اعلان‌های داخلی">
    <div class="mx-auto max-w-5xl space-y-5">
        <form method="POST" action="{{ route('panel.notifications.update') }}" class="space-y-5">@csrf
            <div class="rounded-2xl border border-line bg-white p-5"><h2 class="mb-4 text-lg font-bold">فعال‌سازی کانال‌ها</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach(['enabled' => 'فعال‌سازی کل سیستم', 'sms_enabled' => 'ارسال پیامک قاصدک', 'email_enabled' => 'ارسال ایمیل', 'admin_enabled' => 'اعلان داخل پنل مدیریت'] as $key => $label)
                        <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><input type="checkbox" name="{{ $key }}" value="1" @checked($settings[$key]) class="size-5">{{ $label }}</label>
                    @endforeach
                </div>
            </div>
            <div class="rounded-2xl border border-line bg-white p-5"><h2 class="mb-4 text-lg font-bold">اتصال پیامک قاصدک</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm">API Key <span class="text-xs text-slate-400">{{ $hasSmsKey ? '(ذخیره شده؛ برای نگه‌داشتن خالی بگذارید)' : '(ثبت نشده)' }}</span><input type="password" name="sms_api_key" autocomplete="new-password" class="mt-2 w-full rounded-xl border p-3" placeholder="کلید API قاصدک"></label>
                    <label class="block text-sm">شماره خط فرستنده<input type="text" name="sms_sender" value="{{ old('sms_sender', $settings['sms_sender']) }}" class="mt-2 w-full rounded-xl border p-3" placeholder="اختیاری"></label>
                    <label class="block text-sm sm:col-span-2">شماره‌های دریافت‌کننده (جداشده با کاما)<textarea name="sms_recipients" rows="2" class="mt-2 w-full rounded-xl border p-3" dir="ltr">{{ old('sms_recipients', $settings['sms_recipients']) }}</textarea></label>
                </div>
            </div>
            <div class="rounded-2xl border border-line bg-white p-5"><h2 class="mb-4 text-lg font-bold">تنظیمات SMTP</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach(['smtp_host' => 'SMTP Host', 'smtp_port' => 'Port', 'smtp_username' => 'Username', 'smtp_from_address' => 'From Email', 'smtp_from_name' => 'From Name'] as $key => $label)
                        <label class="block text-sm">{{ $label }}<input type="text" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}" class="mt-2 w-full rounded-xl border p-3" dir="ltr"></label>
                    @endforeach
                    <label class="block text-sm">SMTP Password <span class="text-xs text-slate-400">{{ $hasSmtpPassword ? '(ذخیره شده)' : '(ثبت نشده)' }}</span><input type="password" name="smtp_password" autocomplete="new-password" class="mt-2 w-full rounded-xl border p-3"></label>
                    <label class="block text-sm">Encryption<select name="smtp_encryption" class="mt-2 w-full rounded-xl border p-3">@foreach(['tls','ssl','none'] as $v)<option value="{{ $v }}" @selected($settings['smtp_encryption'] === $v)>{{ strtoupper($v) }}</option>@endforeach</select></label>
                    <label class="block text-sm sm:col-span-2">ایمیل‌های دریافت‌کننده (جداشده با کاما)<textarea name="email_recipients" rows="2" class="mt-2 w-full rounded-xl border p-3" dir="ltr">{{ old('email_recipients', $settings['email_recipients']) }}</textarea></label>
                </div>
            </div>
            <div class="rounded-2xl border border-line bg-white p-5"><h2 class="mb-4 text-lg font-bold">اعلان برای هر رویداد</h2><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b"><th class="p-3 text-right">رویداد</th><th class="p-3">SMS</th><th class="p-3">Email</th><th class="p-3">Admin</th></tr></thead><tbody>
                @foreach($events as $key => $label)<tr class="border-b"><td class="p-3">{{ $label }}</td>@foreach(['sms','email','admin'] as $channel)<td class="p-3 text-center"><input type="checkbox" name="events[{{ $key }}][{{ $channel }}]" value="1" @checked($settings['events'][$key][$channel] ?? false) class="size-4"></td>@endforeach</tr>@endforeach
            </tbody></table></div></div>
            @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-700">{{ $errors->first() }}</div>@endif
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white">ذخیره تنظیمات</button>
        </form>
        <div class="rounded-2xl border border-line bg-white p-5"><h2 class="mb-4 font-bold">ارسال آزمایشی (ابتدا تنظیمات را ذخیره کنید)</h2>
            <form method="POST" action="{{ route('panel.notifications.test') }}" class="flex flex-wrap gap-3">@csrf<select name="channel" class="rounded-xl border p-3"><option value="sms">SMS</option><option value="email">Email</option></select><input name="recipient" required placeholder="موبایل یا ایمیل مقصد" class="min-w-60 flex-1 rounded-xl border p-3" dir="ltr"><button class="rounded-xl border border-blue-600 px-4 py-3 text-blue-600">ارسال تست</button></form>
            <a href="{{ route('panel.notifications.index') }}" class="mt-3 inline-block text-sm text-blue-600">مشاهده تاریخچه ارسال‌ها ←</a>
        </div>
    </div>
</x-admin.layout>
