@extends('emails.layout')
@section('title', $subject ?? 'خبرنامه آرون سافت')
@section('content')
    {!! $content !!}
@endsection
@section('footer')
    @if(!empty($unsubscribeUrl))
        <a href="{{ $unsubscribeUrl }}" style="color:#64748b;">لغو عضویت در خبرنامه</a>
    @endif
@endsection
