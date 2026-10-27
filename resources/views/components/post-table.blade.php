@props(['posts','categories'])
<div class="nk-content-inner">
    <div class="nk-content-body">
        <div class="nk-block">
            <div class="row g-gs">
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
                                                <span class="d-none d-md-block">
                                                    <button class="btn btn-dim btn-outline-light disabled">اعمال</button>
                                                </span>
                                                <span class="d-md-none">
                                                    <button class="btn btn-dim btn-outline-light btn-icon disabled">
                                                        <em class="icon ni ni-arrow-right"></em>
                                                    </button>
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
                                            <input type="text" id="liveSearch" class="form-control border-transparent form-focus-none" placeholder="جستجو بر اساس نام نویسنده یا عنوان پست" />
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
                                    @foreach($posts as $post)
                                        <!-- .nk-tb-item -->
                                        <div class="nk-tb-item" id="{{$post->id}}">
                                            <div class="nk-tb-col nk-tb-col-check">
                                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                                    <input type="checkbox" class="custom-control-input" id="uid{{$post->id}}" />
                                                    <label class="custom-control-label" for="uid{{$post->id}}"></label>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col">
                                                <span>
                                                    {{ \Illuminate\Support\Str::limit($post->title, 60) }}
                                                </span>
                                            </div>
                                            <div class="nk-tb-col tb-col-mb">
                                                <a href="#">
                                                    <div class="user-card">
                                                        <div class="user-avatar sm bg-orange-dim">
                                                            <span>ف</span>
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead">{{$post->author->name}}</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="nk-tb-col tb-col-md">
                                                @foreach($post->categories as $cat)
                                                <span>{{$cat->name}}-</span>
                                                @endforeach
                                            </div>
                                            <div class="nk-tb-col tb-col-lg">
                                                <span>1 نظر</span>
                                            </div>
                                            <div class="nk-tb-col tb-col-xxl">
                                                <div>
                                                    @if($post->status == 'draft')
                                                        <span>پیش نویس</span>
                                                    @elseif($post->status == 'published')
                                                        <span> منتشر شده</span>
                                                    @else
                                                        <span>خصوصی</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span> {{ jdate($post->created_at)->format('d F Y') }}</span>
                                                </div>
                                            </div>
                                            <div class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li class="nk-tb-action-hidden">
                                                        <a class="btn btn-trigger btn-icon edit-modal" href="#editPost" data-bs-placement="top" title="ویرایش سریع"
                                                           data-id="{{ $post->id }}"
                                                           data-title="{{ $post->title }}"
                                                           data-slug="{{ $post->slug }}"
                                                           data-status="{{ $post->status }}"
                                                           data-created="{{jdate($post->created_at)}}"
                                                           data-cats="{{ $post->categories->pluck('id') }}"
                                                           data-tags="{{ $post->tags->pluck('name') }}"
                                                           data-comment="{{ $post->commentable ? 1 : 0 }}"
                                                           data-categories='@json($post->categories->pluck("id"))'
                                                           data-bs-toggle="modal" data-bs-target="#editPost"
                                                        >
                                                            <em class="icon ni ni-edit-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li class="nk-tb-action-hidden">
                                                        <a href="javascript:void(0)" onclick="deleteConfirm('{{ route('panel.posts.destroy', $post) }}', false)" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="انتقال به سطل زباله">
                                                            <em class="icon ni ni-trash-fill"></em>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="drodown">
                                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a  href="{{route('panel.posts.edit',$post)}}"><em  class="icon ni ni-edit-fill"></em><span>ویرایش پست</span></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="javascript:void(0)" onclick="deleteConfirm('{{ route('panel.posts.forceDelete', $post) }}', true)"><em class="icon ni ni-trash-fill"></em><span>حذف کامل</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- .nk-tb-list -->
                            </div>
                            <!-- .card-inner -->
                            {{ $posts->links('vendor.pagination.dashlite') }}
                            <!-- .card-inner -->
                        </div>
                        <!-- .card-inner-group -->
                    </div>
                    <!-- .card -->
                </div>
                <!-- .nk-block -->
            </div>
            <!-- .row -->
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="editPost">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <a href="#" class="close" data-bs-dismiss="modal">
                <em class="icon ni ni-cross-sm"></em>
            </a>
            <div class="modal-body modal-body-md">
                <h5 class="modal-title">ویرایش سریع</h5>
                <form action="#" class="mt-4" id="modal-form">
                    <div class="row g-gs">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="editTitle">عنوان</label>
                                <input type="text" class="form-control" id="editTitle" placeholder="عنوان" value="" />
                            </div>
                        </div>
                        <!-- .col -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="editSlug">نامک</label>
                                <input type="text" class="form-control" id="editSlug" placeholder="نامک" value="hello-world" />
                            </div>
                        </div>
                        <!-- .col -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">تاریخ</label>
                                <div class="form-control-wrap">
                                    <div class="form-icon form-icon-right">
                                        <em class="icon ni ni-calendar"></em>
                                    </div>
                                    <input type="text" class="form-control date-picker" placeholder="روز/ماه/سال"  />
                                </div>
                            </div>
                        </div>
                        <!-- .col -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="categories">دسته بندی</label>
                                <div class="form-control-wrap">
                                    <select class="form-select js-select2" id="quickCategories" multiple="multiple" data-placeholder="دسته بندی ها">
                                        <option value="uncategorized">دسته بندی نشده</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- .col -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="editTags">برچسب</label>
                                <input type="text" class="form-control" id="editTags" placeholder="برچسب ها" value="—" />
                            </div>
                        </div>
                        <!-- .col -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">وضعیت</label>
                                <div class="form-control-wrap">
                                    <select id="statusSelect" class="form-select js-select2" data-placeholder="وضعیت">
                                        <option value="draft">پیش نویس</option>
                                        <option value="published">منتشر شده</option>
                                        <option value="private">خصوصی</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- .col -->
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="editComment" />
                                <label class="custom-control-label" for="editComment">مجوز نظرات</label>
                            </div>
                        </div>
                        <!-- .col -->
                        <!-- .col -->
                        <div class="col-12">
                            <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                <li>
                                    <button type="submit"  class="btn btn-primary">به روز رسانی</button>
                                </li>
                                <li>
                                    <a href="#" class="link link-light" data-bs-dismiss="modal">لغو</a>
                                </li>
                            </ul>
                        </div>
                        <!-- .col -->
                    </div>
                    <!-- .row -->
                    <input type="hidden" id="editCreatedAt" name="created_at">
                </form>
            </div>
            <!-- .modal-body -->
        </div>
        <!-- .modal-content -->
    </div>
    <!-- .modal-dialog -->
</div>
@slot('btnscript')
    <script src="{{asset('admin/dash/assets/js/jdate.js')}}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let modalEl = document.getElementById("editPost");
            let formEl  = modalEl.querySelector("form");

            // ───────────────────────────────────────────────
            // مقداردهی مودال هنگام باز شدن
            // ───────────────────────────────────────────────
            modalEl.addEventListener("show.bs.modal", function (event) {

                let button = event.relatedTarget;

                let id      = button.getAttribute("data-id");
                let title   = button.getAttribute("data-title");
                let slug    = button.getAttribute("data-slug");
                let status  = button.getAttribute("data-status");
                let created = button.getAttribute("data-created");
                let dateInput = modalEl.querySelector(".date-picker");
                let hiddenDate = modalEl.querySelector("#editCreatedAt");

                // فیلدهای ساده
                modalEl.querySelector("#editTitle").value = title;
                modalEl.querySelector("#editSlug").value  = slug;
                modalEl.querySelector(".date-picker").value = created;


                dateInput.dataset.originalValue = created;
                dateInput.value = created;
                hiddenDate.value = created;

                dateInput.addEventListener('blur', function () {
                    // اگر کاربر انتخاب نکرد و پلاگین مقدار امروز گذاشت،
                    // ما مانع می‌شویم و مقدار اصلی را بازمی‌گردانیم
                    let original = this.dataset.originalValue;

                    // اگر کاربر تاریخ جدید انتخاب نکرد → مقدار را برگردان
                    if (!this.dataset.userSelected) {
                        this.value = original;
                    }

                    // ریست
                    delete this.dataset.userSelected;
                });

                $(".date-picker").on("change", function () {
                    document.getElementById("editCreatedAt").value = this.value;
                });
                // ───── دسته‌بندی‌ها ─────
                let categories = JSON.parse(button.getAttribute("data-categories"));
                let selectCats = document.getElementById("quickCategories");

                [...selectCats.options].forEach(o => o.selected = false);
                categories.forEach(cid => {
                    let opt = selectCats.querySelector(`option[value="${cid}"]`);
                    if (opt) opt.selected = true;
                });
                if ($(selectCats).hasClass("js-select2")) {
                    $(selectCats).trigger("change");
                }

                // ───── وضعیت (status) ─────
                let statusSelect = modalEl.querySelector("#statusSelect");
                statusSelect.value = status;
                if ($(statusSelect).hasClass("js-select2")) {
                    $(statusSelect).val(status).trigger("change");
                }

                // ───── تگ‌ها ─────
                let tags = JSON.parse(button.getAttribute("data-tags"));
                modalEl.querySelector("#editTags").value = tags.join(", ");

                // ───── مجوز نظرات ─────
                let allowComment = button.getAttribute("data-comment");
                modalEl.querySelector("#editComment").checked = (allowComment === "1");

                // ذخیره ID پست
                formEl.dataset.postId = id;
            });


            // ───────────────────────────────────────────────
            // ارسال Ajax هنگام ذخیره
            // ───────────────────────────────────────────────
            formEl.addEventListener("submit", function (e) {
                e.preventDefault();
                console.log(123);
                let postId = this.dataset.postId;

                let formData = new FormData();
                formData.append("id", postId);
                formData.append("title", document.getElementById("editTitle").value);
                formData.append("slug", document.getElementById("editSlug").value);
                formData.append("status", document.getElementById("statusSelect").value);
                formData.append("created_at", document.getElementById("editCreatedAt").value)

                // دسته‌بندی‌ها
                let selectCats = document.getElementById("quickCategories");
                let selectedCats = [...selectCats.selectedOptions].map(o => o.value);
                selectedCats.forEach(c => formData.append("categories[]", c));

                // تگ‌ها
                formData.append("tags", document.getElementById("editTags").value);

                // چک‌باکس
                formData.append("commentable", document.getElementById("editComment").checked ? 1 : 0);

                // ───── ارسال درخواست Ajax به Laravel ─────
                fetch("{{ route('panel.posts.quickUpdate') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {

                        if (data.status === "success") {

                            // SweetAlert
                            Swal.fire({
                                icon: "success",
                                title: "با موفقیت ذخیره شد",
                                text: data.message,
                                timer: 1800,
                                showConfirmButton: false
                            });

                            // ───────────────────────────────────────────
                            // آپدیت ردیف جدول بدون رفرش
                            // ───────────────────────────────────────────
                            let row = document.getElementById(postId);

                            // عنوان
                            row.querySelector(".nk-tb-col span").innerText = data.updated.title;

                            // وضعیت (در صورتی که ستونش هست)
                            let statusDiv = row.querySelector(".post-status");
                            if (statusDiv) {
                                if (data.updated.status === "draft") {
                                    statusDiv.innerText = "پیش‌نویس";
                                } else if (data.updated.status === "published") {
                                    statusDiv.innerText = "منتشر شده";
                                } else {
                                    statusDiv.innerText = "خصوصی";
                                }
                            }

                            // تاریخ
                            let dateDiv = row.querySelector(".post-date");
                            if (dateDiv) {
                                dateDiv.innerText = data.updated.created_at;
                            }

                            // بستن مودال
                            let modalInstance = bootstrap.Modal.getInstance(modalEl);
                            modalInstance.hide();
                        }
                    });
            });

        });
    </script>
    <script>
        window.deleteConfirm = function (url, isForceDelete = false) {

            let title = isForceDelete
                ? "حذف دائمی"
                : "حذف (قابل بازیابی)";

            let message = isForceDelete
                ? "این حذف دائمی است و امکان بازگشت ندارد. مطمئن هستید؟"
                : "این مورد به سطل زباله منتقل می‌شود و قابل بازیابی است.";

            let confirmText = isForceDelete
                ? "بله، برای همیشه حذف کن"
                : "بله، حذف کن";

            Swal.fire({
                title: title,
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: "لغو",
                confirmButtonColor: isForceDelete ? "#d33" : "#3085d6"
            }).then((result) => {
                if (result.isConfirmed) {

                    let form = document.createElement("form");
                    form.method = "POST";
                    form.action = url;

                    let csrf = document.createElement("input");
                    csrf.type = "hidden";
                    csrf.name = "_token";
                    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

                    let method = document.createElement("input");
                    method.type = "hidden";
                    method.name = "_method";
                    method.value = "DELETE";

                    form.appendChild(csrf);
                    form.appendChild(method);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }


    </script>
    <script>
        let timer = null;

        document.getElementById("liveSearch").addEventListener("keyup", function () {
            clearTimeout(timer);

            const searchValue = this.value.trim();

            timer = setTimeout(() => {

                // اگر کمتر از ۳ حرف بود، سرچ نکن
                if (searchValue.length < 3) {
                    return; // اگر خواستی اینجا جدول اصلی هم رندر کنم بگو
                }

                fetchLiveSearch(searchValue);

            }, 350);
        });


        function fetchLiveSearch(search) {

            fetch("{{ route('panel.posts.liveSearch') }}?search=" + encodeURIComponent(search), {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
                .then(res => res.json())
                .then(data => {
                    document.querySelector("#postsTableBody").innerHTML = data.html;
                });
        }

    </script>





@endslot
