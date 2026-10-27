/*
 * Media library + reusable picker (vanilla JS). Exposes window.AronMedia:
 *   AronMedia.pick({ multiple, type: 'image'|'video'|'audio'|'document'|'', insert: bool, title }) -> Promise<item[]|null>
 *   AronMedia.upload(files, hooks)   AronMedia.imageHtml(item, opts)
 * Declarative: <button data-media-pick data-target="#input" data-preview="#img" data-value="rel|url|id" data-type="image">
 * Library page: an element with [data-media-library] (see admin/dash/media/index.blade.php).
 * Routes come from window.AronMediaRoutes (printed by <x-admin.media-config/>).
 */
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const routes = () => window.AronMediaRoutes || {};
const toast = (m, t = 'success') => (window.adminToast ? window.adminToast(m, t) : console.log(m));
const faNum = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);

const TYPES = [
    ['', 'همه', 'squares-four'],
    ['image', 'تصاویر', 'image'],
    ['video', 'ویدیو', 'video-camera'],
    ['audio', 'صوت', 'music-notes'],
    ['document', 'اسناد', 'file-text'],
];
const TYPE_ICON = { image: 'image', video: 'video-camera', audio: 'music-notes', document: 'file-text' };

function api(url, opts = {}) {
    return fetch(url, {
        credentials: 'same-origin',
        ...opts,
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), ...(opts.headers || {}) },
    }).then(async (r) => {
        let j = {};
        try { j = await r.json(); } catch (e) { /* not json */ }
        return { ok: r.ok, status: r.status, json: j };
    });
}

/* ------------------------------------------------------------------ upload */
function sendFile(file, onProgress) {
    return new Promise((resolve) => {
        const x = new XMLHttpRequest();
        x.open('POST', routes().store);
        x.setRequestHeader('X-CSRF-TOKEN', csrf());
        x.setRequestHeader('Accept', 'application/json');
        x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        x.upload.onprogress = (e) => { if (e.lengthComputable) onProgress(Math.round((e.loaded / e.total) * 100)); };
        x.onload = () => {
            let j = {};
            try { j = JSON.parse(x.responseText); } catch (e) { /* ignore */ }
            const ok = x.status >= 200 && x.status < 300 && j.items && j.items.length;
            let message = j.message || (j.errors && j.errors[0] && j.errors[0].message) || '';
            if (!ok && !message) message = x.status === 413 ? 'حجم فایل از محدودیت سرور بیشتر است.' : x.status === 419 ? 'نشست منقضی شده؛ صفحه را تازه‌سازی کنید.' : 'آپلود ناموفق بود (' + x.status + ').';
            resolve({ ok, item: ok ? j.items[0] : null, message });
        };
        x.onerror = () => resolve({ ok: false, message: 'ارتباط با سرور برقرار نشد.' });
        const fd = new FormData();
        fd.append('files[]', file);
        x.send(fd);
    });
}

/** آپلود چندتایی با حداکثر ۳ اتصال همزمان. hooks: start(file,id) progress(id,pct) done(id,item) error(id,msg) */
async function upload(files, hooks = {}) {
    const list = Array.from(files);
    const results = [];
    let next = 0;
    const worker = async () => {
        while (next < list.length) {
            const i = next++;
            const f = list[i];
            hooks.start && hooks.start(f, i);
            const r = await sendFile(f, (p) => hooks.progress && hooks.progress(i, p));
            if (r.ok) { results.push(r.item); hooks.done && hooks.done(i, r.item); }
            else { hooks.error && hooks.error(i, r.message, f); }
        }
    };
    await Promise.all(Array.from({ length: Math.min(3, list.length) }, worker));
    return results;
}

/* -------------------------------------------------------- html for editor */
function imageHtml(m, o = {}) {
    const alt = o.alt ?? m.alt ?? '';
    const size = o.size || 'large';
    const align = o.align || 'center';
    const caption = o.caption ?? m.caption ?? '';
    let src = m.rel || m.url;
    if (size === 'medium' && m.medium_rel) src = m.medium_rel;
    else if (size === 'large' && m.large_rel) src = m.large_rel;
    const set = [];
    if (m.medium_rel) set.push(m.medium_rel + ' 768w');
    if (m.large_rel) set.push(m.large_rel + ' 1600w');
    if (m.width) set.push((m.rel || m.url) + ' ' + m.width + 'w');
    const srcset = set.length > 1 ? ' srcset="' + esc(set.join(', ')) + '" sizes="(max-width: 768px) 100vw, 768px"' : '';
    const dim = m.width && m.height ? ' width="' + m.width + '" height="' + m.height + '"' : '';
    const img = '<img src="' + esc(src) + '"' + srcset + dim + ' alt="' + esc(alt) + '" loading="lazy">';
    let style = 'margin: 1.5rem auto; text-align: center';
    if (align === 'left') style = 'float: left; margin: 0 1.5rem 1rem 0; max-width: 50%';
    if (align === 'right') style = 'float: right; margin: 0 0 1rem 1.5rem; max-width: 50%';
    const cap = caption ? '<figcaption style="text-align: center; font-size: 0.875rem; color: #64748b">' + esc(caption) + '</figcaption>' : '';

    return '<figure class="aron-figure" style="' + style + '">' + img + cap + '</figure>';
}

/* ------------------------------------------------------------ library view */
function createLibrary(root, opt = {}) {
    const state = { q: '', type: opt.type || '', sort: 'new', page: 1, last: 1, view: (function () { try { return localStorage.getItem('aron:media-view') || 'grid'; } catch (e) { return 'grid'; } })(), items: [], selected: new Map(), loading: false, counts: {} };
    const picker = opt.mode === 'picker';
    const lockedType = !!opt.lockType;

    root.innerHTML = `
      <div class="relative flex min-h-0 flex-1 flex-col" data-lib>
        <div class="flex flex-wrap items-center gap-2 pb-3" data-lib-bar>
          <div class="no-scrollbar flex min-w-0 gap-1 overflow-x-auto rounded-2xl bg-slate-100 p-1" data-lib-types></div>
          <div class="relative min-w-[10rem] flex-1">
            <i class="ph ph-magnifying-glass pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" placeholder="جستجوی نام، عنوان، متن جایگزین…" class="a-input !h-10 ps-9" data-lib-q>
          </div>
          <select class="a-select !h-10 !w-auto" data-lib-sort aria-label="مرتب‌سازی">
            <option value="new">جدیدترین</option><option value="old">قدیمی‌ترین</option><option value="name">نام</option><option value="size">حجم</option>
          </select>
          <div class="flex rounded-2xl bg-slate-100 p-1">
            <button type="button" data-lib-view="grid" class="grid size-8 place-items-center rounded-xl text-slate-500" title="نمای شبکه‌ای"><i class="ph ph-squares-four text-lg"></i></button>
            <button type="button" data-lib-view="list" class="grid size-8 place-items-center rounded-xl text-slate-500" title="نمای لیستی"><i class="ph ph-list-bullets text-lg"></i></button>
          </div>
          <label class="a-btn a-btn-primary a-btn-sm cursor-pointer"><i class="ph ph-upload-simple text-base"></i>آپلود<input type="file" multiple class="sr-only" data-lib-file></label>
        </div>
        <div class="relative min-h-0 flex-1 overflow-y-auto rounded-2xl" data-lib-scroll>
          <div class="space-y-2 pb-3 empty:hidden" data-lib-uploads></div>
          <div data-lib-body></div>
          <div class="py-4 text-center" data-lib-more></div>
          <div class="pointer-events-none absolute inset-0 z-10 hidden place-items-center rounded-2xl border-2 border-dashed border-aron-400 bg-aron-50/90 text-aron-700" data-lib-drop>
            <div class="text-center"><i class="ph ph-cloud-arrow-up text-5xl"></i><p class="mt-2 text-sm font-extrabold">فایل‌ها را رها کنید تا آپلود شوند</p></div>
          </div>
        </div>
      </div>`;

    const el = {
        types: $('[data-lib-types]', root), q: $('[data-lib-q]', root), sort: $('[data-lib-sort]', root), file: $('[data-lib-file]', root),
        body: $('[data-lib-body]', root), more: $('[data-lib-more]', root), uploads: $('[data-lib-uploads]', root),
        scroll: $('[data-lib-scroll]', root), drop: $('[data-lib-drop]', root),
    };

    const emit = (name) => opt[name] && opt[name](state);
    const typeCount = (k) => (k === '' ? state.counts.all : state.counts[k]);

    function renderTypes() {
        el.types.innerHTML = TYPES.filter(([k]) => !lockedType || k === state.type || k === '').map(([k, label, icon]) => {
            const on = state.type === k;
            const c = typeCount(k);
            return `<button type="button" data-lib-type="${k}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition ${on ? 'bg-white text-aron-700 shadow-sm' : 'text-slate-500 hover:text-slate-800'}"><i class="ph ph-${icon}"></i>${label}${c !== undefined ? `<span class="rounded-full bg-slate-200/70 px-1.5 text-[10px] leading-4 text-slate-500">${faNum(c)}</span>` : ''}</button>`;
        }).join('');
        $$('[data-lib-view]', root).forEach((b) => {
            const on = b.dataset.libView === state.view;
            b.classList.toggle('bg-white', on); b.classList.toggle('text-aron-700', on); b.classList.toggle('shadow-sm', on);
        });
    }

    function thumb(m, cls) {
        if (m.type === 'image' && (m.thumb || m.url)) return `<img src="${esc(m.thumb || m.url)}" alt="${esc(m.alt || '')}" loading="lazy" class="${cls} object-cover">`;
        return `<div class="${cls} grid place-items-center bg-slate-100 text-slate-400"><div class="text-center"><i class="ph ph-${TYPE_ICON[m.type] || 'file'} text-3xl"></i><div class="mt-1 text-[10px] font-extrabold uppercase" dir="ltr">${esc(m.ext)}</div></div></div>`;
    }

    function card(m) {
        const sel = state.selected.has(m.id);
        if (state.view === 'list') {
            return `<div data-id="${m.id}" class="flex cursor-pointer items-center gap-3 border-b border-line/70 px-3 py-2.5 transition hover:bg-aron-50/40 ${sel ? 'bg-aron-50' : ''}">
              <span class="grid size-5 shrink-0 place-items-center rounded-md border ${sel ? 'border-aron-600 bg-aron-600 text-white' : 'border-slate-300 bg-white'}" data-check><i class="ph ph-check text-xs ${sel ? '' : 'hidden'}"></i></span>
              ${thumb(m, 'size-11 shrink-0 rounded-xl')}
              <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-ink">${esc(m.name)}</p><p class="truncate text-xs text-slate-400" dir="ltr" style="text-align:start">${esc(m.file_name)}</p></div>
              <span class="hidden w-24 text-xs text-slate-500 sm:block">${m.width ? faNum(m.width) + '×' + faNum(m.height) : '—'}</span>
              <span class="hidden w-20 text-xs text-slate-500 sm:block">${faNum(m.size_h)}</span>
              <span class="hidden w-28 text-xs text-slate-500 md:block">${esc(m.date || '')}</span>
            </div>`;
        }
        return `<div data-id="${m.id}" class="group relative aspect-square cursor-pointer overflow-hidden rounded-2xl border bg-white transition ${sel ? 'border-aron-600 ring-4 ring-aron-100' : 'border-line hover:border-aron-300 hover:shadow-adm'}">
            ${thumb(m, 'absolute inset-0 size-full')}
            <span data-check class="absolute start-2 top-2 grid size-6 place-items-center rounded-lg border shadow-sm transition ${sel ? 'border-aron-600 bg-aron-600 text-white' : 'border-white/80 bg-white/90 text-transparent group-hover:text-slate-400'}"><i class="ph ph-check text-sm"></i></span>
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/70 to-transparent px-2.5 pb-2 pt-6 text-[11px] font-bold text-white opacity-0 transition group-hover:opacity-100"><span class="block truncate">${esc(m.name)}</span></div>
          </div>`;
    }

    function renderBody() {
        if (!state.items.length && !state.loading) {
            el.body.innerHTML = `<div class="flex flex-col items-center px-6 py-16 text-center"><span class="mb-4 grid size-16 place-items-center rounded-3xl bg-aron-50 text-3xl text-aron-500"><i class="ph ph-images"></i></span><h3 class="text-base font-extrabold text-ink">${state.q || state.type ? 'موردی پیدا نشد' : 'هنوز فایلی آپلود نشده'}</h3><p class="mt-1.5 max-w-sm text-sm text-slate-500">فایل‌ها را بکشید و اینجا رها کنید یا دکمه «آپلود» را بزنید.</p></div>`;
        } else if (state.view === 'list') {
            el.body.innerHTML = `<div class="rounded-2xl border border-line bg-white">${state.items.map(card).join('')}</div>`;
        } else {
            el.body.innerHTML = `<div class="grid grid-cols-3 gap-2.5 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">${state.items.map(card).join('')}</div>`;
        }
        el.more.innerHTML = state.page < state.last
            ? `<button type="button" data-lib-loadmore class="a-btn a-btn-secondary a-btn-sm">${state.loading ? 'در حال بارگذاری…' : 'نمایش بیشتر'}</button>`
            : '';
    }

    async function load(reset) {
        if (state.loading) return;
        if (reset) { state.page = 1; state.items = []; }
        else state.page += 1;
        state.loading = true;
        if (reset) el.body.innerHTML = '<div class="grid place-items-center py-20 text-slate-400"><i class="ph ph-spinner-gap animate-spin text-3xl"></i></div>';
        const p = new URLSearchParams({ page: state.page, per_page: picker ? 30 : 36, sort: state.sort });
        if (state.q) p.set('q', state.q);
        if (state.type) p.set('type', state.type);
        const r = await api(routes().list + '?' + p);
        state.loading = false;
        if (!r.ok) { toast('بارگذاری کتابخانه ناموفق بود', 'error'); el.body.innerHTML = ''; return; }
        state.items = state.items.concat(r.json.items);
        state.last = r.json.meta.last_page;
        state.counts = r.json.counts || state.counts;
        renderTypes();
        renderBody();
        emit('onLoad');
    }

    function toggle(id) {
        const m = state.items.find((x) => x.id === id);
        if (!m) return;
        if (opt.multiple) {
            state.selected.has(id) ? state.selected.delete(id) : state.selected.set(id, m);
        } else if (picker) {
            state.selected.clear(); state.selected.set(id, m);
        } else {
            state.selected.has(id) ? state.selected.delete(id) : state.selected.set(id, m);
        }
        renderBody();
        emit('onSelect');
    }

    /* ----- upload with progress cards ----- */
    function startUpload(files) {
        files = Array.from(files || []);
        if (!files.length) return;
        const rows = [];
        files.forEach((f, i) => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 rounded-2xl border border-line bg-white px-3 py-2.5';
            row.innerHTML = `<i class="ph ph-file-arrow-up text-xl text-aron-500"></i><div class="min-w-0 flex-1"><div class="flex items-center justify-between gap-2 text-xs"><span class="truncate font-bold text-ink">${esc(f.name)}</span><span data-pct class="shrink-0 font-bold text-slate-400">۰٪</span></div><div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100"><div data-bar class="h-full w-0 rounded-full bg-aron-600 transition-all"></div></div><p data-err class="mt-1 hidden text-xs font-semibold text-coral-600"></p></div>`;
            el.uploads.appendChild(row);
            rows[i] = row;
        });
        upload(files, {
            progress: (i, p) => { $('[data-bar]', rows[i]).style.width = p + '%'; $('[data-pct]', rows[i]).textContent = faNum(p) + '٪'; },
            done: (i, item) => {
                $('[data-bar]', rows[i]).style.width = '100%'; $('[data-pct]', rows[i]).textContent = 'انجام شد';
                setTimeout(() => rows[i].remove(), 900);
                state.items.unshift(item);
                if (picker && !opt.multiple) { state.selected.clear(); }
                if (picker) state.selected.set(item.id, item);
                state.counts.all = (state.counts.all || 0) + 1;
                const k = item.type; state.counts[k] = (state.counts[k] || 0) + 1;
                renderTypes(); renderBody(); emit('onSelect'); emit('onUploaded');
            },
            error: (i, msg) => {
                const e = $('[data-err]', rows[i]); e.textContent = msg; e.classList.remove('hidden');
                $('[data-bar]', rows[i]).classList.replace('bg-aron-600', 'bg-coral-500');
                const x = document.createElement('button'); x.type = 'button'; x.className = 'text-slate-400 hover:text-slate-700'; x.innerHTML = '<i class="ph ph-x"></i>'; x.onclick = () => rows[i].remove();
                rows[i].appendChild(x);
                toast(f2(files[i]) + ': ' + msg, 'error');
            },
        });
    }
    const f2 = (f) => (f.name.length > 28 ? f.name.slice(0, 26) + '…' : f.name);

    /* ----- events ----- */
    let timer;
    el.q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => { state.q = el.q.value.trim(); load(true); }, 300); });
    el.sort.addEventListener('change', () => { state.sort = el.sort.value; load(true); });
    el.file.addEventListener('change', () => { startUpload(el.file.files); el.file.value = ''; });
    root.addEventListener('click', (e) => {
        const t = e.target.closest('[data-lib-type]');
        if (t) { state.type = t.dataset.libType; load(true); return; }
        const v = e.target.closest('[data-lib-view]');
        if (v) { state.view = v.dataset.libView; try { localStorage.setItem('aron:media-view', state.view); } catch (er) { /* ignore */ } renderTypes(); renderBody(); return; }
        if (e.target.closest('[data-lib-loadmore]')) { load(false); return; }
        const c = e.target.closest('[data-id]');
        if (c && root.contains(c)) {
            const id = +c.dataset.id;
            if (opt.onOpen && !picker && !e.target.closest('[data-check]')) { opt.onOpen(state.items.find((x) => x.id === id)); return; }
            toggle(id);
        }
    });
    root.addEventListener('dblclick', (e) => {
        const c = e.target.closest('[data-id]');
        if (c && picker && opt.onConfirm) { const id = +c.dataset.id; if (!state.selected.has(id)) toggle(id); opt.onConfirm(); }
    });

    let depth = 0;
    const hasFiles = (e) => e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files');
    root.addEventListener('dragenter', (e) => { if (!hasFiles(e)) return; e.preventDefault(); depth++; el.drop.classList.remove('hidden'); el.drop.classList.add('grid'); });
    root.addEventListener('dragover', (e) => { if (hasFiles(e)) e.preventDefault(); });
    root.addEventListener('dragleave', (e) => { if (!hasFiles(e)) return; depth = Math.max(0, depth - 1); if (!depth) { el.drop.classList.add('hidden'); el.drop.classList.remove('grid'); } });
    root.addEventListener('drop', (e) => {
        if (!hasFiles(e)) return;
        e.preventDefault(); depth = 0; el.drop.classList.add('hidden'); el.drop.classList.remove('grid');
        startUpload(e.dataTransfer.files);
    });

    renderTypes();
    load(true);

    return {
        state,
        reload: () => load(true),
        clearSelection() { state.selected.clear(); renderBody(); emit('onSelect'); },
        selected: () => Array.from(state.selected.values()),
        upload: startUpload,
        replaceItem(item) { const i = state.items.findIndex((x) => x.id === item.id); if (i > -1) { state.items[i] = item; if (state.selected.has(item.id)) state.selected.set(item.id, item); renderBody(); } },
        removeIds(ids) { state.items = state.items.filter((x) => !ids.includes(x.id)); ids.forEach((id) => state.selected.delete(id)); renderBody(); emit('onSelect'); },
        setCounts(c) { state.counts = c; renderTypes(); },
    };
}

/* ------------------------------------------------------------------ picker */
function pick(opts = {}) {
    return new Promise((resolve) => {
        const wrap = document.createElement('div');
        wrap.className = 'fixed inset-0 flex items-end justify-center sm:items-center sm:p-6';
        wrap.style.zIndex = "20000"; wrap.setAttribute("role", "dialog");
        wrap.setAttribute('aria-modal', 'true');
        const insert = !!opts.insert;
        wrap.innerHTML = `
          <div class="absolute inset-0 bg-rail/60 backdrop-blur-[2px]" data-ap-close></div>
          <div class="relative flex h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-t-[1.75rem] bg-white shadow-adm-pop sm:h-[86vh] sm:rounded-[1.5rem]">
            <header class="flex items-center justify-between gap-3 border-b border-line px-5 py-3.5">
              <h3 class="text-base font-extrabold text-ink">${esc(opts.title || (opts.multiple ? 'انتخاب رسانه' : 'انتخاب فایل'))}</h3>
              <button type="button" data-ap-close class="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="بستن"><i class="ph ph-x text-lg"></i></button>
            </header>
            <div class="flex min-h-0 flex-1 flex-col px-4 pt-3 sm:px-5" data-ap-lib></div>
            <div class="hidden border-t border-line bg-slate-50/70 px-4 py-3 sm:px-5" data-ap-insert>
              <div class="grid gap-2.5 sm:grid-cols-4">
                <label class="block sm:col-span-2"><span class="a-label !mb-1">متن جایگزین (alt)</span><input class="a-input !h-10" data-ap-alt placeholder="توضیح کوتاه تصویر برای سئو و دسترس‌پذیری"></label>
                <label class="block sm:col-span-2"><span class="a-label !mb-1">کپشن (اختیاری)</span><input class="a-input !h-10" data-ap-caption></label>
                <label class="block"><span class="a-label !mb-1">اندازه</span><select class="a-select !h-10" data-ap-size><option value="large">بزرگ</option><option value="medium">متوسط</option><option value="full">اصلی</option></select></label>
                <label class="block"><span class="a-label !mb-1">چینش</span><select class="a-select !h-10" data-ap-align><option value="center">وسط</option><option value="right">راست</option><option value="left">چپ</option></select></label>
              </div>
            </div>
            <footer class="flex items-center justify-between gap-3 border-t border-line px-4 py-3 sm:px-5">
              <span class="text-xs font-bold text-slate-500" data-ap-count>چیزی انتخاب نشده</span>
              <div class="flex gap-2"><button type="button" data-ap-close class="a-btn a-btn-secondary a-btn-sm">انصراف</button><button type="button" data-ap-ok class="a-btn a-btn-primary a-btn-sm" disabled>${esc(opts.okLabel || (insert ? 'درج' : 'انتخاب'))}</button></div>
            </footer>
          </div>`;
        document.body.appendChild(wrap);
        document.body.classList.add('overflow-hidden');

        const ok = $('[data-ap-ok]', wrap);
        const count = $('[data-ap-count]', wrap);
        const box = $('[data-ap-insert]', wrap);
        const altIn = $('[data-ap-alt]', wrap);
        let closed = false;
        const close = (val) => {
            if (closed) return; closed = true;
            document.removeEventListener('keydown', onKey, true);
            wrap.remove();
            if (!$$('[data-modal]:not(.hidden)').length) document.body.classList.remove('overflow-hidden');
            resolve(val);
        };
        const onKey = (e) => { if (e.key === 'Escape') { e.stopPropagation(); close(null); } };
        document.addEventListener('keydown', onKey, true);

        const confirm = async () => {
            const sel = lib.selected();
            if (!sel.length) return;
            if (insert && sel.length === 1 && sel[0].type === 'image') {
                const m = sel[0];
                const o = { alt: altIn.value.trim(), caption: $('[data-ap-caption]', wrap).value.trim(), size: $('[data-ap-size]', wrap).value, align: $('[data-ap-align]', wrap).value };
                if (o.alt !== (m.alt || '') && routes().update) { // alt را روی خود رسانه هم ذخیره کن
                    api(routes().update.replace('__ID__', m.id), { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ title: m.title, alt: o.alt, caption: m.caption }) });
                    m.alt = o.alt;
                }
                close([Object.assign({}, m, { _insert: o })]);
                return;
            }
            close(sel);
        };

        const lib = createLibrary($('[data-ap-lib]', wrap), {
            mode: 'picker', multiple: !!opts.multiple, type: opts.type || '', lockType: !!opts.type,
            onSelect: (s) => {
                const n = s.selected.size;
                count.textContent = n ? (opts.multiple ? faNum(n) + ' مورد انتخاب شد' : 'یک مورد انتخاب شد') : 'چیزی انتخاب نشده';
                ok.disabled = !n;
                const one = n === 1 ? Array.from(s.selected.values())[0] : null;
                const showBox = insert && one && one.type === 'image';
                box.classList.toggle('hidden', !showBox);
                if (showBox && altIn.dataset.for !== String(one.id)) {
                    altIn.dataset.for = one.id; altIn.value = one.alt || ''; $('[data-ap-caption]', wrap).value = one.caption || '';
                }
            },
            onConfirm: confirm,
        });
        ok.addEventListener('click', confirm);
        wrap.addEventListener('click', (e) => { if (e.target.closest('[data-ap-close]')) close(null); });
    });
}

/* ------------------------------------------------------ declarative picker */
document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-media-pick]');
    if (b) {
        e.preventDefault();
        const items = await pick({ multiple: b.dataset.multiple === '1', type: b.dataset.type || '', title: b.dataset.title });
        if (!items || !items.length) return;
        const m = items[0];
        const mode = b.dataset.value || 'rel';
        const target = b.dataset.target && $(b.dataset.target);
        if (target) {
            target.value = mode === 'id' ? m.id : mode === 'url' ? m.url : m.rel;
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const prev = b.dataset.preview && $(b.dataset.preview);
        if (prev) { prev.src = m.medium || m.url; prev.classList.remove('hidden'); }
        b.dispatchEvent(new CustomEvent('media:picked', { bubbles: true, detail: { items } }));
        return;
    }
    const c = e.target.closest('[data-media-clear]');
    if (c) {
        const t = $(c.dataset.target); if (t) { t.value = ''; t.dispatchEvent(new Event('input', { bubbles: true })); t.dispatchEvent(new Event('change', { bubbles: true })); }
        const p = c.dataset.preview && $(c.dataset.preview); if (p) { p.src = ''; p.classList.add('hidden'); }
        c.dispatchEvent(new CustomEvent('media:cleared', { bubbles: true }));
    }
});

/* ------------------------------------------------------------ library page */
function copy(text, label) {
    const done = () => toast((label || 'آدرس') + ' کپی شد');
    if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done, () => fallback()); return; }
    fallback();
    function fallback() {
        const t = document.createElement('textarea'); t.value = text; t.style.position = 'fixed'; t.style.opacity = '0'; document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); done(); } catch (er) { toast('کپی نشد؛ آدرس را دستی کپی کنید.', 'error'); }
        t.remove();
    }
}

function initLibraryPage(page) {
    const host = $('[data-media-lib-host]', page);
    const drawer = $('[data-media-drawer]', page);
    const bulk = $('[data-media-bulk]', page);
    let current = null;
    let lib;

    const field = (n) => $('[data-f="' + n + '"]', drawer);
    const bulkBar = () => {
        const n = lib.state.selected.size;
        bulk.classList.toggle('hidden', !n);
        $('[data-bulk-count]', bulk).textContent = faNum(n) + ' مورد انتخاب شده';
    };

    function openDrawer(m) {
        current = m;
        drawer.classList.remove('translate-x-full', '-translate-x-full', 'pointer-events-none', 'opacity-0');
        drawer.classList.add('pointer-events-auto');
        $('[data-drawer-backdrop]', page).classList.remove('hidden');
        fill(m);
        api(routes().show.replace('__ID__', m.id)).then((r) => { if (r.ok && current && current.id === m.id) { renderUsage(r.json.usage || []); } });
    }
    function closeDrawer() {
        current = null;
        drawer.classList.add('pointer-events-none', 'opacity-0');
        drawer.classList.add(document.dir === 'rtl' ? '-translate-x-full' : 'translate-x-full');
        $('[data-drawer-backdrop]', page).classList.add('hidden');
    }
    function fill(m) {
        const pv = $('[data-f-preview]', drawer);
        if (m.type === 'image') pv.innerHTML = `<img src="${esc(m.medium || m.url)}" alt="" class="max-h-64 w-full rounded-2xl object-contain">`;
        else if (m.type === 'video') pv.innerHTML = `<video src="${esc(m.url)}" controls class="max-h-64 w-full rounded-2xl bg-black"></video>`;
        else if (m.type === 'audio') pv.innerHTML = `<audio src="${esc(m.url)}" controls class="w-full"></audio>`;
        else pv.innerHTML = `<div class="grid h-40 place-items-center rounded-2xl bg-slate-100 text-slate-400"><div class="text-center"><i class="ph ph-file-text text-5xl"></i><div class="mt-1 text-xs font-extrabold uppercase">${esc(m.ext)}</div></div></div>`;
        field('title').value = m.title || '';
        field('alt').value = m.alt || '';
        field('caption').value = m.caption || '';
        field('alt').closest('label').classList.toggle('hidden', m.type !== 'image');
        field('url').value = m.url;
        $('[data-f-info]', drawer).innerHTML = [
            ['نام فایل', `<span dir="ltr">${esc(m.file_name)}</span>`],
            ['نوع', `<span dir="ltr">${esc(m.mime)}</span>`],
            ['حجم', faNum(m.size_h)],
            m.width ? ['ابعاد', `<span dir="ltr">${faNum(m.width)} × ${faNum(m.height)}</span>`] : null,
            ['تاریخ آپلود', esc(m.date || '—')],
            m.by ? ['آپلود توسط', esc(m.by)] : null,
        ].filter(Boolean).map(([k, v]) => `<div class="flex items-center justify-between gap-3 py-1.5 text-xs"><dt class="text-slate-400">${k}</dt><dd class="min-w-0 truncate font-bold text-slate-700">${v}</dd></div>`).join('');
        $('[data-f-usage]', drawer).innerHTML = '<p class="text-xs text-slate-400">در حال بررسی…</p>';
        $('[data-f-variants]', drawer).innerHTML = m.type === 'image'
            ? [['webp', 'WebP'], ['large', 'بزرگ'], ['medium', 'متوسط'], ['thumb', 'بندانگشتی']].filter(([k]) => m[k]).map(([k, l]) => `<button type="button" data-copy="${esc(m[k])}" class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600 hover:bg-aron-50 hover:text-aron-700">کپی ${l}</button>`).join('')
            : '';
    }
    function renderUsage(list) {
        const box = $('[data-f-usage]', drawer);
        box.innerHTML = list.length
            ? '<ul class="space-y-1">' + list.map((u) => `<li class="flex items-center gap-2 text-xs"><i class="ph ph-link-simple text-aron-500"></i>${u.url ? `<a class="truncate font-bold text-aron-700 hover:underline" href="${esc(u.url)}">${esc(u.label)}</a>` : `<span class="truncate font-bold text-slate-600">${esc(u.label)}</span>`}</li>`).join('') + '</ul>'
            : '<p class="text-xs text-slate-400">هنوز در جایی استفاده نشده.</p>';
    }

    lib = createLibrary(host, {
        mode: 'page', multiple: true,
        onOpen: openDrawer,
        onSelect: bulkBar,
        onLoad: () => { const c = lib.state.counts; $$('[data-count]', page).forEach((n) => { n.textContent = faNum(c[n.dataset.count] ?? 0); }); },
    });

    drawer.addEventListener('click', (e) => {
        const c = e.target.closest('[data-copy]');
        if (c) { copy(c.dataset.copy); return; }
        if (e.target.closest('[data-drawer-close]')) closeDrawer();
    });
    $('[data-drawer-backdrop]', page).addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && current) closeDrawer(); });

    $('[data-act="copy-url"]', drawer).addEventListener('click', () => current && copy(current.url));
    $('[data-act="copy-rel"]', drawer).addEventListener('click', () => current && copy(current.rel));

    $('[data-act="save"]', drawer).addEventListener('click', async (ev) => {
        if (!current) return;
        const btn = ev.currentTarget; btn.disabled = true;
        const r = await api(routes().update.replace('__ID__', current.id), { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ title: field('title').value, alt: field('alt').value, caption: field('caption').value }) });
        btn.disabled = false;
        if (r.ok) { current = r.json.item; lib.replaceItem(current); toast('ذخیره شد'); } else toast((r.json.message) || 'ذخیره ناموفق بود', 'error');
    });

    $('[data-act="replace"]', drawer).addEventListener('change', async (ev) => {
        const f = ev.target.files[0]; ev.target.value = '';
        if (!f || !current) return;
        const fd = new FormData(); fd.append('file', f);
        toast('در حال جایگزینی فایل…', 'info');
        const r = await api(routes().replace.replace('__ID__', current.id), { method: 'POST', body: fd });
        if (r.ok) { current = r.json.item; lib.replaceItem(current); fill(current); renderUsage(r.json.usage || []); toast('فایل جایگزین شد'); } else toast(r.json.message || 'جایگزینی ناموفق بود', 'error');
    });

    async function removeOne(m, force) {
        const r = await api(routes().destroy.replace('__ID__', m.id) + (force ? '?force=1' : ''), { method: 'DELETE' });
        if (r.status === 409 && !force) {
            const names = (r.json.usage || []).slice(0, 5).map((u) => '• ' + u.label).join('\n');
            if (window.confirm('این فایل در ' + faNum((r.json.usage || []).length) + ' جا استفاده شده است:\n' + names + '\n\nبا حذف آن، تصویر در این بخش‌ها از کار می‌افتد. حذف شود؟')) return removeOne(m, true);
            return false;
        }
        if (r.ok) { lib.removeIds([m.id]); if (r.json.counts) lib.setCounts(r.json.counts); return true; }
        toast(r.json.message || 'حذف ناموفق بود', 'error');
        return false;
    }
    $('[data-act="delete"]', drawer).addEventListener('click', async () => {
        if (!current) return;
        if (!window.confirm('این فایل برای همیشه حذف شود؟')) return;
        if (await removeOne(current, false)) { closeDrawer(); toast('حذف شد'); }
    });

    $('[data-act="bulk-delete"]', bulk).addEventListener('click', async () => {
        const ids = lib.selected().map((m) => m.id);
        if (!ids.length || !window.confirm(faNum(ids.length) + ' فایل حذف شود؟ (فایل‌های در حال استفاده، رد می‌شوند)')) return;
        const r = await api(routes().bulkDestroy, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ids }) });
        if (r.ok) {
            lib.removeIds(r.json.deleted); if (r.json.counts) lib.setCounts(r.json.counts);
            toast(faNum(r.json.deleted.length) + ' فایل حذف شد' + (r.json.skipped.length ? ' — ' + faNum(r.json.skipped.length) + ' فایل در حال استفاده بود و حذف نشد' : ''), r.json.skipped.length ? 'warning' : 'success');
        } else toast('حذف ناموفق بود', 'error');
    });
    $('[data-act="bulk-clear"]', bulk).addEventListener('click', () => lib.clearSelection());
}

const init = () => {
    const page = $('[data-media-library]');
    if (page) initLibraryPage(page);
};
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();

window.AronMedia = { pick, upload, imageHtml, copy, esc };
