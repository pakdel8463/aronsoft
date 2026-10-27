<!DOCTYPE html>
<html lang="fa" class="js">
<head>
    <base href="../" />
    <meta charset="utf-8" />
    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="{{asset('admin/dash/images/favicon.png')}}" />
    <!-- Page Title  -->
    <title>طرح بندی خالی | قالب مدیریتی دش‌لایت</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="{{asset('admin/dash/assets/css/dashlite.rtl.css')}}" />
    <link id="skin-default" rel="stylesheet" href="{{asset('admin/dash/assets/css/theme.css')}}" />
    <link rel="stylesheet" href="/admin/dash/assets/nestable/nestable.css">
    @yield('topcss')
</head>

<body class="has-rtl nk-body npc-default has-apps-sidebar has-sidebar" dir="rtl">
<div class="nk-app-root">
@include('admin.dash.layouts.sidebar')
    <!-- main @s -->
    <div class="nk-main">
        <!-- wrap @s -->
        <div class="nk-wrap">
            <!-- main header @s -->
@include('admin.dash.layouts.header')
            <!-- main header @e -->
@include('admin.dash.layouts.main-sidebar')
            <!-- content @s -->
            <div class="nk-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            <!-- content @e -->
        </div>
        <!-- wrap @e -->
    </div>
    <!-- main @e -->
</div>
<!-- app-root @e -->
<!-- select region modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="region">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <a href="#" class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
            <div class="modal-body modal-body-md">
                <h5 class="title mb-4">زبان</h5>
                <div class="nk-country-region">
                    <ul class="country-list text-center gy-2">

                        <li>
                            <a href="#" class="country-item">
                                <img src="{{asset('admin/dash/images/flags/iran.png')}}" alt="" class="country-flag" />
                                <span class="country-name">فارسی</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="country-item">
                                <img src="{{asset('admin/dash/images/flags/english.png')}}" alt="" class="country-flag" />
                                <span class="country-name">انگلیسی</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- .modal-content -->
    </div>
    <!-- .modla-dialog -->
</div>
<!-- .modal -->
<!-- JavaScript -->
<script src="{{asset('admin/dash/assets/js/bundle.js')}}"></script>
<script src="{{asset('admin/dash/assets/js/scripts.js')}}"></script>

@yield('btnscript')
</body>
</html>
