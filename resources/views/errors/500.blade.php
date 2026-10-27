@extends('errors.layout')
@section('code', '500')
@section('title', 'خطای سرور')
@section('message', 'مشکلی در سمت سرور رخ داد. خطا برای تیم فنی ثبت شد؛ لطفاً چند دقیقه دیگر دوباره تلاش کنید.')
@section('actions')
    <a class="btn btn-primary" href="/">بازگشت به صفحه اصلی</a>
    <a class="btn btn-ghost" href="javascript:location.reload()">تلاش دوباره</a>
@endsection
