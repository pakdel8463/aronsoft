@extends('emails.layout')
@section('title', $subjectLine)
@section('content')
    <h2 style="margin:0 0 12px;font-size:18px;color:#1e3a8a;">{{ $subjectLine }}</h2>
    <div style="white-space:pre-line;line-height:2;color:#334155;">{{ $body }}</div>
    @if ($url)
        <p style="margin:24px 0 8px;"><a href="{{ $url }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:8px;font-weight:bold;">مشاهده در پنل</a></p>
    @endif
@endsection
