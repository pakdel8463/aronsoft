<section>
    <header class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">تغییر رمز عبور</h2>
        <p class="mt-1 text-sm text-slate-600">از یک رمز عبور طولانی و تصادفی استفاده کنید.</p>
    </header>
    <form method="post" action="{{ route('password.update') }}" class="max-w-xl space-y-4">
        @csrf @method('put')
        @foreach([['current_password', 'رمز عبور فعلی', 'current-password', 'update_password_current_password'], ['password', 'رمز عبور جدید', 'new-password', 'update_password_password'], ['password_confirmation', 'تکرار رمز عبور جدید', 'new-password', 'update_password_password_confirmation']] as [$n, $l, $ac, $id])
            <div>
                <x-input-label :for="$id" :value="$l" />
                <x-text-input :id="$id" :name="$n" type="password" class="form-input text-left" dir="ltr" :autocomplete="$ac" />
                <x-input-error :messages="$errors->updatePassword->get($n)" />
            </div>
        @endforeach
        <div class="flex items-center gap-4">
            <x-primary-button>ذخیره</x-primary-button>
            @if (session('status') === 'password-updated')<p class="text-sm font-bold text-emerald-600" role="status">ذخیره شد.</p>@endif
        </div>
    </form>
</section>
