@extends('errors.layout')
@section('code', '429')
@section('title', 'درخواست بیش از حد')
@section('message', 'تعداد درخواست‌های شما از حد مجاز گذشته است. لطفاً چند دقیقه صبر کنید و دوباره تلاش کنید.')
@section('actions')
    <a class="btn btn-primary" href="/">بازگشت به صفحه اصلی</a>
@endsection
