@extends('emails.layout')
@section('title', $isNew ? 'تیکت جدید' : 'پاسخ مشتری')
@section('content')
    <h2 style="margin:0 0 12px;font-size:18px;color:#1e3a8a;">{{ $isNew ? 'تیکت جدید ثبت شد' : 'مشتری به تیکت پاسخ داد' }}</h2>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border-radius:8px;margin:12px 0;">
        <tr><td style="padding:14px 16px;font-size:14px;line-height:2;">
            <div><strong>شماره:</strong> <span dir="ltr">{{ $ticket->number }}</span></div>
            <div><strong>موضوع:</strong> {{ $ticket->subject }}</div>
            <div><strong>دپارتمان:</strong> {{ $ticket->department?->name ?? '—' }} &nbsp; <strong>اولویت:</strong> {{ $ticket->priority_label }}</div>
            <div><strong>مشتری:</strong> {{ $customer?->name }} <span dir="ltr">({{ $customer?->email }})</span></div>
        </td></tr>
    </table>
    <div style="border-right:3px solid #93c5fd;padding:4px 12px;margin:12px 0;color:#475569;white-space:pre-line;">{{ $excerpt }}</div>
    <p style="margin:24px 0 8px;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:8px;font-weight:bold;">مشاهده و پاسخ در پنل</a>
    </p>
@endsection
@section('footer')
    این پیام به‌صورت خودکار از سیستم تیکتینگ ارسال شده است.
@endsection
