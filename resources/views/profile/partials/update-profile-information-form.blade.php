<section>
    <header class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">اطلاعات پروفایل</h2>
        <p class="mt-1 text-sm text-slate-600">نام و ایمیل حساب خود را به‌روزرسانی کنید.</p>
    </header>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
    <form method="post" action="{{ route('profile.update') }}" class="max-w-xl space-y-4">
        @csrf @method('patch')
        <div>
            <x-input-label for="name" value="نام" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="email" value="ایمیل" />
            <x-text-input id="email" name="email" type="email" class="form-input text-left" dir="ltr" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-3 text-sm text-slate-700">ایمیل شما هنوز تأیید نشده است.
                    <button form="send-verification" class="font-bold text-tech-blue hover:underline">ارسال دوباره لینک تأیید</button></p>
                @if (session('status') === 'verification-link-sent')<p class="form-msg is-ok mt-2" role="status">لینک تأیید جدید ارسال شد.</p>@endif
            @endif
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>ذخیره</x-primary-button>
            @if (session('status') === 'profile-updated')<p class="text-sm font-bold text-emerald-600" role="status">ذخیره شد.</p>@endif
        </div>
    </form>
</section>
