<section>
    <header class="mb-4">
        <h2 class="text-lg font-bold text-slate-900">حذف حساب کاربری</h2>
        <p class="mt-1 text-sm leading-7 text-slate-600">با حذف حساب، همه‌ی اطلاعات آن برای همیشه پاک می‌شود. پیش از حذف، اطلاعات مورد نیاز خود را ذخیره کنید.</p>
    </header>
    <details class="max-w-xl rounded-xl border border-red-200 bg-red-50/40" @if($errors->userDeletion->isNotEmpty()) open @endif>
        <summary class="flex min-h-[48px] cursor-pointer list-none items-center px-4 text-sm font-bold text-red-700">حذف حساب…</summary>
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4 px-4 pb-4">
            @csrf @method('delete')
            <p class="text-sm leading-7 text-slate-700">برای تأیید، رمز عبور خود را وارد کنید.</p>
            <div>
                <x-input-label for="delete_password" value="رمز عبور" />
                <x-text-input id="delete_password" name="password" type="password" class="form-input text-left" dir="ltr" autocomplete="current-password" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>
            <x-danger-button>حذف دائمی حساب</x-danger-button>
        </form>
    </details>
</section>
