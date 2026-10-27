@extends('admin.dash.master')

@section('content')

    {{$slot}}

@endsection

@section('script')
    {{ $script ?? '' }}
@endsection

@section('topcss')

    {{$topcss ?? ''}}
@endsection

@section('btnscript')
    {{ $btnscript ?? '' }}
@endsection
