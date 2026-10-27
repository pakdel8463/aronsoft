@extends('layouts.auth')
@section('title', 'تنظیم رمز عبور جدید')
@section('heading', 'تنظیم رمز عبور جدید')
@section('subheading', 'رمز عبور جدید خود را وارد کنید.')
@section('auth')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        @include('auth._field', ['name' => 'email', 'label' => 'آدرس ایمیل', 'type' => 'email', 'icon' => 'envelope-simple', 'ac' => 'username', 'ltr' => true, 'value' => old('email', request()->email)])
        @include('auth._field', ['name' => 'password', 'label' => 'رمز عبور جدید', 'type' => 'password', 'icon' => 'lock-key', 'ac' => 'new-password', 'ltr' => true, 'placeholder' => 'حداقل ۸ کاراکتر'])
        @include('auth._field', ['name' => 'password_confirmation', 'label' => 'تکرار رمز عبور', 'type' => 'password', 'icon' => 'lock-key', 'ac' => 'new-password', 'ltr' => true])
        <button type="submit" class="btn btn-primary btn-lg btn-block">ذخیره رمز جدید</button>
    </form>
@endsection
