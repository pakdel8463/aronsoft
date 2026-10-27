@extends('layouts.auth')
@section('title', 'ثبت‌نام')
@section('heading', 'ایجاد حساب کاربری')
@section('subheading', 'با ثبت‌نام می‌توانید سفارش‌ها و پیش‌فاکتورهای خود را پیگیری کنید.')
@section('auth')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        @include('auth._field', ['name' => 'name', 'label' => 'نام کامل', 'icon' => 'user', 'ac' => 'name', 'placeholder' => 'مثلاً: علی رضایی', 'value' => old('name')])
        @include('auth._field', ['name' => 'email', 'label' => 'آدرس ایمیل', 'type' => 'email', 'icon' => 'envelope-simple', 'ac' => 'username', 'ltr' => true, 'placeholder' => 'name@company.com', 'value' => old('email')])
        <div class="grid gap-4 sm:grid-cols-2">
            @include('auth._field', ['name' => 'password', 'label' => 'رمز عبور', 'type' => 'password', 'icon' => 'lock-key', 'ac' => 'new-password', 'ltr' => true, 'placeholder' => 'حداقل ۸ کاراکتر'])
            @include('auth._field', ['name' => 'password_confirmation', 'label' => 'تکرار رمز', 'type' => 'password', 'icon' => 'lock-key', 'ac' => 'new-password', 'ltr' => true])
        </div>
        @include('aronlabs-captcha::captcha-input')
        <button type="submit" class="btn btn-primary btn-lg btn-block">ایجاد حساب <i class="ph ph-arrow-left" aria-hidden="true"></i></button>
    </form>
@endsection
@section('alt')قبلاً ثبت‌نام کرده‌اید؟ <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center font-bold text-tech-blue hover:underline">وارد شوید</a>@endsection
