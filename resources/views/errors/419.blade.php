@extends('errors.layout')
@section('code', '419')
@section('title', 'نشست منقضی شد')
@section('message', 'مدت زیادی از باز شدن این صفحه گذشته است. لطفاً صفحه را تازه‌سازی کنید و فرم را دوباره ارسال کنید.')
@section('actions')
    <a class="btn btn-primary" href="javascript:location.reload()">تازه‌سازی صفحه</a>
    <a class="btn btn-ghost" href="/">بازگشت به صفحه اصلی</a>
@endsection
