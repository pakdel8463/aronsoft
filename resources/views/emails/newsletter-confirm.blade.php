@extends('emails.layout')
@section('title', 'تأیید عضویت')
@section('content')
    <h2 style="margin:0 0 12px;font-size:18px;color:#1e3a8a;">عضویت در خبرنامه را تأیید کنید</h2>
    <p style="margin:0 0 12px;">برای تکمیل عضویت با این ایمیل در خبرنامه آرون سافت، روی دکمه زیر بزنید.</p>
    <p style="margin:24px 0 8px;text-align:center;">
        <a href="{{ $confirmUrl }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:bold;">تأیید عضویت</a>
    </p>
@endsection
@section('footer')
    اگر شما درخواست عضویت نداده‌اید، این ایمیل را نادیده بگیرید؛ بدون تأیید شما هیچ ایمیلی برایتان ارسال نمی‌شود.<br>
    <a href="{{ $unsubscribeUrl }}" style="color:#64748b;">لغو درخواست</a>
@endsection
