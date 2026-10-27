@extends('emails.layout')
@section('title', $subjectLine)
@section('content')
    <h2 style="margin:0 0 12px;font-size:18px;color:#1e3a8a;">{{ $heading }}</h2>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        @foreach($rows as $label => $value)
            @continue($value === null || $value === '')
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #f1f5f9;width:130px;color:#64748b;vertical-align:top;">{{ $label }}</td>
                <td style="padding:8px 0;border-bottom:1px solid #f1f5f9;vertical-align:top;white-space:pre-line;">{{ $value }}</td>
            </tr>
        @endforeach
    </table>
    @if($url)
        <p style="margin:24px 0 8px;">
            <a href="{{ $url }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:8px;font-weight:bold;">مشاهده در پنل</a>
        </p>
    @endif
@endsection
