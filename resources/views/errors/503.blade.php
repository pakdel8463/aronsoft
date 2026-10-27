@extends('errors.layout')
@section('code', '503')
@section('title', 'در حال به‌روزرسانی')
@section('message', 'سایت برای انجام کارهای فنی موقتاً در دسترس نیست و به‌زودی بازمی‌گردد. از صبوری شما سپاسگزاریم.')
@section('actions')
    <a class="btn btn-primary" href="javascript:location.reload()">تلاش دوباره</a>
@endsection
