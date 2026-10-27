@extends('emails.layout')
@section('title', 'ثبت درخواست')
@section('content')
    <h2 style="margin:0 0 12px;font-size:18px;color:#1e3a8a;">{{ $order->name }} عزیز، درخواست شما ثبت شد</h2>
    <p style="margin:0 0 12px;">از اعتماد شما سپاسگزاریم. کارشناسان ما درخواست شما را بررسی می‌کنند و{{ $order->contact_time_label ? ' در بازه «'.$order->contact_time_label.'»' : ' در اولین فرصت' }} با شما تماس می‌گیرند.</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border-radius:8px;margin:16px 0;">
        <tr>
            <td style="padding:16px;text-align:center;">
                <div style="font-size:12px;color:#64748b;">کد پیگیری</div>
                <div style="font-size:26px;font-weight:bold;color:#1e3a8a;letter-spacing:2px;direction:ltr;">{{ $order->code }}</div>
                <div style="font-size:13px;color:#475569;margin-top:6px;">{{ $order->type_label }}@if($order->budget_label) — {{ $order->budget_label }}@endif</div>
            </td>
        </tr>
    </table>
    <p style="margin:24px 0 8px;text-align:center;">
        <a href="{{ $trackUrl }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:8px;font-weight:bold;">پیگیری درخواست</a>
    </p>
@endsection
@section('footer')
    اگر این درخواست را شما ثبت نکرده‌اید، این ایمیل را نادیده بگیرید.
@endsection
