@extends('layouts.auth')
@section('title', 'تأیید رمز عبور')
@section('heading', 'تأیید رمز عبور')
@section('subheading', 'برای ادامه، رمز عبور خود را وارد کنید. این مرحله برای امنیت بیشتر انجام می‌شود.')
@section('auth')
    <x-auth-session-status :status="session('status')" class="mb-4" />
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        @include('auth._field', ['name' => 'password', 'label' => 'رمز عبور', 'type' => 'password', 'icon' => 'lock-key', 'ac' => 'current-password', 'ltr' => true])
        <button type="submit" class="btn btn-primary btn-lg btn-block">تأیید و ادامه</button>
    </form>
@endsection
