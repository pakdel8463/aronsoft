@extends('errors.layout')
@section('code', '404')
@section('title', 'صفحه پیدا نشد')
@section('message', 'آدرسی که دنبالش هستید وجود ندارد یا منتقل شده است. می‌توانید از صفحه اصلی ادامه دهید یا با ما تماس بگیرید.')
@section('actions')
    <a class="btn btn-primary" href="/">بازگشت به صفحه اصلی</a>
    <a class="btn btn-ghost" href="/contact">تماس با ما</a>
@endsection
