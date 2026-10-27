@php
    use App\View\Seo;
    use App\Helpers\Fa;
    use Illuminate\Support\Carbon;
    $img = Seo::img($post->featured_image);
    $date = Carbon::parse($post->published_at ?? $post->created_at);
    $words = max(1, str_word_count(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', strip_tags((string) $post->body)) ?: '', 0) ?: count(preg_split('/\s+/u', trim(strip_tags((string) $post->body)))));
    $minutes = max(1, (int) ceil($words / 200));
    $authorName = optional($post->author)->name ?: 'تیم آرون سافت';
    $crumbs = [['خانه', url('/')], ['وبلاگ', route('blog.index')]];
    if ($category) { $crumbs[] = [$category->name, route('blog.category', $category->slug)]; }
    $crumbs[] = [$post->title, null];
    $comments = $post->comments()->whereNull('parent_id')->approved()->with(['replies' => fn ($q) => $q->approved()])->get();
    $shareUrl = urlencode(url()->current());
    $shareText = urlencode($post->title);
    Seo::meta(['type' => 'article', 'image' => $img]);
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd([Seo::breadcrumbs(collect($crumbs)->map(fn ($c) => [$c[0], $c[1] ?: url()->current()])->all()), Seo::article($post)]) !!}
        <meta property="article:published_time" content="{{ $date->toIso8601String() }}">
    @endslot

    <header class="border-b border-slate-100 bg-gradient-to-b from-brand-50/60 to-white">
        <div class="container-x py-10 md:py-14">
            @include('front.t-one.partials.breadcrumb', ['items' => $crumbs])
            <div class="max-w-3xl">
                @if($category)<a href="{{ route('blog.category', $category->slug) }}" class="badge mb-3">{{ $category->name }}</a>@endif
                <h1 class="h1-page" dir="auto">{{ $post->title }}</h1>
                <p class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                    <span class="flex items-center gap-1.5"><i class="ph ph-user-circle text-lg text-slate-400" aria-hidden="true"></i>{{ $authorName }}</span>
                    <time datetime="{{ $date->toDateString() }}" class="flex items-center gap-1.5"><i class="ph ph-calendar-blank text-lg text-slate-400" aria-hidden="true"></i>{{ Fa::toFa(jdate($date)->format('%d %B %Y')) }}</time>
                    <span class="flex items-center gap-1.5"><i class="ph ph-clock text-lg text-slate-400" aria-hidden="true"></i>{{ Fa::toFa($minutes) }} دقیقه مطالعه</span>
                </p>
            </div>
        </div>
    </header>

    <div class="section">
        <div class="container-x grid gap-10 lg:grid-cols-12">
            <article class="min-w-0 lg:col-span-8">
                @if($img)
                    <img src="{{ $img }}" alt="{{ $post->alt ?: $post->title }}" width="1200" height="675" fetchpriority="high" class="mb-8 aspect-video w-full rounded-2xl border border-slate-200 object-cover">
                @endif

                <div class="rich" data-post-body>{!! $post->body !!}</div>

                <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    @if($post->tags->count())
                        <ul class="flex flex-wrap gap-2" aria-label="برچسب‌ها">@foreach($post->tags as $tag)<li class="chip">#{{ $tag->name }}</li>@endforeach</ul>
                    @else<span></span>@endif
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-500">اشتراک‌گذاری</span>
                        <a class="share-btn" href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" aria-label="اشتراک در تلگرام"><i class="ph ph-telegram-logo" aria-hidden="true"></i></a>
                        <a class="share-btn" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="اشتراک در لینکدین"><i class="ph ph-linkedin-logo" aria-hidden="true"></i></a>
                        <a class="share-btn" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" aria-label="اشتراک در ایکس"><i class="ph ph-x-logo" aria-hidden="true"></i></a>
                    </div>
                </div>

                {{-- دیدگاه‌ها --}}
                @if($post->commentable === null || $post->commentable)
                    <section id="comments-section" class="mt-12" aria-labelledby="comments-title">
                        <h2 id="comments-title" class="h2-section mb-6 flex items-center gap-2"><i class="ph ph-chat-centered-text text-tech-blue" aria-hidden="true"></i> دیدگاه‌ها</h2>

                        <div class="card mb-8 p-5 sm:p-6">
                            <div data-reply-indicator hidden class="mb-4 flex items-center justify-between rounded-lg bg-brand-50 px-3 py-2 text-sm text-tech-blue">
                                <span>پاسخ به <b data-reply-name></b></span>
                                <button type="button" data-reply-cancel class="min-h-[44px] px-2 text-xs font-bold text-red-600 hover:underline">لغو پاسخ</button>
                            </div>
                            <form id="comment-form" method="POST" action="{{ route('comments.store', $post) }}" data-ajax-form data-hide-on-success novalidate class="space-y-3">
                                @csrf
                                <input type="hidden" name="parent_id" value="">
                                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                                <div data-form-fields class="space-y-3">
                                    @guest
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div><label for="c-name" class="form-label">نام <span class="text-red-600" aria-hidden="true">*</span></label><input id="c-name" type="text" name="name" required class="form-input" autocomplete="name" maxlength="120"></div>
                                            <div><label for="c-email" class="form-label">ایمیل (نمایش داده نمی‌شود) <span class="text-red-600" aria-hidden="true">*</span></label><input id="c-email" type="email" name="email" required class="form-input text-left" dir="ltr" autocomplete="email"></div>
                                        </div>
                                    @endguest
                                    <div><label for="c-body" class="form-label">دیدگاه شما <span class="text-red-600" aria-hidden="true">*</span></label><textarea id="c-body" name="body" rows="4" required minlength="5" maxlength="3000" class="form-input"></textarea></div>
                                    @include('aronlabs-captcha::captcha-input')
                                    <button type="submit" class="btn btn-primary"><i class="ph ph-paper-plane-right" aria-hidden="true"></i><span>ثبت دیدگاه</span></button>
                                </div>
                                <p data-form-msg class="form-msg" hidden aria-live="polite"></p>
                            </form>
                        </div>

                        @if($comments->count())
                            <ul class="space-y-4">
                                @foreach($comments as $comment)
                                    <li class="card p-5">
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 font-bold text-tech-blue" aria-hidden="true">{{ mb_substr($comment->name, 0, 1) }}</span>
                                                <div><h3 class="text-sm font-bold text-slate-900" dir="auto">{{ $comment->name }}</h3><span class="text-xs text-slate-500">{{ Fa::toFa(jdate($comment->created_at)->ago()) }}</span></div>
                                            </div>
                                            <button type="button" data-reply-id="{{ $comment->id }}" data-reply-name="{{ $comment->name }}" class="btn btn-soft btn-sm">پاسخ</button>
                                        </div>
                                        <p class="whitespace-pre-line text-sm leading-8 text-slate-700" dir="auto">{{ $comment->body }}</p>
                                        @if($comment->replies->count())
                                            <ul class="mt-4 space-y-3 border-s-2 border-slate-100 ps-4">
                                                @foreach($comment->replies as $reply)
                                                    <li class="rounded-xl bg-slate-50 p-4"><h4 class="text-sm font-bold text-slate-900" dir="auto">{{ $reply->name }} <span class="text-xs font-normal text-slate-500">· {{ Fa::toFa(jdate($reply->created_at)->ago()) }}</span></h4><p class="mt-1 whitespace-pre-line text-sm leading-7 text-slate-700" dir="auto">{{ $reply->body }}</p></li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">هنوز دیدگاهی ثبت نشده؛ اولین نفر باشید.</p>
                        @endif
                    </section>
                @endif
            </article>

            <aside class="space-y-6 lg:col-span-4" aria-label="کنار مقاله">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <nav data-toc hidden class="card p-5" aria-label="فهرست مطالب">
                        <h2 class="mb-3 flex items-center gap-2 text-base font-bold text-slate-900"><i class="ph ph-list-bullets text-tech-blue" aria-hidden="true"></i> فهرست مطالب</h2>
                        <ul data-toc-list></ul>
                    </nav>
                    @include('front.t-one.partials.newsletter')
                </div>
            </aside>
        </div>
    </div>

    @include('front.t-one.partials.cta-band', ['title' => 'برای پروژه‌تان مشاوره می‌خواهید؟', 'text' => 'تجربه‌ی این مقاله را در پروژه‌ی شما هم به کار می‌گیریم؛ نیازتان را ثبت کنید.'])
@endcomponent
