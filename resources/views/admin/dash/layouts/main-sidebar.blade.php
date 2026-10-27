<div class="nk-sidebar" data-content="sidebarMenu">
    <div class="nk-sidebar-inner" data-simplebar>
        <ul class="nk-menu nk-menu-md">
            <li class="nk-menu-heading">
                <h6 class="overline-title text-primary-alt">داشبوردها</h6>
            </li>
            <!-- .nk-menu-heading -->
            <li class="nk-menu-item">
                <a href="{{route('panel.home')}}" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-dashboard"></em></span>
                    <span class="nk-menu-text">داشبورد</span>
                </a>
            </li>

            <li class="nk-menu-item">
                <a href="{{route('panel.proformas.index')}}" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-dashboard"></em></span>
                    <span class="nk-menu-text">پیش فاکتور ساز</span>
                </a>
            </li>
            <!-- .nk-menu-heading -->
            <li class="nk-menu-item has-sub">
                <a href="{{route('panel.portfolios.index')}}" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-tile-thumb"></em></span>
                    <span class="nk-menu-text">پروژه ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="{{route('panel.portfolios.index')}}" class="nk-menu-link"><span class="nk-menu-text">پروژه ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="{{route('panel.portfolios.create')}}" class="nk-menu-link"><span class="nk-menu-text">پروژه جدید</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>

            <!-- .nk-menu-heading -->
            <li class="nk-menu-item has-sub">
                <a href="{{route('panel.posts.index')}}" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                    <span class="nk-menu-text">نوشته ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="{{route('panel.posts.index')}}" class="nk-menu-link"><span class="nk-menu-text">نوشته ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="{{route('panel.posts.create')}}" class="nk-menu-link"><span class="nk-menu-text">نوشته جدید</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-heading -->
            <li class="nk-menu-item has-sub">
                <a href="{{route('panel.categories.index')}}" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                    <span class="nk-menu-text">دسته بندی ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="{{route('panel.categories.index')}}" class="nk-menu-link"><span class="nk-menu-text">دسته بندی ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="{{route('panel.categories.create')}}" class="nk-menu-link"><span class="nk-menu-text">دسته بندی جدید</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-heading -->
            <li class="nk-menu-item has-sub">
                <a href="{{route('panel.landing.index')}}" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                    <span class="nk-menu-text">لندینگ ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="{{route('panel.landing.index')}}" class="nk-menu-link"><span class="nk-menu-text">لندینگ ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="{{route('panel.landing.create')}}" class="nk-menu-link"><span class="nk-menu-text">لندینگ جدید</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            @if(auth()->user()->hasPermission('users.index') || auth()->user()->hasPermission('users.create'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-users"></em></span>
                        <span class="nk-menu-text">مدیریت کاربران</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('users.index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.users.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست کاربران</span>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->hasPermission('users.create'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.users.create') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">کاربر جدید</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            @if(auth()->user()->hasPermission('permission-index') || auth()->user()->hasPermission('permission-create'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-lock-alt-fill"></em></span>
                        <span class="nk-menu-text">دسترسی ها</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('permissions-index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.permissions.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست دسترسی ها</span>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->hasPermission('permission-create'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.permissions.create') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">دسترسی جدید</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            @if(auth()->user()->hasPermission('role-index') || auth()->user()->hasPermission('role-create'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-puzzle"></em></span>
                        <span class="nk-menu-text">نقش ها</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('role-index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.roles.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست نقش ها</span>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->hasPermission('role-create'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.roles.create') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">نقش جدید</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif
            <!-- .nk-menu-item -->

            @if(auth()->user()->hasPermission('leads-index'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon">
                            <em class="icon ni ni-puzzle"></em></span>
                        <span class="nk-menu-text">لید ها</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('leads-index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.leads.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست لید ها</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(auth()->user()->hasPermission('menus-index'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon">
                            <em class="icon ni ni-layers"></em></span>
                        <span class="nk-menu-text">منو ها</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('menus-index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.menus.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست منو ها</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(auth()->user()->hasPermission('comments-index'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon">
                            <em class="icon ni ni-chat-circle"></em></span>
                        <span class="nk-menu-text">نظرات</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('comments-index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.comments.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست نظرات</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(auth()->user()->hasPermission('products-index'))
                <li class="nk-menu-item has-sub">
                    <a href="#" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon">
                            <em class="icon ni ni-chat-circle"></em></span>
                        <span class="nk-menu-text">محصول ها</span>
                    </a>

                    <ul class="nk-menu-sub">

                        @if(auth()->user()->hasPermission('products-index'))
                            <li class="nk-menu-item">
                                <a href="{{ route('panel.products.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-text">لیست محصول ها</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-tranx"></em></span>
                    <span class="nk-menu-text">تراکنش ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/transaction-basic.html" class="nk-menu-link"><span class="nk-menu-text">لیست تراکنش ها - پایه</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/transaction-crypto.html" class="nk-menu-link"><span class="nk-menu-text">لیست تراکنش ها - ارز دیجیتال</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                    <span class="nk-menu-text">فاکتورها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/invoice-list.html" class="nk-menu-link"><span class="nk-menu-text">لیست فاکتورها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/invoice-details.html" class="nk-menu-link"><span class="nk-menu-text">جزئیات فاکتور</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-card-view"></em></span>
                    <span class="nk-menu-text">محصولات</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/product-list.html" class="nk-menu-link"><span class="nk-menu-text">لیست محصولات</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/product-card.html" class="nk-menu-link"><span class="nk-menu-text">کارت های محصول</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/product-details.html" class="nk-menu-link"><span class="nk-menu-text">جزئیات محصول</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item">
                <a href="html/pricing-table.html" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-view-col"></em></span>
                    <span class="nk-menu-text">جداول قیمت گذاری</span>
                </a>
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item">
                <a href="html/gallery.html" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-img"></em></span>
                    <span class="nk-menu-text">گالری تصاویر</span>
                </a>
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-heading">
                <h6 class="overline-title text-primary-alt">صفحه های متفرقه</h6>
            </li>
            <!-- .nk-menu-heading -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-signin"></em></span>
                    <span class="nk-menu-text">صفحه های احراز هویت</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/pages/auths/auth-login.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">ورود</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/auths/auth-register.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">ثبت نام / عضویت</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/auths/auth-reset.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">فراموشی رمز عبور</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/auths/auth-success.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">موفقیت آمیز / تایید</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-text">نسخه کلاسیک - نسخه 2</span></a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-login-v2.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">ورود</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-register-v2.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">ثبت نام / عضویت</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-reset-v2.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">فراموشی رمز عبور</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-success-v2.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">موفقیت آمیز / تایید</span></a>
                            </li>
                        </ul>
                    </li>
                    <li class="nk-menu-item">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-text">نسخه بدون اسلایدر - نسخه 3</span></a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-login-v3.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">ورود</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-register-v3.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">ثبت نام / عضویت</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-reset-v3.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">فراموشی رمز عبور</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/pages/auths/auth-success-v3.html" class="nk-menu-link" target="_blank"><span class="nk-menu-text">موفقیت آمیز / تایید</span></a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-files"></em></span>
                    <span class="nk-menu-text">صفحه های خطا</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/pages/errors/404-classic.html" target="_blank" class="nk-menu-link"><span class="nk-menu-text">404 کلاسیک</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/errors/504-classic.html" target="_blank" class="nk-menu-link"><span class="nk-menu-text">504 کلاسیک</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/errors/404-s1.html" target="_blank" class="nk-menu-link"><span class="nk-menu-text">404 مدرن</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/errors/504-s1.html" target="_blank" class="nk-menu-link"><span class="nk-menu-text">504 مدرن</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-files"></em></span>
                    <span class="nk-menu-text">سایر صفحه ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/_blank.html" class="nk-menu-link"><span class="nk-menu-text">خالی / راه اندازی</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/faqs.html" class="nk-menu-link"><span class="nk-menu-text">سوالات متداول / راهنمایی</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/terms-policy.html" class="nk-menu-link"><span class="nk-menu-text">شرایط و ضوابط / سیاست</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/regular-v1.html" class="nk-menu-link"><span class="nk-menu-text">صفحه عادی - نسخه 1</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/pages/regular-v2.html" class="nk-menu-link"><span class="nk-menu-text">صفحه عادی - نسخه 2</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-heading">
                <h6 class="overline-title text-primary-alt">کامپوننت ها</h6>
            </li>
            <!-- .nk-menu-heading -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-layers"></em></span>
                    <span class="nk-menu-text">عناصر رابط کاربری</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/elements/alerts.html" class="nk-menu-link"><span class="nk-menu-text">هشدارها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/accordions.html" class="nk-menu-link"><span class="nk-menu-text">آکاردئون ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/avatar.html" class="nk-menu-link"><span class="nk-menu-text">آواتار</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/badges.html" class="nk-menu-link"><span class="nk-menu-text">نشان ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/buttons.html" class="nk-menu-link"><span class="nk-menu-text">دکمه ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/buttons-group.html" class="nk-menu-link"><span class="nk-menu-text">گروه های دکمه</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/breadcrumb.html" class="nk-menu-link"><span class="nk-menu-text">بردکرامب ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/cards.html" class="nk-menu-link"><span class="nk-menu-text">کارت ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/carousel.html" class="nk-menu-link"><span class="nk-menu-text">کاروسل ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/list-dropdown.html" class="nk-menu-link"><span class="nk-menu-text">لیست های کشویی</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/modals.html" class="nk-menu-link"><span class="nk-menu-text">مودال ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/pagination.html" class="nk-menu-link"><span class="nk-menu-text">صفحه بندی</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/popover.html" class="nk-menu-link"><span class="nk-menu-text">پاپ اورها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/progress.html" class="nk-menu-link"><span class="nk-menu-text">پیشرفت</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/preloader.html" class="nk-menu-link"><span class="nk-menu-text">پیش بارگذارنده</span><span class="nk-menu-badge">جدید</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/placeholders.html" class="nk-menu-link"><span class="nk-menu-text">نگهدارنده مکان</span><span class="nk-menu-badge">جدید</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/spinner.html" class="nk-menu-link"><span class="nk-menu-text">اسپینرها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/tabs.html" class="nk-menu-link"><span class="nk-menu-text">زبانه ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/toast.html" class="nk-menu-link"><span class="nk-menu-text">Toast ها</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/tooltip.html" class="nk-menu-link"><span class="nk-menu-text">راهنمای ابزار</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/elements/typography.html" class="nk-menu-link"><span class="nk-menu-text">تایپوگرافی</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-text">ابزارها</span></a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-border.html" class="nk-menu-link"><span class="nk-menu-text">خط حاشیه</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-colors.html" class="nk-menu-link"><span class="nk-menu-text">رنگ ها</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-display.html" class="nk-menu-link"><span class="nk-menu-text">نمایش</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-embeded.html" class="nk-menu-link"><span class="nk-menu-text">جاسازی</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-flex.html" class="nk-menu-link"><span class="nk-menu-text">فلکس</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-text.html" class="nk-menu-link"><span class="nk-menu-text">متن</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-sizing.html" class="nk-menu-link"><span class="nk-menu-text">سایزبندی</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-spacing.html" class="nk-menu-link"><span class="nk-menu-text">فاصله گذاری</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/elements/util-others.html" class="nk-menu-link"><span class="nk-menu-text">سایر</span></a>
                            </li>
                        </ul>
                        <!-- .nk-menu-sub -->
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-dot-box"></em></span>
                    <span class="nk-menu-text">آیکون های ساخته شده</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/misc/svg-icons.html" class="nk-menu-link">
                            <span class="nk-menu-text">آیکون های SVG - انحصاری</span>
                        </a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/misc/nioicon.html" class="nk-menu-link">
                            <span class="nk-menu-text">Nioicon - دست ساز</span>
                        </a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item">
                <a href="html/components/misc/icons.html" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-menu-circled"></em></span>
                    <span class="nk-menu-text">کتابخانه های آیکون</span>
                </a>
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-table-view"></em></span>
                    <span class="nk-menu-text">جداول</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/tables/table-basic.html" class="nk-menu-link"><span class="nk-menu-text">جداول پایه</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/tables/table-special.html" class="nk-menu-link"><span class="nk-menu-text">جداول انحصاری</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/tables/table-datatable.html" class="nk-menu-link"><span class="nk-menu-text">جدول داده ها</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-card-view"></em></span>
                    <span class="nk-menu-text">فرم ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/forms/form-elements.html" class="nk-menu-link"><span class="nk-menu-text">عناصر فرم</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/checkbox-radio.html" class="nk-menu-link"><span class="nk-menu-text">چک باکس و رادیو</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/advanced-controls.html" class="nk-menu-link"><span class="nk-menu-text">کنترل های پیشرفته</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/input-group.html" class="nk-menu-link"><span class="nk-menu-text">گروه های ورودی</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/form-upload.html" class="nk-menu-link"><span class="nk-menu-text">فرم آپلود</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/datetime-picker.html" class="nk-menu-link"><span class="nk-menu-text">انتخابگر تاریخ و زمان</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/number-spinner.html" class="nk-menu-link"><span class="nk-menu-text">اسپینر عدد</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/nouislider.html" class="nk-menu-link"><span class="nk-menu-text">noUiSlider</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/form-layouts.html" class="nk-menu-link"><span class="nk-menu-text">طرح بندی های فرم</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/forms/form-validation.html" class="nk-menu-link"><span class="nk-menu-text">اعتبارسنجی فرم</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-text">چند مرحله ای</span></a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="html/components/forms/form-wizard.html" class="nk-menu-link"><span class="nk-menu-text">دموی پایه</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/forms/wizard/create-project.html" class="nk-menu-link"><span class="nk-menu-text">ایجاد پروژه</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/forms/wizard/create-profile.html" class="nk-menu-link"><span class="nk-menu-text">ایجاد پروفایل</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/forms/wizard/two-factor-auth.html" class="nk-menu-link"><span class="nk-menu-text">احراز هویت دو مرحله ای</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a target="_blank" href="html/components/forms/wizard/survey-v1.html" class="nk-menu-link"><span class="nk-menu-text">نظرسنجی</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a target="_blank" href="html/components/forms/wizard/survey-v2.html" class="nk-menu-link"><span class="nk-menu-text">نظرسنجی نسخه 2</span></a>
                            </li>
                        </ul>
                        <!-- .nk-menu-sub -->
                    </li>
                    <li class="nk-menu-item">
                        <a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-text">ویرایشگر توانمند</span></a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="html/components/forms/form-summernote.html" class="nk-menu-link"><span class="nk-menu-text">Summernote</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/forms/form-quill.html" class="nk-menu-link"><span class="nk-menu-text">Quill</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="html/components/forms/form-tinymce.html" class="nk-menu-link"><span class="nk-menu-text">Tinymce</span></a>
                            </li>
                        </ul>
                        <!-- .nk-menu-sub -->
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-pie"></em></span>
                    <span class="nk-menu-text">نمودارها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/charts/chartjs.html" class="nk-menu-link"><span class="nk-menu-text">Chart JS</span></a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="html/components/charts/knob.html" class="nk-menu-link"><span class="nk-menu-text">Knob JS</span></a>
                    </li>
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-puzzle"></em></span>
                    <span class="nk-menu-text">ابزارک ها</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/widgets/cards.html" class="nk-menu-link"><span class="nk-menu-text">ابزارک های کارت</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/widgets/charts.html" class="nk-menu-link"><span class="nk-menu-text">ابزارک های نمودار</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/widgets/ratings.html" class="nk-menu-link"><span class="nk-menu-text">ابزارک های امتیازدهی</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item has-sub">
                <a href="#" class="nk-menu-link nk-menu-toggle">
                    <span class="nk-menu-icon"><em class="icon ni ni-block-over"></em></span>
                    <span class="nk-menu-text">متفرقه</span>
                </a>
                <ul class="nk-menu-sub">
                    <li class="nk-menu-item">
                        <a href="html/components/misc/slick-sliders.html" class="nk-menu-link"><span class="nk-menu-text">اسلایدر اسلیک</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/misc/toastr.html" class="nk-menu-link"><span class="nk-menu-text">Toastr</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/misc/sweet-alert.html" class="nk-menu-link"><span class="nk-menu-text">Sweet Alert</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/misc/js-tree.html" class="nk-menu-link"><span class="nk-menu-text">jsTree</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/misc/dual-listbox.html" class="nk-menu-link"><span class="nk-menu-text">لیست باکس دوتایی</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/misc/dragula.html" class="nk-menu-link"><span class="nk-menu-text">Dragula</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                    <li class="nk-menu-item">
                        <a href="html/components/misc/map.html" class="nk-menu-link"><span class="nk-menu-text">نقشه گوگل</span></a>
                    </li>
                    <!-- .nk-menu-item -->
                </ul>
                <!-- .nk-menu-sub -->
            </li>
            <!-- .nk-menu-item -->
            <li class="nk-menu-item">
                <a href="html/email-templates.html" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-text-rich"></em></span>
                    <span class="nk-menu-text">قالب ایمیل</span>
                </a>
            </li>
        </ul>
        <!-- .nk-menu -->
    </div>
</div>
