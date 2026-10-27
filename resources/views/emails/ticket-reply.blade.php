@extends('emails.layout')
@section('title', 'پاسخ به تیکت')
@section('content')
    <h2 style="margin:0 0 12px;font-size:18px;color:#1e3a8a;">{{ $ticket->user?->name }} عزیز، به تیکت شما پاسخ داده شد</h2>
    <p style="margin:0 0 12px;">تیکت <strong dir="ltr">{{ $ticket->number }}</strong> — {{ $ticket->subject }}</p>
    <div style="border-right:3px solid #93c5fd;padding:4px 12px;margin:12px 0;color:#475569;white-space:pre-line;">{{ $excerpt }}</div>
    <p style="margin:24px 0 8px;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:8px;font-weight:bold;">مشاهده گفتگو و پاسخ</a>
    </p>
@endsection
@section('footer')
    برای ادامهٔ گفتگو از همان صفحهٔ تیکت پاسخ دهید؛ پاسخ به این ایمیل ثبت نمی‌شود.
@endsection
