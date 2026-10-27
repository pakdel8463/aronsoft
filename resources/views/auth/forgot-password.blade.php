@extends('layouts.auth')
@section('title', 'بازیابی رمز عبور')
@section('heading', 'بازیابی رمز عبور')
@section('subheading', 'ایمیل خود را وارد کنید تا لینک بازیابی رمز عبور برای شما ارسال شود.')
@section('auth')
    <x-auth-session-status :status="session('status')" class="mb-4" />
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        @include('auth._field', ['name' => 'email', 'label' => 'آدرس ایمیل', 'type' => 'email', 'icon' => 'envelope-simple', 'ac' => 'email', 'ltr' => true, 'placeholder' => 'name@company.com', 'value' => old('email')])
        <button type="submit" class="btn btn-primary btn-lg btn-block">ارسال لینک بازیابی</button>
    </form>
@endsection
@section('alt')<a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center font-bold text-tech-blue hover:underline">بازگشت به ورود</a>@endsection
