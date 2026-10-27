@php
    $exists = $post->exists;
    $statusValue = old('status', $exists ? $post->display_status : 'draft');
    $publishedAt = old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '');
    $tagList = old('tags', $tagNames);
    $tagList = is_array($tagList) ? $tagList : array_filter(explode(',', (string) $tagList));
    $catSel = array_map('intval', (array) old('categories', $selectedCategories));
    $action = $exists ? route('panel.posts.update', $post->id) : route('panel.posts.store');
    $siteHost = parse_url(url('/'), PHP_URL_HOST) ?: 'example.com';
    $shortcuts = [];
@endphp
<x-admin.layout :title="$exists ? 'ویرایش نوشته' : 'نوشته جدید'"
                :subtitle="$exists ? $post->title : 'محتوا را بنویسید، تنظیمات انتشار و سئو را کامل کنید.'"
                :breadcrumbs="[['label' => 'نوشته‌ها', 'url' => route('panel.posts.index')], ['label' => $exists ? 'ویرایش' : 'نوشته جدید']]"
                width="max-w-[1500px]">
    <x-slot:actions>
        @if ($exists)
            <x-admin.button :href="$post->publicUrl()" target="_blank" rel="noopener" variant="secondary" size="sm" icon="arrow-square-out">{{ $post->isLive() ? 'مشاهده در سایت' : 'پیش‌نمایش' }}</x-admin.button>
            <form method="POST" action="{{ route('panel.posts.duplicate', $post->id) }}" class="inline-flex">@csrf
                <x-admin.button type="submit" variant="secondary" size="sm" icon="copy-simple">تکثیر</x-admin.button>
            </form>
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ $action }}" id="post-form" data-post-form novalidate
          data-id="{{ $post->id ?? '' }}" data-updated="{{ $exists ? $post->updated_at?->timestamp : 0 }}"
          data-slug-url="{{ route('panel.posts.slugCheck') }}" data-cat-url="{{ route('panel.categories.ajaxStore') }}"
          data-tag-url="{{ route('panel.tags.suggest') }}" data-site="{{ $siteHost }}" data-blog-base="{{ rtrim(route('blog.index'), '/') }}"
          data-rev-url="{{ $exists ? route('panel.posts.revision', [$post->id, '__ID__']) : '' }}">
        @csrf
        @if ($exists) @method('PUT') @endif

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_21.5rem]">
            {{-- ================= ستون اصلی ================= --}}
            <div class="min-w-0 space-y-5">
                <div id="local-draft-banner" class="hidden items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <span class="flex items-center gap-2 font-bold"><i class="ph ph-clock-counter-clockwise text-lg"></i><span data-banner-text>یک پیش‌نویس ذخیره‌نشده‌ی محلی یافت شد.</span></span>
                    <span class="flex gap-2"><button type="button" data-draft-restore class="a-btn a-btn-sm bg-amber-500 text-white hover:bg-amber-600">بازیابی</button><button type="button" data-draft-discard class="a-btn a-btn-ghost a-btn-sm">نادیده بگیر</button></span>
                </div>

                <x-admin.card>
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="sr-only">عنوان</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required maxlength="255" autocomplete="off"
                                   placeholder="عنوان نوشته را بنویسید…"
                                   class="a-input !h-14 !rounded-2xl !text-xl font-extrabold {{ $errors->has('title') ? 'has-error' : '' }}">
                            @error('title')<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500" id="permalink-row">
                            <i class="ph ph-link-simple text-base text-slate-400"></i>
                            <span class="font-bold">پیوند یکتا:</span>
                            <span dir="ltr" class="truncate text-slate-400" id="permalink-prefix">{{ $siteHost }}/blog/</span>
                            <input type="text" name="slug" id="slug" dir="auto" value="{{ old('slug', $post->slug) }}" maxlength="190" placeholder="خودکار از عنوان"
                                   class="min-w-[8rem] flex-1 rounded-lg border border-transparent bg-slate-50 px-2 py-1 text-xs font-bold text-slate-700 outline-none hover:border-line focus:border-aron-300 focus:bg-white focus:ring-2 focus:ring-aron-100 {{ $errors->has('slug') ? 'border-coral-400' : '' }}">
                            <span id="slug-hint" class="w-full text-coral-600 empty:hidden"></span>
                        </div>
                        @error('slug')<p class="text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror

                        <x-admin.editor name="body" :value="$post->body" :height="520" />
                        @error('body')<p class="text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                    </div>
                </x-admin.card>

                <x-admin.card title="خلاصه‌ی نوشته" subtitle="در کارت‌های وبلاگ و نتایج جستجو نمایش داده می‌شود (اختیاری)." icon="text-align-right">
                    <textarea name="excerpt" id="excerpt" rows="3" maxlength="1000" class="a-textarea" placeholder="یک یا دو جمله‌ی جذاب درباره‌ی این نوشته…">{{ old('excerpt', $post->excerpt) }}</textarea>
                </x-admin.card>

                {{-- ------------- سئو ------------- --}}
                <x-admin.card title="سئو و اشتراک‌گذاری" subtitle="پیش‌نمایش گوگل و تنظیمات پیشرفته" icon="magnifying-glass" id="seo-card">
                    <x-slot:actions><span id="seo-score-badge" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-500">امتیاز: ۰</span></x-slot:actions>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between"><label for="meta_title" class="a-label !mb-0">عنوان سئو</label><span class="text-[11px] font-bold text-slate-400" id="mt-count">۰ / ۶۰</span></div>
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $post->meta_title) }}" maxlength="255" class="a-input" placeholder="خالی = عنوان نوشته">
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100"><div id="mt-bar" class="h-full w-0 rounded-full bg-mint-500 transition-all"></div></div>
                            </div>
                            <div>
                                <div class="mb-1.5 flex items-center justify-between"><label for="meta_description" class="a-label !mb-0">توضیحات متا</label><span class="text-[11px] font-bold text-slate-400" id="md-count">۰ / ۱۶۰</span></div>
                                <textarea name="meta_description" id="meta_description" rows="3" maxlength="500" class="a-textarea !min-h-24" placeholder="خالی = خلاصه یا ابتدای متن">{{ old('meta_description', $post->meta_description) }}</textarea>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100"><div id="md-bar" class="h-full w-0 rounded-full bg-mint-500 transition-all"></div></div>
                            </div>
                            <x-admin.input name="canonical_url" type="url" label="آدرس canonical" hint="فقط اگر این مطلب نسخه‌ی اصلی دیگری دارد." :value="$post->canonical_url" placeholder="https://" />
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="a-label">پیش‌نمایش در گوگل</p>
                                <div class="rounded-2xl border border-line bg-white p-4" dir="rtl">
                                    <p class="truncate text-xs text-slate-500" dir="ltr" style="text-align:right" id="snip-url">{{ $siteHost }} › blog</p>
                                    <p class="mt-1 line-clamp-2 text-[1.05rem] font-medium leading-7 text-[#1a0dab]" id="snip-title">—</p>
                                    <p class="mt-0.5 line-clamp-2 text-[13px] leading-6 text-slate-600" id="snip-desc">—</p>
                                </div>
                            </div>
                            <div>
                                <p class="a-label">تصویر اشتراک‌گذاری (OG)</p>
                                <div class="flex items-center gap-3">
                                    <img id="og_preview" src="{{ old('og_image', $post->og_image) ? asset(ltrim(old('og_image', $post->og_image), '/')) : '' }}" alt="" class="{{ old('og_image', $post->og_image) ? '' : 'hidden' }} h-14 w-24 rounded-xl border border-line object-cover">
                                    <input type="hidden" name="og_image" id="og_image" value="{{ old('og_image', $post->og_image) }}">
                                    <x-admin.button type="button" variant="secondary" size="sm" icon="image" data-media-pick data-type="image" data-target="#og_image" data-preview="#og_preview">انتخاب</x-admin.button>
                                    <x-admin.button type="button" variant="ghost" size="sm" icon="x" data-media-clear data-target="#og_image" data-preview="#og_preview">حذف</x-admin.button>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-400">خالی = تصویر شاخص.</p>
                            </div>
                            <x-admin.toggle name="noindex" label="عدم نمایش در موتورهای جستجو (noindex)" hint="این نوشته ایندکس نشود." :checked="(bool) old('noindex', $post->noindex)" unchecked="0" />
                        </div>
                    </div>

                    <ul class="mt-5 grid gap-x-6 gap-y-1.5 border-t border-line pt-4 text-xs sm:grid-cols-2" id="seo-checks"></ul>
                </x-admin.card>
            </div>

            {{-- ================= ستون کناری ================= --}}
            <aside class="min-w-0 space-y-5 xl:sticky xl:top-20">
                <x-admin.card title="انتشار" icon="paper-plane-tilt">
                    <div class="space-y-4">
                        <x-admin.select name="status" label="وضعیت" :selected="$statusValue" id="status"
                                        :options="['draft' => 'پیش‌نویس', 'published' => 'منتشرشده', 'scheduled' => 'زمان‌بندی‌شده', 'private' => 'خصوصی']" />
                        <div>
                            <label for="published_at" class="a-label">زمان انتشار</label>
                            <input type="datetime-local" name="published_at" id="published_at" value="{{ $publishedAt }}" dir="ltr" class="a-input {{ $errors->has('published_at') ? 'has-error' : '' }}">
                            <p class="mt-1.5 text-xs text-slate-400" id="published-fa">خالی = همین حالا (برای وضعیت «منتشرشده»)</p>
                            @error('published_at')<p class="mt-1 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                        </div>
                        <div id="password-box" class="hidden space-y-2">
                            <x-admin.input name="password" type="password" label="رمز نوشته‌ی خصوصی" hint="{{ $post->password ? 'رمز تنظیم شده است؛ برای تغییر، رمز جدید بنویسید.' : 'اختیاری' }}" autocomplete="new-password" />
                            @if ($post->password)
                                <x-admin.checkbox name="remove_password" label="حذف رمز فعلی" />
                            @endif
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-[11px] leading-5 text-amber-700">نوشته‌ی خصوصی در سایت نمایش داده نمی‌شود و فقط کارکنان با لینک مستقیم آن را می‌بینند.</p>
                        </div>
                        <x-admin.toggle name="commentable" label="امکان ثبت دیدگاه" hint="بازدیدکنندگان بتوانند دیدگاه بگذارند." :checked="(bool) old('commentable', $post->commentable ?? true)" unchecked="0" />

                        <dl class="space-y-1.5 border-t border-line pt-4 text-xs">
                            <div class="flex justify-between"><dt class="text-slate-400">کلمات</dt><dd class="font-bold text-slate-700" id="stat-words">—</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-400">زمان مطالعه</dt><dd class="font-bold text-slate-700" id="stat-read">—</dd></div>
                            @if ($exists)
                                <div class="flex justify-between"><dt class="text-slate-400">نویسنده</dt><dd class="font-bold text-slate-700">{{ $post->author?->name ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-slate-400">آخرین ویرایش</dt><dd class="font-bold text-slate-700">{{ jdate($post->updated_at)->format('Y/m/d H:i') }}@if ($post->updater) · {{ $post->updater->name }}@endif</dd></div>
                            @endif
                            <div class="flex justify-between"><dt class="text-slate-400">ذخیره‌ی محلی</dt><dd class="font-bold text-slate-500" id="autosave-state">—</dd></div>
                        </dl>
                    </div>
                </x-admin.card>

                <x-admin.card title="دسته‌بندی‌ها" icon="folders">
                    <div class="max-h-56 space-y-1 overflow-y-auto rounded-2xl border border-line bg-slate-50/60 p-2" id="cat-list">
                        @forelse ($categoryOptions as $c)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl px-2 py-1.5 text-sm font-semibold text-slate-700 hover:bg-white" style="padding-inline-start: {{ 0.5 + $c['depth'] * 1.1 }}rem">
                                <input type="checkbox" name="categories[]" value="{{ $c['id'] }}" class="a-check" @checked(in_array($c['id'], $catSel, true))>
                                <span class="truncate {{ $c['active'] ? '' : 'opacity-50' }}">@if ($c['depth'])<span class="text-slate-300">└</span> @endif{{ $c['name'] }}</span>
                            </label>
                        @empty
                            <p class="p-2 text-xs text-slate-400" id="cat-empty">هنوز دسته‌ای ساخته نشده.</p>
                        @endforelse
                    </div>
                    @error('categories.*')<p class="mt-1 text-xs font-semibold text-coral-600">{{ $message }}</p>@enderror
                    <details class="mt-3 rounded-2xl border border-dashed border-line px-3 py-2" id="cat-quick">
                        <summary class="cursor-pointer text-xs font-extrabold text-aron-700">+ افزودن دسته‌ی جدید</summary>
                        <div class="mt-3 space-y-2">
                            <input type="text" class="a-input !h-10" placeholder="نام دسته" id="cat-new-name" maxlength="120">
                            <select class="a-select !h-10" id="cat-new-parent">
                                <option value="">بدون والد (دسته‌ی اصلی)</option>
                                @foreach ($categoryOptions as $c)<option value="{{ $c['id'] }}">{{ str_repeat('— ', $c['depth']) }}{{ $c['name'] }}</option>@endforeach
                            </select>
                            <x-admin.button type="button" size="sm" icon="plus" id="cat-new-btn">افزودن</x-admin.button>
                        </div>
                    </details>
                </x-admin.card>

                <x-admin.card title="برچسب‌ها" icon="tag">
                    <div class="relative" id="tag-box" data-initial='@json(array_values($tagList))'>
                        <div class="flex min-h-[2.75rem] flex-wrap items-center gap-1.5 rounded-2xl border border-line bg-white px-2.5 py-2 focus-within:border-aron-400 focus-within:ring-4 focus-within:ring-aron-100" id="tag-wrap">
                            <input type="text" id="tag-input" class="min-w-[7rem] flex-1 border-0 bg-transparent p-1 text-sm outline-none focus:ring-0" placeholder="تایپ کنید و Enter بزنید…" autocomplete="off" maxlength="80">
                        </div>
                        <div class="absolute inset-x-0 top-full z-20 mt-1 hidden rounded-2xl border border-line bg-white p-1.5 shadow-adm-pop" id="tag-suggest"></div>
                    </div>
                    @if (count($allTags))
                        <div class="mt-3 flex flex-wrap gap-1.5" id="tag-popular">
                            <span class="w-full text-[11px] font-bold text-slate-400">پرکاربردها:</span>
                            @foreach (array_slice($allTags, 0, 8) as $t)
                                <button type="button" data-tag-add="{{ $t }}" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 hover:bg-aron-50 hover:text-aron-700">{{ $t }}</button>
                            @endforeach
                        </div>
                    @endif
                </x-admin.card>

                <x-admin.card title="تصویر شاخص" icon="image-square">
                    <input type="hidden" name="featured_image" id="featured_image" value="{{ old('featured_image', $post->featured_image) }}">
                    @php($fi = old('featured_image', $post->featured_image))
                    <div class="overflow-hidden rounded-2xl border border-line bg-slate-50">
                        <img id="featured_preview" src="{{ $fi ? (preg_match('#^(https?:)?//#', $fi) ? $fi : asset(ltrim($fi, '/'))) : '' }}" alt="" class="{{ $fi ? '' : 'hidden' }} aspect-video w-full object-cover">
                        <button type="button" data-media-pick data-type="image" data-target="#featured_image" data-preview="#featured_preview" id="featured-empty"
                                class="{{ $fi ? 'hidden' : 'flex' }} w-full flex-col items-center gap-1.5 px-4 py-8 text-sm font-semibold text-slate-500 transition hover:bg-aron-50/50 hover:text-aron-700">
                            <i class="ph ph-image-square text-3xl text-aron-500"></i>انتخاب از کتابخانه یا آپلود
                        </button>
                    </div>
                    <div class="mt-3 flex gap-2 {{ $fi ? '' : 'hidden' }}" id="featured-actions">
                        <x-admin.button type="button" variant="secondary" size="sm" icon="arrows-clockwise" data-media-pick data-type="image" data-target="#featured_image" data-preview="#featured_preview">تغییر</x-admin.button>
                        <x-admin.button type="button" variant="danger-soft" size="sm" icon="trash" data-media-clear data-target="#featured_image" data-preview="#featured_preview">حذف</x-admin.button>
                    </div>
                    <div class="mt-3">
                        <x-admin.input name="alt" label="متن جایگزین تصویر (alt)" :value="$post->alt" placeholder="توصیف کوتاه تصویر" />
                    </div>
                </x-admin.card>

                @if ($exists && $revisions->count())
                    <x-admin.card title="نسخه‌های قبلی" subtitle="۱۰ نسخه‌ی آخر پیش از هر ذخیره" icon="clock-counter-clockwise">
                        <ul class="space-y-1.5">
                            @foreach ($revisions as $rev)
                                <li class="flex items-center justify-between gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs">
                                    <span class="min-w-0"><span class="block truncate font-bold text-slate-700">{{ jdate($rev->created_at)->format('Y/m/d H:i') }}</span><span class="text-slate-400">{{ $rev->user?->name ?? '—' }}</span></span>
                                    <button type="button" data-rev="{{ $rev->id }}" class="shrink-0 rounded-lg bg-white px-2.5 py-1 font-bold text-aron-700 shadow-sm hover:bg-aron-50">بازگردانی</button>
                                </li>
                            @endforeach
                        </ul>
                    </x-admin.card>
                @endif
            </aside>
        </div>

        {{-- نوار ذخیره‌ی چسبان --}}
        <div class="sticky bottom-3 z-30 mt-5">
            <div class="a-card flex flex-wrap items-center justify-between gap-3 !rounded-2xl px-4 py-3 shadow-adm-lift">
                <div class="flex min-w-0 items-center gap-2 text-xs font-bold text-slate-500"><span class="size-2 rounded-full bg-slate-300" id="dirty-dot"></span><span id="dirty-text">بدون تغییر</span></div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.button :href="route('panel.posts.index')" variant="ghost" size="sm">انصراف</x-admin.button>
                    <button type="submit" name="_continue" value="1" class="a-btn a-btn-secondary a-btn-sm" data-loading-on-submit><i class="ph ph-floppy-disk text-[1.15em]"></i>ذخیره و ادامه</button>
                    <button type="submit" class="a-btn a-btn-primary a-btn-sm" data-loading-on-submit id="primary-save"><i class="ph ph-check text-[1.15em]"></i><span id="primary-label">ذخیره</span></button>
                </div>
            </div>
        </div>
    </form>

    <x-slot:scripts>
        <x-admin.media-config />
    </x-slot:scripts>
</x-admin.layout>
