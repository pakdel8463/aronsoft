@extends('front.t-one.master')

@php
    // عنوان/توضیح صفحه می‌تواند از طریق پارامتر کامپوننت یا اسلات داده شود.
    if (!empty($title) || !empty($meta_description)) {
        \App\View\Seo::meta(array_filter([
            'title' => $title ?? null,
            'description' => $meta_description ?? null,
        ]));
    }
@endphp

@section('main')
    {{ $slot }}
@endsection

@section('head')
    {{ $head ?? '' }}
@endsection

@section('topcss')
    {{ $topcss ?? '' }}
@endsection

@section('bottomscript')
    {{ $bottomscript ?? '' }}{{ $btnscript ?? '' }}{{ $script ?? '' }}
@endsection
