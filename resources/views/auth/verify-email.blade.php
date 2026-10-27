@extends('layouts.auth')
@section('title', 'تأیید آدرس ایمیل')
@section('heading', 'تأیید آدرس ایمیل')
@section('subheading', 'برای فعال‌سازی حساب، لینک ارسال‌شده به ایمیل خود را بررسی کنید. اگر ایمیل را دریافت نکردید، می‌توانید دوباره درخواست دهید.')
@section('auth')
    @if (session('status') == 'verification-link-sent')<div class="form-msg is-ok mb-4" role="status">یک لینک تأیید جدید به ایمیل شما ارسال شد.</div>@endif
    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">@csrf<button type="submit" class="btn btn-primary btn-lg btn-block">ارسال دوباره لینک تأیید</button></form>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-secondary btn-block">خروج از حساب کاربری</button></form>
    </div>
@endsection
