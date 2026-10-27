/*
 * Post editor page behaviour (admin/dash/posts/form.blade.php): Persian-safe slug, tag chips, quick-add category,
 * SEO counters + Google snippet + checklist, status/date/password logic, local autosave, dirty state, revisions.
 */
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const faNum = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
const toast = (m, t = 'success') => (window.adminToast ? window.adminToast(m, t) : console.log(m));

export function slugify(text, max = 110) {
    let t = String(text || '').trim();
    const map = { ي: 'ی', ى: 'ی', ك: 'ک', ۀ: 'ه', ة: 'ه', ؤ: 'و', أ: 'ا', إ: 'ا' };
    t = t.replace(/[يىكۀةؤأإ]/g, (c) => map[c]);
    t = t.replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
    t = t.replace(/[‌‍‎‏‪-‮]/g, '-').replace(/[ً-ٰٟـ]/g, '');
    t = t.toLowerCase().replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-+|-+$/g, '');
    if (t.length > max) t = t.slice(0, max).replace(/-+$/g, '');
    return t;
}

function init(form) {
    const id = form.dataset.id || '';
    const KEY = 'aron:post:' + (id || 'new');
    const f = (n) => form.elements[n];
    const title = f('title'), slug = f('slug'), status = f('status'), when = f('published_at');
    const noindexEl = $('input[type=checkbox][name=noindex]', form);
    const metaTitle = f('meta_title'), metaDesc = f('meta_description'), excerpt = f('excerpt');
    const ta = $('#ed_body') || f('body');

    /* ---------- slug ---------- */
    let slugTouched = !!slug.value;
    slug.addEventListener('input', () => { slugTouched = true; });
    title.addEventListener('input', () => { if (!slugTouched) { slug.value = slugify(title.value); renderSnippet(); } });
    const checkSlug = async () => {
        const v = slug.value.trim() || title.value.trim();
        const hint = $('#slug-hint');
        hint.textContent = '';
        if (!v) return;
        try {
            const r = await fetch(form.dataset.slugUrl + '?' + new URLSearchParams({ slug: slugify(v), ignore: id }), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const j = await r.json();
            if (j.taken && j.slug !== slugify(v)) { slug.value = j.slug; hint.textContent = 'این پیوند قبلاً استفاده شده بود؛ به «' + j.slug + '» تغییر کرد.'; }
            else if (slug.value) slug.value = slugify(slug.value);
        } catch (e) { /* offline */ }
        renderSnippet();
    };
    slug.addEventListener('blur', checkSlug);
    title.addEventListener('blur', () => { if (!id && !slug.value) { slug.value = slugify(title.value); } if (!id && !slugTouched) checkSlug(); });

    /* ---------- status / date / password ---------- */
    const faDate = $('#published-fa');
    function renderStatus() {
        const s = status.value;
        $('#password-box').classList.toggle('hidden', s !== 'private');
        $('#primary-label').textContent = { draft: 'ذخیره پیش‌نویس', published: id ? 'به‌روزرسانی' : 'انتشار', scheduled: 'زمان‌بندی انتشار', private: 'ذخیره (خصوصی)' }[s] || 'ذخیره';
        if (s === 'scheduled' && !when.value) {
            const d = new Date(Date.now() + 24 * 3600 * 1000);
            d.setMinutes(0, 0, 0);
            when.value = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        }
        renderWhen();
    }
    function renderWhen() {
        if (!when.value) { faDate.textContent = status.value === 'scheduled' ? 'یک تاریخ و ساعت در آینده انتخاب کنید.' : 'خالی = همین حالا (برای وضعیت «منتشرشده»)'; return; }
        try {
            const d = new Date(when.value);
            faDate.textContent = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { dateStyle: 'full', timeStyle: 'short' }).format(d);
            if (status.value === 'published' && d.getTime() > Date.now() + 60000) faDate.textContent += ' — در آینده است؛ نوشته در همین زمان منتشر می‌شود.';
        } catch (e) { faDate.textContent = ''; }
    }
    status.addEventListener('change', renderStatus);
    when.addEventListener('input', renderWhen);

    /* ---------- tags (chips) ---------- */
    const tagWrap = $('#tag-wrap'), tagInput = $('#tag-input'), sugg = $('#tag-suggest');
    const tags = [];
    function addTag(name) {
        name = String(name || '').replace(/^#/, '').replace(/\s+/g, ' ').trim();
        if (!name || name.length > 80 || tags.some((t) => t.toLowerCase() === name.toLowerCase())) return;
        tags.push(name);
        const chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-1 rounded-full bg-aron-50 px-2.5 py-1 text-xs font-bold text-aron-700';
        chip.dataset.tag = name;
        chip.innerHTML = '<span></span><input type="hidden" name="tags[]"><button type="button" class="grid size-4 place-items-center rounded-full hover:bg-aron-100" aria-label="حذف"><i class="ph ph-x text-[10px]"></i></button>';
        chip.firstChild.textContent = '#' + name;
        chip.querySelector('input').value = name;
        chip.querySelector('button').onclick = () => { tags.splice(tags.indexOf(name), 1); chip.remove(); markDirty(); };
        tagWrap.insertBefore(chip, tagInput);
        markDirty();
    }
    JSON.parse($('#tag-box').dataset.initial || '[]').forEach(addTag);
    let sT;
    tagInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',' || e.key === '،') { e.preventDefault(); addTag(tagInput.value); tagInput.value = ''; sugg.classList.add('hidden'); }
        else if (e.key === 'Backspace' && !tagInput.value && tags.length) { tagWrap.querySelector('[data-tag]:last-of-type')?.querySelector('button').click(); }
    });
    tagInput.addEventListener('blur', () => { setTimeout(() => sugg.classList.add('hidden'), 150); if (tagInput.value.trim()) { addTag(tagInput.value); tagInput.value = ''; } });
    tagInput.addEventListener('input', () => {
        clearTimeout(sT);
        const q = tagInput.value.trim();
        if (!q) { sugg.classList.add('hidden'); return; }
        sT = setTimeout(async () => {
            try {
                const r = await fetch(form.dataset.tagUrl + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
                const list = (await r.json()).filter((n) => !tags.some((t) => t.toLowerCase() === n.toLowerCase()));
                sugg.innerHTML = list.map((n) => '<button type="button" class="block w-full rounded-xl px-3 py-1.5 text-start text-sm font-semibold text-slate-700 hover:bg-aron-50" data-s></button>').join('') || '<p class="px-3 py-1.5 text-xs text-slate-400">«Enter» برای ساخت برچسب «' + q.replace(/[<>&"]/g, '') + '»</p>';
                $$('[data-s]', sugg).forEach((b, i) => { b.textContent = '#' + list[i]; b.onmousedown = (e) => { e.preventDefault(); addTag(list[i]); tagInput.value = ''; sugg.classList.add('hidden'); }; });
                sugg.classList.remove('hidden');
            } catch (e) { /* ignore */ }
        }, 200);
    });
    document.addEventListener('click', (e) => { const b = e.target.closest('[data-tag-add]'); if (b) addTag(b.dataset.tagAdd); });
    tagWrap.addEventListener('click', () => tagInput.focus());

    /* ---------- quick-add category ---------- */
    const catBtn = $('#cat-new-btn');
    catBtn.addEventListener('click', async () => {
        const name = $('#cat-new-name').value.trim();
        if (!name) { $('#cat-new-name').focus(); return; }
        catBtn.disabled = true;
        const r = await window.adminFetch(form.dataset.catUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name, parent_id: $('#cat-new-parent').value || null, type: 'blog' }) });
        catBtn.disabled = false;
        const j = await r.json().catch(() => ({}));
        if (!r.ok) { toast((j.errors && Object.values(j.errors)[0][0]) || j.message || 'ایجاد دسته ناموفق بود', 'error'); return; }
        $('#cat-empty')?.remove();
        const lab = document.createElement('label');
        lab.className = 'flex cursor-pointer items-center gap-2.5 rounded-xl px-2 py-1.5 text-sm font-semibold text-slate-700 hover:bg-white';
        lab.style.paddingInlineStart = (0.5 + (j.depth || 0) * 1.1) + 'rem';
        lab.innerHTML = '<input type="checkbox" name="categories[]" class="a-check" checked><span class="truncate"></span>';
        lab.querySelector('input').value = j.id;
        lab.querySelector('span').textContent = j.name;
        $('#cat-list').appendChild(lab);
        const opt = document.createElement('option'); opt.value = j.id; opt.textContent = j.name; $('#cat-new-parent').appendChild(opt);
        $('#cat-new-name').value = '';
        toast('دسته‌ی «' + j.name + '» ساخته و انتخاب شد');
        markDirty();
    });
    $('#cat-new-name').addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); catBtn.click(); } });

    /* ---------- featured image visibility ---------- */
    const fi = f('featured_image');
    function renderFeatured() {
        const has = !!fi.value;
        $('#featured_preview').classList.toggle('hidden', !has);
        $('#featured-empty').classList.toggle('hidden', has); $('#featured-empty').classList.toggle('flex', !has);
        $('#featured-actions').classList.toggle('hidden', !has);
        renderSeo();
    }
    fi.addEventListener('change', renderFeatured);
    form.addEventListener('media:picked', (e) => {
        if (e.target.closest('[data-target="#featured_image"]') || e.target.dataset.target === '#featured_image') {
            const m = e.detail.items[0];
            if (m && m.alt && !f('alt').value) f('alt').value = m.alt;
        }
    });

    /* ---------- SEO ---------- */
    let words = 0, bodyHtml = ta ? ta.value : '';
    const plain = (h) => { const d = document.createElement('div'); d.innerHTML = h || ''; return (d.textContent || '').replace(/\s+/g, ' ').trim(); };
    function bar(el, len, min, max) {
        const p = Math.min(100, Math.round((len / max) * 100));
        el.style.width = p + '%';
        el.className = 'h-full rounded-full transition-all ' + (len === 0 ? 'bg-slate-200' : len >= min && len <= max ? 'bg-mint-500' : len > max ? 'bg-coral-500' : 'bg-amber-400');
    }
    function renderSnippet() {
        const t = (metaTitle.value || title.value || '').trim();
        const d = (metaDesc.value || excerpt.value || plain(bodyHtml)).trim();
        $('#snip-title').textContent = t || 'عنوان نوشته';
        $('#snip-desc').textContent = d.length > 160 ? d.slice(0, 157) + '…' : (d || 'توضیحات متا اینجا نمایش داده می‌شود.');
        $('#snip-url').textContent = form.dataset.site + ' › blog › ' + (slug.value || '…');
    }
    function renderSeo() {
        const tl = (metaTitle.value || title.value || '').length;
        const dl = (metaDesc.value || excerpt.value || '').length;
        $('#mt-count').textContent = faNum(metaTitle.value.length) + ' / ۶۰';
        $('#md-count').textContent = faNum(metaDesc.value.length) + ' / ۱۶۰';
        bar($('#mt-bar'), metaTitle.value.length, 30, 60);
        bar($('#md-bar'), metaDesc.value.length, 70, 160);
        renderSnippet();

        const checks = [
            [tl >= 30 && tl <= 65, 'طول عنوان بین ۳۰ تا ۶۵ نویسه باشد (' + faNum(tl) + ')', tl >= 15 ? 0.5 : 0, 20],
            [dl >= 70 && dl <= 160, 'توضیحات متا بین ۷۰ تا ۱۶۰ نویسه باشد (' + faNum(dl) + ')', dl >= 40 ? 0.5 : 0, 20],
            [words >= 300, 'حداقل ۳۰۰ کلمه محتوا (' + faNum(words) + ')', words >= 100 ? 0.3 : 0, 20],
            [!!fi.value && !!f('alt').value.trim(), 'تصویر شاخص همراه با متن جایگزین', fi.value ? 0.5 : 0, 15],
            [/<h[23][\s>]/i.test(bodyHtml), 'حداقل یک زیرعنوان (H2/H3) در متن', 0, 10],
            [/<a\s[^>]*href/i.test(bodyHtml), 'حداقل یک لینک در متن', 0, 5],
            [slug.value.length >= 3 && slug.value.length <= 75, 'پیوند یکتا کوتاه و معنادار', 0, 5],
            [!noindexEl.checked, 'نوشته ایندکس شود (noindex خاموش)', 0, 5],
        ];
        let score = 0;
        checks.forEach(([ok, , part, pts]) => { score += ok ? pts : pts * part; });
        score = Math.min(100, Math.round(score));
        const badge = $('#seo-score-badge');
        badge.textContent = 'امتیاز: ' + faNum(score);
        badge.className = 'rounded-full px-3 py-1 text-xs font-extrabold ' + (score >= 75 ? 'bg-mint-50 text-mint-700' : score >= 45 ? 'bg-amber-50 text-amber-700' : 'bg-coral-50 text-coral-700');
        $('#seo-checks').innerHTML = checks.map(([ok, label]) => `<li class="flex items-start gap-2 ${ok ? 'text-slate-600' : 'text-slate-400'}"><i class="ph-fill ph-${ok ? 'check-circle text-mint-500' : 'circle text-slate-300'} mt-0.5 text-base"></i>${label}</li>`).join('');
    }
    [title, metaTitle, metaDesc, excerpt, slug, f('alt')].forEach((el) => el && el.addEventListener('input', renderSeo));
    noindexEl.addEventListener('change', renderSeo);

    ta.addEventListener('aron-editor:change', (e) => {
        words = e.detail.words; bodyHtml = e.detail.html;
        $('#stat-words').textContent = faNum(e.detail.words);
        $('#stat-read').textContent = faNum(e.detail.minutes) + ' دقیقه';
        renderSeo(); markDirty();
    });
    if (ta.value) { words = plain(ta.value).split(' ').filter(Boolean).length; $('#stat-words').textContent = faNum(words); $('#stat-read').textContent = faNum(Math.max(1, Math.ceil(words / 200))) + ' دقیقه'; }

    /* ---------- dirty + local autosave ---------- */
    let dirty = false, submitting = false, t2;
    const stateEl = $('#autosave-state');
    const snapshot = () => {
        const d = {};
        $$('input[name], textarea[name], select[name]', form).forEach((el) => {
            const n = el.name;
            if (['_token', '_method', 'password', 'remove_password', '_continue'].includes(n)) return;
            if (n.endsWith('[]')) { d[n] = d[n] || []; if (el.type !== 'checkbox' || el.checked) d[n].push(el.value); return; }
            if (el.type === 'checkbox') { d[n] = el.checked ? el.value : '0'; return; }
            d[n] = el.value;
        });
        d.body = window.AronEditor ? window.AronEditor.getData('ed_body') : d.body;
        return d;
    };
    const store = {
        get() { try { return JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { return null; } },
        set(v) { try { localStorage.setItem(KEY, JSON.stringify(v)); return true; } catch (e) { return false; } },
        del() { try { localStorage.removeItem(KEY); } catch (e) { /* ignore */ } },
    };
    function markDirty() {
        if (submitting) return;
        dirty = true;
        $('#dirty-dot').className = 'size-2 rounded-full bg-amber-400';
        $('#dirty-text').textContent = 'تغییرات ذخیره‌نشده';
        clearTimeout(t2);
        t2 = setTimeout(() => {
            const ok = store.set({ t: Date.now(), d: snapshot() });
            stateEl.textContent = ok ? 'ذخیره شد · ' + new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' }) : 'ناموفق';
        }, 1500);
    }
    form.addEventListener('input', (e) => { if (e.target.id === 'tag-input' || e.target.id === 'cat-new-name') return; markDirty(); });
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', () => { submitting = true; dirty = false; store.del(); });
    window.addEventListener('beforeunload', (e) => { if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; } });

    // پیش‌نویس محلی
    const saved = store.get();
    const banner = $('#local-draft-banner');
    if (saved && saved.d && (saved.t / 1000 > Number(form.dataset.updated || 0) + 5)) {
        const cur = JSON.stringify([title.value, ta.value]);
        if (JSON.stringify([saved.d.title, saved.d.body]) !== cur) {
            banner.classList.remove('hidden'); banner.classList.add('flex');
            $('[data-banner-text]', banner).textContent = 'یک پیش‌نویس ذخیره‌نشده‌ی محلی (' + new Date(saved.t).toLocaleString('fa-IR') + ') یافت شد.';
            $('[data-draft-restore]', banner).onclick = () => {
                const d = saved.d;
                Object.keys(d).forEach((n) => {
                    if (n === 'body') { window.AronEditor.setData('ed_body', d.body || ''); return; }
                    if (n === 'tags[]') { $$('[data-tag]', tagWrap).forEach((c) => c.remove()); tags.length = 0; (d[n] || []).forEach(addTag); return; }
                    if (n === 'categories[]') { $$('input[name="categories[]"]', form).forEach((c) => { c.checked = d[n].includes(c.value); }); return; }
                    let el = form.elements[n];
                    if (el && el.length) el = Array.from(el).find((x) => x.type === 'checkbox') || null;
                    if (!el) return;
                    if (el.type === 'checkbox') el.checked = d[n] === el.value; else el.value = d[n];
                });
                slugTouched = !!slug.value; renderStatus(); renderFeatured(); renderSeo();
                banner.classList.add('hidden'); banner.classList.remove('flex');
                toast('پیش‌نویس محلی بازیابی شد؛ برای ثبت نهایی ذخیره کنید.', 'info'); markDirty();
            };
            $('[data-draft-discard]', banner).onclick = () => { store.del(); banner.classList.add('hidden'); banner.classList.remove('flex'); };
        }
    }

    /* ---------- revisions ---------- */
    document.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-rev]');
        if (!b || !window.confirm('محتوای این نسخه جایگزین متن فعلی در ویرایشگر شود؟ (تا ذخیره نکنید ثبت نمی‌شود)')) return;
        const r = await fetch(form.dataset.revUrl.replace('__ID__', b.dataset.rev), { headers: { Accept: 'application/json' } });
        if (!r.ok) { toast('بارگذاری نسخه ناموفق بود', 'error'); return; }
        const j = await r.json();
        title.value = j.title; window.AronEditor.setData('ed_body', j.body || '');
        toast('نسخه در ویرایشگر بارگذاری شد؛ برای ثبت، ذخیره کنید.', 'info'); markDirty();
    });

    renderStatus(); renderFeatured(); renderSeo();
    dirty = false; $('#dirty-dot').className = 'size-2 rounded-full bg-slate-300'; $('#dirty-text').textContent = 'بدون تغییر';

    // Ctrl/Cmd+S
    document.addEventListener('keydown', (e) => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); $('button[name="_continue"]', form).click(); } });
}

const boot = () => { const f = document.querySelector('[data-post-form]'); if (f) init(f); };
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
