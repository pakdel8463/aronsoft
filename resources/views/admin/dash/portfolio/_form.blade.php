@php
    $isEdit = $portfolio->exists;
    $action = $isEdit ? route('panel.portfolios.update', $portfolio) : route('panel.portfolios.store');
    $galleryItems = $isEdit ? $portfolio->images->map(fn ($i) => ['value' => $i->id, 'url' => $i->url])->all() : [];
    $techValues = $isEdit ? $portfolio->technologies : [];
    $tagValues = $isEdit ? $portfolio->tags->pluck('name')->all() : [];
@endphp
<form method="POST" action="{{ $action }}" class="grid items-start gap-5 lg:grid-cols-3">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    {{-- ستون اصلی --}}
    <div class="grid gap-5 lg:col-span-2">
        <x-admin.card title="اطلاعات پروژه" icon="note-pencil">
            <div class="grid gap-4">
                <x-admin.input name="title" label="عنوان پروژه" required :value="$portfolio->title" data-slug-source />
                <x-admin.input name="slug" label="نامک (Slug)" :value="$portfolio->slug" ltr hint="خالی بگذارید تا از روی عنوان ساخته شود. آدرس: /portfolio/نامک" />
                <x-admin.textarea name="short_description" label="توضیح کوتاه" :value="$portfolio->short_description" rows="2" hint="در کارت نمونه‌کار نمایش داده می‌شود (حداکثر ۳۰۰ نویسه)." />
                @include('admin.dash.portfolio.partials.rich', ['name' => 'description', 'value' => $portfolio->description, 'label' => 'شرح کامل پروژه', 'rows' => 14])
            </div>
        </x-admin.card>

        <x-admin.card title="گالری تصاویر" subtitle="تصاویر را بکشید تا ترتیب نمایش عوض شود. حداکثر ۴ مگابایت برای هر تصویر." icon="images">
            <x-admin.gallery-field name="gallery" :items="$galleryItems" :upload-url="route('panel.portfolio.upload-image')" :extra="['purpose' => 'gallery']" :max="30" />
        </x-admin.card>

        <x-admin.card title="سئو" subtitle="نمایش در نتایج گوگل" icon="magnifying-glass">
            <div class="grid gap-4">
                <div>
                    <x-admin.input name="seo_title" label="عنوان سئو" :value="$portfolio->seo_title" data-count="seo_title" hint="بهترین طول: ۲۵ تا ۶۵ نویسه" />
                    <p class="mt-1 text-xs text-slate-400"><span data-count-out="seo_title">0</span> نویسه</p>
                </div>
                <div>
                    <x-admin.textarea name="seo_description" label="توضیحات سئو" :value="$portfolio->seo_description" rows="3" data-count="seo_description" hint="بهترین طول: ۷۰ تا ۱۶۰ نویسه" />
                    <p class="mt-1 text-xs text-slate-400"><span data-count-out="seo_description">0</span> نویسه</p>
                </div>
                @if ($isEdit)
                    <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                        <span class="font-bold text-slate-600">امتیاز سئو:</span>
                        <x-admin.badge :color="$portfolio->seo_score >= 70 ? 'mint' : ($portfolio->seo_score >= 40 ? 'amber' : 'coral')">{{ \App\Helpers\Fa::toFa($portfolio->seo_score) }}٪</x-admin.badge>
                        <span class="text-xs text-slate-400">بر اساس عنوان/توضیحات سئو، تصویر شاخص و طول متن؛ هنگام ذخیره محاسبه می‌شود.</span>
                    </div>
                @endif
            </div>
        </x-admin.card>
    </div>

    {{-- ستون کناری --}}
    <div class="grid gap-5">
        <x-admin.card title="انتشار" icon="paper-plane-tilt">
            <div class="grid gap-4">
                <x-admin.select name="status" label="وضعیت" :options="\App\Models\Portfolio::STATUSES" :selected="$portfolio->status ?: 'draft'" hint="فقط «منتشر شده» در سایت دیده می‌شود." />
                <div class="flex flex-wrap gap-2">
                    <x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'بروزرسانی' : 'ذخیره' }}</x-admin.button>
                    <x-admin.button :href="route('panel.portfolios.index')" variant="secondary">انصراف</x-admin.button>
                    @if ($isEdit && $portfolio->status === 'published')
                        <x-admin.button :href="route('portfolio.show', $portfolio->slug)" variant="ghost" icon="arrow-square-out" target="_blank" rel="noopener">مشاهده</x-admin.button>
                    @endif
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="تصویر شاخص" icon="image">
            <x-admin.image-field name="featured_image" :value="$portfolio->featured_image" :upload-url="route('panel.portfolio.upload-image')" :extra="['purpose' => 'featured']" />
        </x-admin.card>

        <x-admin.card title="مشخصات" icon="briefcase">
            <div class="grid gap-4">
                <x-admin.input name="client_name" label="نام کارفرما" :value="$portfolio->client_name" />
                <x-admin.input name="preview_link" label="لینک پیش‌نمایش" :value="$portfolio->preview_link" type="url" placeholder="https://" />
                <div class="grid grid-cols-2 gap-3">
                    <x-admin.input name="start_date" label="تاریخ شروع" :value="\App\Support\JalaliInput::format($portfolio->start_date)" ltr placeholder="1404/01/01" />
                    <x-admin.input name="end_date" label="تاریخ پایان" :value="\App\Support\JalaliInput::format($portfolio->end_date)" ltr placeholder="1404/03/15" />
                </div>
                @php $prog = (int) old('progress', $portfolio->progress ?? 100); @endphp
                <x-admin.field label="درصد پیشرفت" name="progress">
                    <div class="flex items-center gap-3">
                        <input type="range" name="progress" min="0" max="100" step="5" value="{{ $prog }}" dir="ltr" class="h-2 w-full flex-1 cursor-pointer accent-aron-600" data-progress>
                        <span class="w-12 shrink-0 text-end text-sm font-extrabold text-aron-700" dir="ltr"><span data-progress-out>{{ $prog }}</span>%</span>
                    </div>
                </x-admin.field>
            </div>
        </x-admin.card>

        <x-admin.card title="دسته‌بندی و برچسب" icon="tag">
            <div class="grid gap-4">
                <x-admin.field label="دسته‌بندی‌ها" name="categories">
                    @php $selCats = array_map('strval', (array) old('categories', $selectedCategories)); @endphp
                    <div class="grid max-h-44 gap-1.5 overflow-y-auto rounded-xl border border-line p-3">
                        @forelse ($categories as $cat)
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="a-check" @checked(in_array((string) $cat->id, $selCats, true))>
                                {{ $cat->name }}
                            </label>
                        @empty
                            <p class="text-xs text-slate-400">هنوز دسته‌بندی «نمونه‌کار» ساخته نشده است.</p>
                        @endforelse
                    </div>
                </x-admin.field>
                <x-admin.chip-input name="technologies" label="تکنولوژی‌ها" :values="$techValues" placeholder="مثلاً Laravel" />
                <x-admin.chip-input name="tags" label="برچسب‌ها" :values="$tagValues" :suggestions="$tagSuggestions" placeholder="برچسب جدید" />
            </div>
        </x-admin.card>

        <x-admin.card title="اعضای تیم" icon="users-three">
            @php $selTeam = array_map('strval', (array) old('team_members', $selectedTeam)); @endphp
            <div class="grid gap-3" data-team data-url="{{ route('panel.portfolio.team.store') }}">
                <div class="grid gap-1.5" data-team-list>
                    @foreach ($teamMembers as $m)
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="team_members[]" value="{{ $m->id }}" class="a-check" @checked(in_array((string) $m->id, $selTeam, true))>
                            <span>{{ $m->name }}@if ($m->role) <span class="text-xs font-normal text-slate-400">— {{ $m->role }}</span>@endif</span>
                        </label>
                    @endforeach
                    <p class="text-xs text-slate-400 {{ $teamMembers->isEmpty() ? '' : 'hidden' }}" data-team-empty>عضوی ثبت نشده است.</p>
                </div>
                <div class="grid grid-cols-[1fr_1fr_auto] gap-2 border-t border-line pt-3">
                    <input type="text" class="a-input" placeholder="نام" data-team-name maxlength="120" aria-label="نام عضو جدید">
                    <input type="text" class="a-input" placeholder="نقش" data-team-role maxlength="120" aria-label="نقش عضو جدید">
                    <x-admin.button type="button" variant="soft" icon="plus" icon-only data-team-add aria-label="افزودن عضو" />
                </div>
            </div>
        </x-admin.card>
    </div>
</form>

<script>
(function () {
    const q = (s, r = document) => r.querySelector(s);
    // شمارنده‌ی نویسه سئو
    document.querySelectorAll('[data-count]').forEach(el => {
        const out = q('[data-count-out="' + el.dataset.count + '"]');
        const up = () => { if (out) out.textContent = el.value.length; };
        el.addEventListener('input', up); up();
    });
    // پیشرفت
    const pr = q('[data-progress]'), po = q('[data-progress-out]');
    pr?.addEventListener('input', () => { po.textContent = pr.value; });
    // عضو تیم
    const team = q('[data-team]');
    if (team) {
        const list = q('[data-team-list]', team), nameI = q('[data-team-name]', team), roleI = q('[data-team-role]', team);
        async function add() {
            const name = nameI.value.trim(); if (!name) { nameI.focus(); return; }
            const fd = new FormData(); fd.append('name', name); fd.append('role', roleI.value.trim());
            try {
                const r = await adminFetch(team.dataset.url, { method: 'POST', body: fd });
                const j = await r.json().catch(() => ({}));
                if (!r.ok) throw new Error((j.errors && Object.values(j.errors)[0][0]) || j.message || 'خطا در ثبت عضو');
                const m = j.member, ex = list.querySelector('input[value="' + m.id + '"]');
                if (ex) { ex.checked = true; }
                else {
                    const lb = document.createElement('label'); lb.className = 'flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-slate-700';
                    const cb = document.createElement('input'); cb.type = 'checkbox'; cb.name = 'team_members[]'; cb.value = m.id; cb.checked = true; cb.className = 'a-check';
                    const sp = document.createElement('span'); sp.textContent = m.name + (m.role ? ' — ' + m.role : '');
                    lb.append(cb, sp); list.insertBefore(lb, q('[data-team-empty]', list));
                }
                q('[data-team-empty]', list).classList.add('hidden');
                nameI.value = roleI.value = '';
            } catch (e) { adminToast(e.message, 'error'); }
        }
        q('[data-team-add]', team).addEventListener('click', add);
        [nameI, roleI].forEach(i => i.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); add(); } }));
    }
})();
</script>
