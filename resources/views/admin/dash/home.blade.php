@component('admin.dash.layouts.content')

    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block">
                <div class="row g-gs">
                    <div class="col-12">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <div class="card-title mb-4">
                                    <h3 class="title">به داشبورد سیستم مدیریت خوش آمدید!</h3>
                                </div>
                                <div class="row g-gs">
                                    <div class="col-xxl-3">
                                        <div class="fake-class">
                                            <h5 class="title">شروع کنید</h5>
                                            <div class="nk-block-des text-soft">
                                                <p>امرزوو با محتوای جدیدتر و بهتر همراهت هستم تا سایت هر روز بهتر بشه </p>
                                            </div>
                                            <a href="{{route('panel.portfolios.create')}}" class="btn btn-primary btn-lg mt-4">معرفی پروژه جدید</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xxl-3">
                                        <div class="fake-class">
                                            <h5 class="title">مراحل بعدی</h5>
                                            <ul class="link-list is-compact pb-0">
                                                <li>
                                                    <a href="{{route('panel.posts.create')}}"><em class="icon ni ni-file-text"></em><span>یک پست وبلاگ بنویسید</span></a>
                                                </li>
                                                <li>
                                                    <a href="#"><em class="icon ni ni-property-add"></em><span>یک صفحه  اضافه کنید</span></a>
                                                </li>
                                                <li>
                                                    <a href="{{ setting('siteurl') }}"><em class="icon ni ni-laptop"></em><span>وب سایت خود را مشاهده کنید</span></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xxl-3">
                                        <div class="fake-class">
                                            <h5 class="title">در یک نگاه</h5>
                                            <ul class="link-list is-compact pb-0">
                                                <li>
                                                    <a href="{{route('panel.posts.index')}}">
                                                        <em class="icon ni ni-edit-fill"></em>
                                                        <span>

                                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#"><em class="icon ni ni-files"></em><span>21 صفحه</span></a>
                                                </li>
                                                <li>
                                                    <a href="#"><em class="icon ni ni-comments"></em><span>18 نظر</span></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xxl-3">
                                        <div class="fake-class">
                                            <h5 class="title">اقدامات بیشتر</h5>
                                            <ul class="link-list is-compact pb-0">
                                                <li>
                                                    <a href="#"><em class="icon ni ni-grid-fill"></em><span>مدیریت منو</span></a>
                                                </li>
                                                <li>
                                                    <a href="#"><em class="icon ni ni-comments"></em><span>خاموش یا روشن کردن نظرات</span></a>
                                                </li>
                                                <li>
                                                    <a href="#"><em class="icon ni ni-more-h"></em><span>پیام ها</span></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- .row -->
                            </div>
                            <!-- .card-inner -->
                        </div>
                        <!-- .card -->
                    </div>
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">آخرین نوشته ها</h3>
                                <div class="nk-block-des text-soft">
                                    <p>شما در مجموع 95 پست دارید.</p>
                                </div>
                            </div>
                            <!-- .nk-block-head-content -->
                            <div class="nk-block-head-content">
                                <a href="{{route('panel.posts.create')}}" class="btn btn-icon btn-primary d-md-none"><em class="icon ni ni-plus"></em></a>
                                <a href="{{route('panel.posts.create')}}" class="btn btn-primary d-none d-md-inline-flex"><em class="icon ni ni-plus"></em><span>افزودن پست</span></a>
                            </div>
                            <!-- .nk-block-head-content -->
                        </div>
                        <!-- .nk-block-between -->
                    </div>
                    <!-- .nk-block-head -->
                    <div class="nk-block">
                        <div class="card card-bordered card-stretch">
                            <div class="card-inner-group">
                                <div class="card-inner position-relative card-tools-toggle">
                                    <div class="card-title-group">
                                        <div class="card-tools">
                                            <div class="form-inline flex-nowrap gx-3">
                                                <div class="form-wrap w-150px">
                                                    <select class="form-select js-select2" data-search="off" data-placeholder="عمل دسته جمعی">
                                                        <option value="">عمل دسته جمعی</option>
                                                        <option value="edit">ویرایش</option>
                                                        <option value="delete">انتقال به سطل زباله</option>
                                                    </select>
                                                </div>
                                                <div class="btn-wrap">
                                                    <span class="d-none d-md-block"><button class="btn btn-dim btn-outline-light disabled">اعمال</button></span>
                                                    <span class="d-md-none">
                                                                        <button class="btn btn-dim btn-outline-light btn-icon disabled"><em class="icon ni ni-arrow-right"></em></button>
                                                                    </span>
                                                </div>
                                            </div>
                                            <!-- .form-inline -->
                                        </div>
                                        <!-- .card-tools -->
                                        <div class="card-tools me-n1">
                                            <ul class="btn-toolbar gx-1">
                                                <li>
                                                    <a href="#" class="btn btn-icon search-toggle toggle-search" data-target="search"><em class="icon ni ni-search"></em></a>
                                                </li>
                                                <!-- li -->
                                                <li class="btn-toolbar-sep"></li>
                                                <!-- li -->
                                                <li>
                                                    <div class="toggle-wrap">
                                                        <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-menu-right"></em></a>
                                                        <div class="toggle-content" data-content="cardTools">
                                                            <ul class="btn-toolbar gx-1">
                                                                <li class="toggle-close">
                                                                    <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-arrow-left"></em></a>
                                                                </li>
                                                                <!-- li -->
                                                                <li>
                                                                    <div class="dropdown">
                                                                        <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                            <div class="dot dot-primary"></div>
                                                                            <em class="icon ni ni-filter-alt"></em>
                                                                        </a>
                                                                        <div class="filter-wg dropdown-menu dropdown-menu-xl dropdown-menu-end">
                                                                            <div class="dropdown-head">
                                                                                <span class="sub-title dropdown-title">فیلتر کاربران</span>
                                                                                <div class="dropdown">
                                                                                    <a href="#" class="btn btn-sm btn-icon">
                                                                                        <em class="icon ni ni-more-h"></em>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="dropdown-body dropdown-body-rg">
                                                                                <div class="row gx-6 gy-3">
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">برچسب ها</label>
                                                                                            <select class="form-select js-select2">
                                                                                                <option value="any">همه برچسب ها</option>
                                                                                                <option value="health">سلامتی</option>
                                                                                                <option value="technology">تکنولوژی</option>
                                                                                                <option value="seo">سئو</option>
                                                                                                <option value="react">ری اکت</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">دسته بندی ها</label>
                                                                                            <select class="form-select js-select2">
                                                                                                <option value="any">همه دسته بندی ها</option>
                                                                                                <option value="uncategorized">دسته بندی نشده</option>
                                                                                                <option value="covid">کووید</option>
                                                                                                <option value="seo">سئو</option>
                                                                                                <option value="website">وب سایت</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">وضعیت</label>
                                                                                            <select class="form-select js-select2">
                                                                                                <option value="any">همه وضعیت ها</option>
                                                                                                <option value="published">منتشر شده</option>
                                                                                                <option value="pending">در انتظار</option>
                                                                                                <option value="draft">پیش نویس</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">تاریخ</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <div class="form-icon form-icon-right">
                                                                                                    <em class="icon ni ni-calendar"></em>
                                                                                                </div>
                                                                                                <input type="text" class="form-control date-picker" placeholder="روز/ماه/سال" />
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-12">
                                                                                        <div class="form-group">
                                                                                            <button type="button" class="btn btn-secondary">فیلتر</button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="dropdown-foot between">
                                                                                <a class="clickable" href="#">بازنشانی فیلتر</a>
                                                                                <a href="#">ذخیره فیلتر</a>
                                                                            </div>
                                                                        </div>
                                                                        <!-- .filter-wg -->
                                                                    </div>
                                                                    <!-- .dropdown -->
                                                                </li>
                                                                <!-- li -->
                                                                <li>
                                                                    <div class="dropdown">
                                                                        <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                            <em class="icon ni ni-setting"></em>
                                                                        </a>
                                                                        <div class="dropdown-menu dropdown-menu-xs dropdown-menu-end">
                                                                            <ul class="link-check">
                                                                                <li><span>نمایش</span></li>
                                                                                <li class="active"><a href="#">10</a></li>
                                                                                <li><a href="#">20</a></li>
                                                                                <li><a href="#">50</a></li>
                                                                            </ul>
                                                                            <ul class="link-check">
                                                                                <li><span>مرتب سازی</span></li>
                                                                                <li class="active"><a href="#">نزولی</a></li>
                                                                                <li><a href="#">صعودی</a></li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <!-- .dropdown -->
                                                                </li>
                                                                <!-- li -->
                                                            </ul>
                                                            <!-- .btn-toolbar -->
                                                        </div>
                                                        <!-- .toggle-content -->
                                                    </div>
                                                    <!-- .toggle-wrap -->
                                                </li>
                                                <!-- li -->
                                            </ul>
                                            <!-- .btn-toolbar -->
                                        </div>
                                        <!-- .card-tools -->
                                    </div>
                                    <!-- .card-title-group -->
                                    <div class="card-search search-wrap" data-search="search">
                                        <div class="card-body">
                                            <div class="search-content">
                                                <a href="#" class="search-back btn btn-icon toggle-search" data-target="search"><em class="icon ni ni-arrow-left"></em></a>
                                                <input type="text" class="form-control border-transparent form-focus-none" placeholder="جستجو بر اساس نام نویسنده یا عنوان پست" />
                                                <button class="search-submit btn btn-icon"><em class="icon ni ni-search"></em></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- .card-search -->
                                </div>
                                <!-- .card-inner -->
                                <div class="card-inner p-0">
                                    <div class="nk-tb-list nk-tb-ulist">
                                        <div class="nk-tb-item nk-tb-head">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid" />
                                                    <label class="custom-control-label" for="uid"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col"><span class="sub-text">عنوان</span></div>
                                            <div class="nk-tb-col tb-col-mb"><span class="sub-text">نویسنده</span></div>
                                            <div class="nk-tb-col tb-col-md"><span class="sub-text">دسته بندی ها</span></div>
                                            <div class="nk-tb-col tb-col-md"><span class="sub-text">برچسب ها</span></div>
                                            <div class="nk-tb-col tb-col-lg"><span class="sub-text">نظرات</span></div>
                                            <div class="nk-tb-col tb-col-xxl"><span class="sub-text">تاریخ</span></div>
                                            <div class="nk-tb-col nk-tb-col-tools text-end">
                                                <div class="dropdown">
                                                    <a href="#" class="btn btn-xs btn-outline-light btn-icon dropdown-toggle" data-bs-toggle="dropdown" data-offset="0,5"><em class="icon ni ni-plus"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-xs dropdown-menu-end">
                                                        <ul class="link-tidy sm no-bdr">
                                                            <li>
                                                                <div class="custom-control custom-control-sm custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" checked="" id="bl" />
                                                                    <label class="custom-control-label" for="bl">نویسنده</label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="custom-control custom-control-sm custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" checked="" id="ph" />
                                                                    <label class="custom-control-label" for="ph">تاریخ</label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="custom-control custom-control-sm custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="vri" />
                                                                    <label class="custom-control-label" for="vri">نامک</label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- .nk-tb-item -->
                                        <div class="nk-tb-item">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid1" />
                                                    <label class="custom-control-label" for="uid1"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col">
                                                <span>سلام دنیا!</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-mb">
                                                <a href="#">
                                                    <div class="user-card">
                                                        <div class="user-avatar sm bg-orange-dim">
                                                            <span>ف</span>
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead">فرشید ترنیان</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>دسته بندی نشده</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>—</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-lg">
                                                <span>1 نظر</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-xxl">
                                                <div>
                                                    <span>منتشر شده</span>
                                                </div>
                                                <div>
                                                    <span>21 آبان 1402</span>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li class="nk-tb-action-hidden">
                                                        <a class="btn btn-trigger btn-icon" data-bs-toggle="modal" href="#editPost" data-bs-placement="top" title="ویرایش سریع">
                                                            <em class="icon ni ni-edit-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li class="nk-tb-action-hidden">
                                                        <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="انتقال به سطل زباله">
                                                            <em class="icon ni ni-trash-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="drodown">
                                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a data-bs-toggle="modal" href="#editPost"><em class="icon ni ni-edit-fill"></em><span>ویرایش پست</span></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><em class="icon ni ni-trash-fill"></em><span>سطل زباله</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- .nk-tb-item -->
                                        <div class="nk-tb-item">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid2" />
                                                    <label class="custom-control-label" for="uid2"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col">
                                                <span>درباره کووید!</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-mb">
                                                <a href="#">
                                                    <div class="user-card">
                                                        <div class="user-avatar sm bg-purple-dim">
                                                            <span>ش‌م</span>
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead">شهرام میناوند</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>کووید</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>سلامتی</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-lg">
                                                <span>3 نظر</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-xxl">
                                                <div>
                                                    <span>منتشر شده</span>
                                                </div>
                                                <div>
                                                    <span>11 مهر 1402</span>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li class="nk-tb-action-hidden">
                                                        <a class="btn btn-trigger btn-icon" data-bs-toggle="modal" href="#editPost" data-bs-placement="top" title="ویرایش سریع">
                                                            <em class="icon ni ni-edit-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li class="nk-tb-action-hidden">
                                                        <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="انتقال به سطل زباله">
                                                            <em class="icon ni ni-trash-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="drodown">
                                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a data-bs-toggle="modal" href="#editPost"><em class="icon ni ni-edit-fill"></em><span>ویرایش پست</span></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><em class="icon ni ni-trash-fill"></em><span>سطل زباله</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- .nk-tb-item -->
                                        <div class="nk-tb-item">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid3" />
                                                    <label class="custom-control-label" for="uid3"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col">
                                                <span>بهینه سازی سئو</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-mb">
                                                <a href="#">
                                                    <div class="user-card">
                                                        <div class="user-avatar sm">
                                                            <img src="./images/avatar/a-sm.jpg" alt="" />
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead">خسرو میرزایی</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>سئو، وب سایت</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>تکنولوژی</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-lg">
                                                <span>12 نظر</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-xxl">
                                                <div>
                                                    <span>منتشر شده</span>
                                                </div>
                                                <div>
                                                    <span>11 مهر 1402</span>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li class="nk-tb-action-hidden">
                                                        <a class="btn btn-trigger btn-icon" data-bs-toggle="modal" href="#editPost" data-bs-placement="top" title="ویرایش سریع">
                                                            <em class="icon ni ni-edit-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li class="nk-tb-action-hidden">
                                                        <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="انتقال به سطل زباله">
                                                            <em class="icon ni ni-trash-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="drodown">
                                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a data-bs-toggle="modal" href="#editPost"><em class="icon ni ni-edit-fill"></em><span>ویرایش پست</span></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><em class="icon ni ni-trash-fill"></em><span>سطل زباله</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- .nk-tb-item -->
                                        <div class="nk-tb-item">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid4" />
                                                    <label class="custom-control-label" for="uid4"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col">
                                                <span>طراحی وب سایت</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-mb">
                                                <a href="#">
                                                    <div class="user-card">
                                                        <div class="user-avatar sm bg-orange-dim">
                                                            <span>م</span>
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead">مرضیه کوهی</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>وب سایت، طراحی</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>تکنولوژی</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-lg">
                                                <span>51 نظر</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-xxl">
                                                <div>
                                                    <span>منتشر شده</span>
                                                </div>
                                                <div>
                                                    <span>21 مرداد 1402</span>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li class="nk-tb-action-hidden">
                                                        <a class="btn btn-trigger btn-icon" data-bs-toggle="modal" href="#editPost" data-bs-placement="top" title="ویرایش سریع">
                                                            <em class="icon ni ni-edit-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li class="nk-tb-action-hidden">
                                                        <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="انتقال به سطل زباله">
                                                            <em class="icon ni ni-trash-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="drodown">
                                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a data-bs-toggle="modal" href="#editPost"><em class="icon ni ni-edit-fill"></em><span>ویرایش پست</span></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><em class="icon ni ni-trash-fill"></em><span>سطل زباله</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- .nk-tb-item -->
                                        <div class="nk-tb-item">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid5" />
                                                    <label class="custom-control-label" for="uid5"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col">
                                                <span>ری اکت ریداکس</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-mb">
                                                <a href="#">
                                                    <div class="user-card">
                                                        <div class="user-avatar sm bg-orange-dim">
                                                            <img src="./images/avatar/b-sm.jpg" alt="" />
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead">عرفان طاعتی</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>فرانت اند، وب سایت</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                <span>تکنولوژی</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-lg">
                                                <span>102 نظر</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-xxl">
                                                <div>
                                                    <span>منتشر شده</span>
                                                </div>
                                                <div>
                                                    <span>12 آبان 1402</span>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li class="nk-tb-action-hidden">
                                                        <a class="btn btn-trigger btn-icon" data-bs-toggle="modal" href="#editPost" data-bs-placement="top" title="ویرایش سریع">
                                                            <em class="icon ni ni-edit-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li class="nk-tb-action-hidden">
                                                        <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="انتقال به سطل زباله">
                                                            <em class="icon ni ni-trash-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="drodown">
                                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a data-bs-toggle="modal" href="#editPost"><em class="icon ni ni-edit-fill"></em><span>ویرایش پست</span></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><em class="icon ni ni-trash-fill"></em><span>سطل زباله</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- .nk-tb-item -->
                                    </div>
                                    <!-- .nk-tb-list -->
                                </div>
                                <!-- .card-inner -->
                                <div class="card-inner">
                                    <div class="nk-block-between-md g-3">
                                        <div class="g">
                                            <ul class="pagination justify-content-center justify-content-md-start">
                                                <li class="page-item"><a class="page-link" href="#">قبلی</a></li>
                                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                <li class="page-item">
                                                    <span class="page-link"><em class="icon ni ni-more-h"></em></span>
                                                </li>
                                                <li class="page-item"><a class="page-link" href="#">6</a></li>
                                                <li class="page-item"><a class="page-link" href="#">7</a></li>
                                                <li class="page-item"><a class="page-link" href="#">بعدی</a></li>
                                            </ul>
                                            <!-- .pagination -->
                                        </div>
                                        <div class="g">
                                            <div class="pagination-goto d-flex justify-content-center justify-content-md-start gx-3">
                                                <div>صفحه</div>
                                                <div>
                                                    <select class="form-select js-select2" data-search="on" data-dropdown="xs center">
                                                        <option value="page-1">1</option>
                                                        <option value="page-2">2</option>
                                                        <option value="page-4">4</option>
                                                        <option value="page-5">5</option>
                                                        <option value="page-6">6</option>
                                                        <option value="page-7">7</option>
                                                        <option value="page-8">8</option>
                                                        <option value="page-9">9</option>
                                                        <option value="page-10">10</option>
                                                        <option value="page-11">11</option>
                                                        <option value="page-12">12</option>
                                                        <option value="page-13">13</option>
                                                        <option value="page-14">14</option>
                                                        <option value="page-15">15</option>
                                                        <option value="page-16">16</option>
                                                        <option value="page-17">17</option>
                                                        <option value="page-18">18</option>
                                                        <option value="page-19">19</option>
                                                        <option value="page-20">20</option>
                                                    </select>
                                                </div>
                                                <div>از 102</div>
                                            </div>
                                        </div>
                                        <!-- .pagination-goto -->
                                    </div>
                                    <!-- .nk-block-between -->
                                </div>
                                <!-- .card-inner -->
                            </div>
                            <!-- .card-inner-group -->
                        </div>
                        <!-- .card -->
                    </div>
                    <!-- .nk-block -->
                    <!-- .col -->
                    <div class="col-md-6 col-xxl-4">
                        <div class="card card-bordered card-full">
                            <div class="card-inner border-bottom">
                                <div class="card-title-group">
                                    <div class="card-title">
                                        <h6 class="title">پروژه های اخیر</h6>
                                    </div>
                                    <div class="card-tools">
                                        <ul class="card-tools-nav">
                                            <li class="active">
                                                <a href="#"><span>همه</span></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <ul class="nk-activity">
                                <li class="nk-activity-item">
                                    <div class="nk-activity-media user-avatar bg-success"><img src="./images/avatar/c-sm.jpg" alt="" /></div>
                                    <div class="nk-activity-data">
                                        <div class="label">سوسن آبتین درخواست برداشت کرد.</div>
                                        <span class="time">2 ساعت پیش</span>
                                    </div>
                                </li>
                                <li class="nk-activity-item">
                                    <div class="nk-activity-media user-avatar bg-warning">م‌ن</div>
                                    <div class="nk-activity-data">
                                        <div class="label">مصطفی نوروزی سفارش داد.</div>
                                        <span class="time">2 ساعت پیش</span>
                                    </div>
                                </li>
                                <li class="nk-activity-item">
                                    <div class="nk-activity-media user-avatar bg-azure">د‌ف</div>
                                    <div class="nk-activity-data">
                                        <div class="label">دنیا فراهانی پاداش بزرگی دریافت کرد.</div>
                                        <span class="time">2 ساعت پیش</span>
                                    </div>
                                </li>
                                <li class="nk-activity-item">
                                    <div class="nk-activity-media user-avatar bg-purple"><img src="./images/avatar/d-sm.jpg" alt="" /></div>
                                    <div class="nk-activity-data">
                                        <div class="label">میلاد فتحی پول را واریز کرد.</div>
                                        <span class="time">2 ساعت پیش</span>
                                    </div>
                                </li>
                                <li class="nk-activity-item">
                                    <div class="nk-activity-media user-avatar bg-pink">ح‌ر</div>
                                    <div class="nk-activity-data">
                                        <div class="label">حامد رضازاده سفارش داد.</div>
                                        <span class="time">2 ساعت پیش</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- .card -->
                    </div>
                    <!-- .col -->
                    <div class="col-md-6 col-xxl-4">
                        <div class="card card-bordered card-full">
                            <div class="card-inner-group">
                                <div class="card-inner">
                                    <div class="card-title-group">
                                        <div class="card-title">
                                            <h6 class="title">کاربران جدید</h6>
                                        </div>
                                        <div class="card-tools">
                                            <a href="html/user-list-regular.html" class="link">مشاهده همه</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner card-inner-md">
                                    <div class="user-card">
                                        <div class="user-avatar bg-primary-dim">
                                            <span>ف‌ت</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="lead-text">فرشید ترنیان</span>
                                            <span class="sub-text">someone@email.com</span>
                                        </div>
                                        <div class="user-action">
                                            <div class="drodown">
                                                <a href="#" class="dropdown-toggle btn btn-icon btn-trigger me-n1" data-bs-toggle="dropdown" aria-expanded="false"><em class="icon ni ni-more-h"></em></a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul class="link-list-opt no-bdr">
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-setting"></em><span>تنظیمات اقدام</span></a>
                                                        </li>
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-notify"></em><span>پوش نوتیفیکیشن</span></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner card-inner-md">
                                    <div class="user-card">
                                        <div class="user-avatar bg-pink-dim">
                                            <span>س‌ع</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="lead-text">سحر عبدی</span>
                                            <span class="sub-text">someone@email.com</span>
                                        </div>
                                        <div class="user-action">
                                            <div class="drodown">
                                                <a href="#" class="dropdown-toggle btn btn-icon btn-trigger me-n1" data-bs-toggle="dropdown" aria-expanded="false"><em class="icon ni ni-more-h"></em></a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul class="link-list-opt no-bdr">
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-setting"></em><span>تنظیمات اقدام</span></a>
                                                        </li>
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-notify"></em><span>پوش نوتیفیکیشن</span></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner card-inner-md">
                                    <div class="user-card">
                                        <div class="user-avatar bg-warning-dim">
                                            <span>ت‌ک</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="lead-text">تینا کریمیان</span>
                                            <span class="sub-text">someone@email.com</span>
                                        </div>
                                        <div class="user-action">
                                            <div class="drodown">
                                                <a href="#" class="dropdown-toggle btn btn-icon btn-trigger me-n1" data-bs-toggle="dropdown" aria-expanded="false"><em class="icon ni ni-more-h"></em></a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul class="link-list-opt no-bdr">
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-setting"></em><span>تنظیمات اقدام</span></a>
                                                        </li>
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-notify"></em><span>پوش نوتیفیکیشن</span></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner card-inner-md">
                                    <div class="user-card">
                                        <div class="user-avatar bg-success-dim">
                                            <span>خ‌ض</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="lead-text">خسرو ضیایی</span>
                                            <span class="sub-text">someone@email.com</span>
                                        </div>
                                        <div class="user-action">
                                            <div class="drodown">
                                                <a href="#" class="dropdown-toggle btn btn-icon btn-trigger me-n1" data-bs-toggle="dropdown" aria-expanded="false"><em class="icon ni ni-more-h"></em></a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul class="link-list-opt no-bdr">
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-setting"></em><span>تنظیمات اقدام</span></a>
                                                        </li>
                                                        <li>
                                                            <a href="#"><em class="icon ni ni-notify"></em><span>پوش نوتیفیکیشن</span></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- .card -->
                    </div>
                    <!-- .col -->
                    <div class="col-lg-6 col-xxl-4">
                        <div class="card card-bordered h-100">
                            <div class="card-inner border-bottom">
                                <div class="card-title-group">
                                    <div class="card-title">
                                        <h6 class="title">درخواست های پشتیبانی</h6>
                                    </div>
                                    <div class="card-tools">
                                        <a href="#" class="link">همه تیکت ها</a>
                                    </div>
                                </div>
                            </div>
                            <ul class="nk-support">
                                <li class="nk-support-item">
                                    <div class="user-avatar">
                                        <img src="./images/avatar/a-sm.jpg" alt="" />
                                    </div>
                                    <div class="nk-support-content">
                                        <div class="title"><span>محمدحسن غفاری</span><span class="badge badge-dot badge-dot-xs bg-warning ms-1">در انتظار</span></div>
                                        <p>با تشکر از اینکه درباره مشکلات خودتان با ما تماس گرفتید...</p>
                                        <span class="time">6 دقیقه پیش</span>
                                    </div>
                                </li>
                                <li class="nk-support-item">
                                    <div class="user-avatar bg-purple-dim">
                                        <span>د‌م</span>
                                    </div>
                                    <div class="nk-support-content">
                                        <div class="title"><span>دانیال مدرس</span><span class="badge badge-dot badge-dot-xs bg-info ms-1">باز</span></div>
                                        <p>با تشکر از اینکه درباره مشکلات خودتان با ما تماس گرفتید...</p>
                                        <span class="time">2 ساعت پیش</span>
                                    </div>
                                </li>
                                <li class="nk-support-item">
                                    <div class="user-avatar">
                                        <img src="./images/avatar/b-sm.jpg" alt="" />
                                    </div>
                                    <div class="nk-support-content">
                                        <div class="title"><span>مهدی پیری</span><span class="badge badge-dot badge-dot-xs bg-success ms-1">حل شده</span></div>
                                        <p>با تشکر از اینکه درباره مشکلات خودتان با ما تماس گرفتید...</p>
                                        <span class="time">3 ساعت پیش</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- .card -->
                    </div>
                    <!-- .col -->
                    <div class="col-lg-6 col-xxl-4">
                        <div class="card card-bordered h-100">
                            <div class="card-inner border-bottom">
                                <div class="card-title-group">
                                    <div class="card-title">
                                        <h6 class="title">اطلاع رسانی ها</h6>
                                    </div>
                                    <div class="card-tools">
                                        <a href="#" class="link">مشاهده همه</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-inner">
                                <div class="timeline">
                                    <h6 class="timeline-head">آبان 1402</h6>
                                    <ul class="timeline-list">
                                        <li class="timeline-item">
                                            <div class="timeline-status bg-primary is-outline"></div>
                                            <div class="timeline-date">21 آبان <em class="icon ni ni-alarm-alt"></em></div>
                                            <div class="timeline-data">
                                                <h6 class="timeline-title">درخواست احراز هویت مشتری ارسال شد</h6>
                                                <div class="timeline-des">
                                                    <p>فرم درخواست احراز هویت مشتری دوباره ارسال شد.</p>
                                                    <span class="time">09:30 صبح</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="timeline-item">
                                            <div class="timeline-status bg-primary"></div>
                                            <div class="timeline-date">21 آبان <em class="icon ni ni-alarm-alt"></em></div>
                                            <div class="timeline-data">
                                                <h6 class="timeline-title">درخواست احراز هویت مشتری ارسال شد</h6>
                                                <div class="timeline-des">
                                                    <p>فرم درخواست احراز هویت مشتری دوباره ارسال شد.</p>
                                                    <span class="time">09:30 صبح</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="timeline-item">
                                            <div class="timeline-status bg-pink"></div>
                                            <div class="timeline-date">21 آبان <em class="icon ni ni-alarm-alt"></em></div>
                                            <div class="timeline-data">
                                                <h6 class="timeline-title">درخواست احراز هویت مشتری ارسال شد</h6>
                                                <div class="timeline-des">
                                                    <p>فرم درخواست احراز هویت مشتری دوباره ارسال شد.</p>
                                                    <span class="time">09:30 صبح</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- .card -->
                    </div>
                    <!-- .col -->
                </div>
                <!-- .row -->
            </div>
        </div>
    </div>
@endcomponent
