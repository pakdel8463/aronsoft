@extends('layouts.auth')
@section('title', 'ورود به حساب کاربری')
@section('heading', 'ورود به حساب کاربری')
@section('subheading', 'برای پیگیری سفارش‌ها و پیش‌فاکتورها وارد شوید.')
@section('auth')
    <x-auth-session-status :status="session('status')" class="mb-4" />
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        @include('auth._field', ['name' => 'email', 'label' => 'آدرس ایمیل', 'type' => 'email', 'icon' => 'envelope-simple', 'ac' => 'username', 'ltr' => true, 'placeholder' => 'name@company.com', 'value' => old('email')])
        @include('auth._field', ['name' => 'password', 'label' => 'رمز عبور', 'type' => 'password', 'icon' => 'lock-key', 'ac' => 'current-password', 'ltr' => true])
        <div class="flex items-center justify-between gap-3">
            <label class="flex min-h-[44px] cursor-pointer items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-slate-300 text-tech-blue focus:ring-tech-blue"> مرا به خاطر بسپار</label>
            @if(Route::has('password.request'))<a href="{{ route('password.request') }}" class="inline-flex min-h-[44px] items-center text-sm font-bold text-tech-blue hover:underline">فراموشی رمز؟</a>@endif
        </div>
        @include('aronlabs-captcha::captcha-input')
        <button type="submit" class="btn btn-primary btn-lg btn-block">ورود به حساب <i class="ph ph-arrow-left" aria-hidden="true"></i></button>
    </form>
@endsection
@section('alt')حساب ندارید؟ <a href="{{ route('register') }}" class="inline-flex min-h-[44px] items-center font-bold text-tech-blue hover:underline">ثبت‌نام کنید</a>@endsection
