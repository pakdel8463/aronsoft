{{-- لیست مشترک بلاگ: $posts, $allCategories, $category (اختیاری), $childrenCategories (اختیاری) --}}
<section class="section" aria-label="مقالات">
    <div class="container-x grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-8">
            @if(isset($childrenCategories) && $childrenCategories->count())
                <nav class="mb-6 flex flex-wrap gap-2" aria-label="زیردسته‌ها">
                    <a href="{{ route('blog.category', $category->slug) }}" aria-current="page" class="chip-filter is-active">همه مطالب</a>
                    @foreach($childrenCategories as $child)<a href="{{ route('blog.category', $child->slug) }}" class="chip-filter">{{ $child->name }}</a>@endforeach
                </nav>
            @endif

            @if($posts->count())
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach($posts as $post)
                        @php $post->loadMissing('categories'); @endphp
                        @include('front.t-one.partials.post-card', ['post' => $post])
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @else
                <div class="card p-10 text-center">
                    <span class="icon-tile mx-auto mb-4"><i class="ph ph-article" aria-hidden="true"></i></span>
                    <h2 class="text-lg font-bold text-slate-900">هنوز مقاله‌ای منتشر نشده</h2>
                    <p class="mt-2 text-sm text-slate-600">به‌زودی مقالات جدید اضافه می‌شود.</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-secondary mt-5">بازگشت به وبلاگ</a>
                </div>
            @endif
        </div>

        <aside class="space-y-6 lg:col-span-4" aria-label="سایدبار">
            <div class="card p-5">
                <h2 class="mb-3 text-base font-bold text-slate-900">دسته‌بندی‌ها</h2>
                <ul class="divide-y divide-slate-100">
                    @foreach($allCategories as $c)
                        <li><a href="{{ route('blog.category', $c->slug) }}" @if(isset($category) && $category->id === $c->id) aria-current="page" @endif class="flex min-h-[44px] items-center justify-between gap-3 text-sm text-slate-700 transition-colors hover:text-tech-blue {{ (isset($category) && $category->id === $c->id) ? 'font-bold text-tech-blue' : '' }}"><span>{{ $c->name }}</span><span class="num rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{{ \App\Helpers\Fa::toFa($c->posts_count) }}</span></a></li>
                    @endforeach
                </ul>
            </div>
            @include('front.t-one.partials.newsletter')
        </aside>
    </div>
</section>
