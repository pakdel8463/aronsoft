@php
    use App\View\Seo;
    Seo::meta([
        'title' => 'طراحی سایت، فروشگاه اینترنتی و توسعه نرم‌افزار اختصاصی',
        'description' => 'آرون سافت؛ طراحی سایت و فروشگاه اینترنتی، توسعه نرم‌افزار اختصاصی با لاراول، وردپرس پیشرفته، DevOps و سئو. مشاوره رایگان و پیش‌فاکتور رسمی.',
    ]);

    $services = [
        ['ph-browser', 'طراحی سایت', 'سایت شرکتی، معرفی خدمات و لندینگ؛ سریع، ریسپانسیو و آماده‌ی سئو.', url('/order?type=website'), 'ثبت سفارش'],
        ['ph-shopping-cart', 'فروشگاه اینترنتی', 'فروشگاه کالا یا محصول دیجیتال با درگاه پرداخت، انبارداری و پنل مدیریت.', url('/order?type=shop'), 'ثبت سفارش'],
        ['ph-code', 'توسعه نرم‌افزار', 'سامانه‌ها و وب‌اپلیکیشن‌های اختصاصی با Laravel و Next.js برای عملکرد بالا.', url('/services/web-development'), 'مشاهده جزئیات'],
        ['wordpress', 'وردپرس پیشرفته', 'طراحی قالب اختصاصی، پلاگین‌نویسی و بهینه‌سازی حرفه‌ای ووکامرس.', url('/services/wordpress-website-design'), 'مشاهده جزئیات'],
        ['ph-hard-drives', 'خدمات DevOps', 'راه‌اندازی سرور، داکر، CI/CD و تامین امنیت زیرساخت‌های نرم‌افزاری شما.', url('/services/devops-services'), 'مشاهده جزئیات'],
        ['ph-magnifying-glass', 'سئو و محتوا', 'سئوی فنی، تحقیق کلمات کلیدی و استراتژی محتوا برای دیده‌شدن در گوگل.', url('/services/seo'), 'مشاهده جزئیات'],
    ];
    $techs = [
        ['ph-brackets-curly', 'Laravel'], ['ph-lightning', 'Next.js'], ['cube', 'Docker'], ['wordpress', 'WordPress'],
        ['ph-device-mobile', 'React Native'], ['ph-terminal-window', 'Linux'], ['ph-database', 'MySQL'],
    ];
    $courses = [
        ['ph-atom', 'دوره جامع ری‌اکت', '۴۰ ساعت آموزش پروژه‌محور', 'ثبت‌نام زودهنگام'],
        ['ph-cube', 'دوره جامع مسترینگ داکر', '۱۲ ساعت؛ از کانتینر تا Dockerfile امنیتی', 'تکمیل‌شده'],
        ['ph-git-branch', 'ورک‌شاپ CI/CD با GitLab', 'سناریوهای واقعی پایپ‌لاین‌نویسی', 'به‌زودی'],
    ];
@endphp

@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd([Seo::organization(), Seo::website()]) !!}
    @endslot

    {{-- ============ HERO ============ --}}
    <section class="relative overflow-hidden" aria-labelledby="hero-title">
        <div class="hero-glow pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 text-slate-300/60" aria-hidden="true"></div>
        <div class="container-x relative grid items-center gap-10 py-12 md:py-16 lg:grid-cols-12 lg:gap-8 lg:py-20">
            <div class="lg:col-span-6">
                <span class="badge mb-5"><span class="h-1.5 w-1.5 rounded-full bg-tech-blue" aria-hidden="true"></span> پذیرش پروژه جدید فعال است</span>
                <h1 id="hero-title" class="h1-page">مهندسیِ <span class="text-tech-blue">آینده دیجیتال</span> برای کسب‌وکارهای پیشرو</h1>
                <p class="lead mt-4 max-w-xl">ما در آرون‌سافت، ایده‌های پیچیده را به نرم‌افزارهای قدرتمند تبدیل می‌کنیم؛ از توسعه وب اختصاصی تا زیرساخت‌های DevOps مقیاس‌پذیر.</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/order') }}" class="btn btn-primary btn-lg">ثبت سفارش پروژه <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                    <a href="{{ route('portfolio.index') }}" class="btn btn-secondary btn-lg">مشاهده نمونه‌کارها</a>
                </div>
                <ul class="mt-7 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-600">
                    <li class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-lg text-tech-blue" aria-hidden="true"></i> مشاوره رایگان</li>
                    <li class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-lg text-tech-blue" aria-hidden="true"></i> پیش‌فاکتور رسمی</li>
                    <li class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-lg text-tech-blue" aria-hidden="true"></i> پشتیبانی پس از تحویل</li>
                </ul>
            </div>

            {{-- محصول نمایشی (HTML/CSS) --}}
            <div class="relative lg:col-span-6" aria-hidden="true" dir="ltr">
                <div class="mock-window mx-auto max-w-[34rem]">
                    <div class="mock-bar">
                        <i></i><i></i><i></i>
                        <span class="ms-3 flex-1 truncate rounded-md bg-white px-3 py-1 text-[11px] text-slate-400">app.your-business.com/dashboard</span>
                    </div>
                    <div class="grid grid-cols-[3.25rem_1fr] sm:grid-cols-[9rem_1fr]">
                        <div class="space-y-2 border-e border-slate-100 bg-slate-50/70 p-3">
                            @foreach(['ph-squares-four', 'ph-shopping-bag', 'ph-users', 'ph-chart-line-up', 'ph-gear'] as $k => $ic)
                                <div class="flex items-center gap-2 rounded-lg px-2 py-2 {{ $k === 0 ? 'bg-brand-50 text-tech-blue' : 'text-slate-400' }}">
                                    <i class="ph {{ $ic }} text-base"></i><span class="hidden h-2 flex-1 rounded bg-current opacity-30 sm:block"></span>
                                </div>
                            @endforeach
                        </div>
                        <div class="space-y-3 p-4">
                            <div class="grid grid-cols-3 gap-2.5">
                                @foreach([['ph-receipt', 'bg-brand-50 text-tech-blue'], ['ph-package', 'bg-emerald-50 text-emerald-600'], ['ph-lightning', 'bg-amber-50 text-amber-600']] as [$ic, $cl])
                                    <div class="rounded-xl border border-slate-100 p-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $cl }}"><i class="ph {{ $ic }}"></i></span>
                                        <span class="mt-2.5 block h-2.5 w-3/4 rounded bg-slate-200"></span>
                                        <span class="mt-1.5 block h-2 w-1/2 rounded bg-slate-100"></span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="rounded-xl border border-slate-100 p-3">
                                <div class="mb-2 flex items-center justify-between"><span class="h-2.5 w-24 rounded bg-slate-200"></span><span class="chip chip-blue !text-[10px]">Live</span></div>
                                <div class="flex h-24 items-end gap-1.5">
                                    @foreach([38, 52, 44, 66, 58, 78, 70, 92, 84, 100] as $h)
                                        <span class="flex-1 rounded-t bg-gradient-to-t from-tech-blue/80 to-tech-blue/30" style="height: {{ $h }}%"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- کارت‌های شناور: دو مورد (نه بیشتر) --}}
                <div class="absolute -bottom-5 -start-1 hidden items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-card sm:flex">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i class="ph-bold ph-check text-base"></i></span>
                    <span class="text-start"><span class="block text-[11px] font-bold text-slate-800">Deploy succeeded</span><span class="block text-[10px] text-slate-400">CI/CD pipeline</span></span>
                </div>
                <div class="absolute -top-4 -end-1 hidden items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-card sm:flex">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-tech-blue"><i class="ph ph-shield-check text-base"></i></span>
                    <span class="text-start"><span class="block text-[11px] font-bold text-slate-800">Secure by design</span><span class="block text-[10px] text-slate-400">OWASP checklist</span></span>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ TECH STRIP ============ --}}
    <section class="border-y border-slate-200 bg-slate-50" aria-label="تکنولوژی‌های مورد استفاده">
        <div class="container-x py-7">
            <p class="mb-4 text-center text-xs font-bold text-slate-500">تکنولوژی‌هایی که روزانه با آن‌ها کار می‌کنیم</p>
            <ul class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3" dir="ltr">
                @foreach($techs as [$ic, $name])
                    <li class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-slate-700">
                        @if($ic === 'wordpress')
                            <svg class="h-[18px] w-[18px] text-tech-blue" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM3.2 12c0-1.2.26-2.35.72-3.38l3.97 10.87A8.8 8.8 0 0 1 3.2 12zm8.8 8.8c-.9 0-1.76-.13-2.58-.38l2.74-7.96 2.8 7.67.02.04a8.8 8.8 0 0 1-3 .63zm1.22-12.9c.55-.03 1.04-.09 1.04-.09.49-.06.43-.78-.06-.75 0 0-1.47.12-2.42.12-.89 0-2.39-.12-2.39-.12-.49-.03-.55.72-.06.75 0 0 .46.06.95.09l1.41 3.87-1.98 5.94L6.4 7.9c.55-.03 1.04-.09 1.04-.09.49-.06.43-.78-.06-.75 0 0-1.47.12-2.42.12h-.57A8.8 8.8 0 0 1 12 3.2c2.3 0 4.4.88 5.97 2.32-.04 0-.08-.01-.12-.01-.89 0-1.52.77-1.52 1.6 0 .74.43 1.37.88 2.11.34.6.74 1.37.74 2.48 0 .77-.3 1.66-.68 2.9l-.89 2.97-3.23-9.6zm4.2 11.5l2.73-7.9c.51-1.27.68-2.29.68-3.2 0-.33-.02-.63-.06-.91A8.8 8.8 0 0 1 20.8 12a8.8 8.8 0 0 1-3.38 6.9z"/></svg>
                        @elseif($ic === 'cube')
                            <i class="ph ph-cube text-lg text-tech-blue" aria-hidden="true"></i>
                        @else
                            <i class="ph {{ $ic }} text-lg text-tech-blue" aria-hidden="true"></i>
                        @endif
                        {{ $name }}
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============ SERVICES ============ --}}
    <section id="services" class="section" aria-labelledby="services-title">
        <div class="container-x">
            <div class="section-head reveal">
                <span class="eyebrow">خدمات</span>
                <h2 id="services-title" class="h2-section">راهکارهای جامع برای رشد دیجیتال کسب‌وکار شما</h2>
                <p class="lead mt-3">از ایده تا اجرا و پشتیبانی، همه‌ی مراحل را یک تیم انجام می‌دهد.</p>
            </div>
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($services as [$ic, $title, $desc, $href, $cta])
                    <li class="reveal">
                        <article class="group card card-hover relative flex h-full flex-col p-6">
                            <span class="icon-tile mb-4">
                                @if($ic === 'wordpress')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM3.2 12c0-1.2.26-2.35.72-3.38l3.97 10.87A8.8 8.8 0 0 1 3.2 12zm8.8 8.8c-.9 0-1.76-.13-2.58-.38l2.74-7.96 2.8 7.67.02.04a8.8 8.8 0 0 1-3 .63zm1.22-12.9c.55-.03 1.04-.09 1.04-.09.49-.06.43-.78-.06-.75 0 0-1.47.12-2.42.12-.89 0-2.39-.12-2.39-.12-.49-.03-.55.72-.06.75 0 0 .46.06.95.09l1.41 3.87-1.98 5.94L6.4 7.9c.55-.03 1.04-.09 1.04-.09.49-.06.43-.78-.06-.75 0 0-1.47.12-2.42.12h-.57A8.8 8.8 0 0 1 12 3.2c2.3 0 4.4.88 5.97 2.32-.04 0-.08-.01-.12-.01-.89 0-1.52.77-1.52 1.6 0 .74.43 1.37.88 2.11.34.6.74 1.37.74 2.48 0 .77-.3 1.66-.68 2.9l-.89 2.97-3.23-9.6zm4.2 11.5l2.73-7.9c.51-1.27.68-2.29.68-3.2 0-.33-.02-.63-.06-.91A8.8 8.8 0 0 1 20.8 12a8.8 8.8 0 0 1-3.38 6.9z"/></svg>
                                @else
                                    <i class="ph {{ $ic }}" aria-hidden="true"></i>
                                @endif
                            </span>
                            <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-7 text-slate-600">{{ $desc }}</p>
                            <a href="{{ $href }}" class="link-arrow mt-3 after:absolute after:inset-0 after:content-['']">{{ $cta }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                        </article>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============ PROJECTS ============ --}}
    <section id="portfolio" class="section section-alt" aria-labelledby="portfolio-title">
        <div class="container-x">
            <div class="mb-8 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <span class="eyebrow">نمونه‌کارها</span>
                    <h2 id="portfolio-title" class="h2-section">پروژه‌های منتخب</h2>
                    <p class="mt-2 text-slate-600">ترکیبی از هنر طراحی و مهندسی نرم‌افزار.</p>
                </div>
                <a href="{{ route('portfolio.index') }}" class="link-arrow">مشاهده همه پروژه‌ها <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
            </div>
            @if($latestProjects->isEmpty())
                <div class="card p-10 text-center text-slate-600"><i class="ph ph-folder-open mb-3 block text-4xl text-slate-300" aria-hidden="true"></i>به‌زودی نمونه‌کارها در این بخش نمایش داده می‌شود.</div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestProjects as $project)
                        <div class="reveal">@include('front.t-one.partials.portfolio-card', ['p' => $project])</div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ============ ACADEMY ============ --}}
    <section class="section" aria-labelledby="academy-title">
        <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
            <div class="reveal">
                <span class="eyebrow">آکادمی آرون سافت</span>
                <h2 id="academy-title" class="h2-section">فقط پروژه نگیرید، متخصص شوید.</h2>
                <p class="lead mt-4">دوره‌های آموزشی ما دقیقاً همان مباحثی هستند که تیم فنی ما در پروژه‌های واقعی استفاده می‌کند؛ بدون حاشیه و کاملاً پروژه‌محور.</p>
                <ul class="check-list mt-5 space-y-1">
                    <li>سرفصل‌های برگرفته از پروژه‌های واقعی</li>
                    <li>تمرین عملی و رفع اشکال در حین دوره</li>
                    <li>مناسب توسعه‌دهندگان و مدیران فنی</li>
                </ul>
                <a href="{{ url('/contact') }}" class="btn btn-dark btn-lg mt-7"><i class="ph ph-student text-lg" aria-hidden="true"></i> اطلاع از زمان دوره‌ها</a>
            </div>
            <ul class="grid gap-3.5">
                @foreach($courses as [$ic, $title, $meta, $status])
                    <li class="reveal">
                        <div class="card flex items-center gap-4 p-4 sm:p-5">
                            <span class="icon-tile"><i class="ph {{ $ic }}" aria-hidden="true"></i></span>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-[0.9375rem] font-bold text-slate-900">{{ $title }}</h3>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $meta }}</p>
                            </div>
                            <span class="chip {{ $status === 'تکمیل‌شده' ? 'bg-emerald-50 text-emerald-700' : 'chip-blue' }} shrink-0">{{ $status }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============ ARTICLES ============ --}}
    @if($latestPosts->isNotEmpty())
        <section class="section section-alt" aria-labelledby="posts-title">
            <div class="container-x">
                <div class="mb-8 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <span class="eyebrow">وبلاگ</span>
                        <h2 id="posts-title" class="h2-section">آخرین مقالات</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="link-arrow">همه مقالات <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                </div>
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestPosts as $post)
                        <div class="reveal">@include('front.t-one.partials.post-card', ['post' => $post])</div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ CTA ============ --}}
    <section id="consultation" class="section" aria-labelledby="cta-title">
        <div class="container-x">
            <div class="grid items-center gap-8 overflow-hidden rounded-3xl bg-slate-900 p-6 sm:p-10 lg:grid-cols-2 lg:gap-12 lg:p-14">
                <div class="text-center lg:text-start">
                    <h2 id="cta-title" class="h2-section !text-white">آماده شروع هستید؟</h2>
                    <p class="mt-3 leading-8 text-slate-300">پروژه‌تان را ثبت کنید تا پیش‌فاکتور و زمان‌بندی اولیه را برایتان بفرستیم، یا فقط شماره‌تان را بگذارید تا کارشناس ما تماس بگیرد.</p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
                        <a href="{{ url('/order') }}" class="btn btn-lg bg-white text-slate-900 hover:bg-slate-100">ثبت سفارش پروژه <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                        <a href="{{ url('/contact') }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">تماس با ما</a>
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-5 sm:p-6">
                    <h3 class="mb-1 text-base font-bold text-slate-900">درخواست مشاوره رایگان</h3>
                    <p class="mb-4 text-sm text-slate-600">شماره موبایل را وارد کنید؛ در اولین فرصت تماس می‌گیریم.</p>
                    @include('front.t-one.partials.lead-form', ['page' => 'home', 'type' => 'مشاوره رایگان (صفحه اصلی)', 'cta' => 'درخواست مشاوره', 'withName' => false])
                </div>
            </div>
        </div>
    </section>
@endcomponent
