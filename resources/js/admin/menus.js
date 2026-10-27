/* Menu item tree builder (flat list + depth, max depth 2). Acts on [data-menu-builder] only. Vanilla JS. */
(function () {
    const root = document.querySelector('[data-menu-builder]');
    if (!root) return;

    const MAX = 2;
    const $ = (s, r = root) => r.querySelector(s);
    const list = $('[data-tree]');
    const emptyEl = $('[data-tree-empty]');
    const saveBtn = $('[data-save]');
    const stateEl = $('[data-state]');
    let uid = 1;
    let dirty = false;
    const toast = (m, t) => (window.adminToast ? window.adminToast(m, t) : alert(m));
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    let items = JSON.parse($('[data-initial]').textContent || '[]').map((i) => ({ uid: uid++, open: false, ...i }));

    const TYPE_LABEL = {
        'App\\Models\\Post': 'نوشته', 'App\\Models\\Portfolio': 'پروژه',
        'App\\Models\\Category': 'دسته‌بندی', 'App\\Models\\LandingPage': 'صفحه',
    };

    function normalize() {
        items.forEach((it, i) => {
            it.depth = Math.max(0, Math.min(MAX, it.depth | 0));
            it.depth = i === 0 ? 0 : Math.min(it.depth, items[i - 1].depth + 1);
        });
    }
    function blockEnd(i) { let j = i + 1; while (j < items.length && items[j].depth > items[i].depth) j++; return j; } // exclusive
    function mark() { dirty = true; if (stateEl) { stateEl.textContent = 'تغییرات ذخیره نشده'; stateEl.classList.remove('hidden'); } }

    function render() {
        normalize();
        list.innerHTML = '';
        items.forEach((it, i) => {
            const li = document.createElement('li');
            li.dataset.uid = it.uid;
            li.className = 'rounded-2xl border border-line bg-white shadow-adm transition';
            li.style.marginInlineStart = it.depth * 1.75 + 'rem';
            const type = it.model_type ? TYPE_LABEL[it.model_type] || 'پیوند' : 'پیوند دلخواه';
            li.innerHTML = `
              <div class="flex items-center gap-2 p-2.5">
                <button type="button" data-handle class="grid size-9 shrink-0 cursor-grab touch-none place-items-center rounded-xl text-slate-400 hover:bg-slate-100 active:cursor-grabbing" aria-label="جابجایی" title="بکشید"><i class="ph ph-dots-six-vertical text-xl"></i></button>
                <div class="min-w-0 flex-1"><div class="truncate text-sm font-bold text-ink" data-label>${esc(it.title) || '(بدون عنوان)'}</div>
                  <div class="truncate text-[11px] text-slate-400" dir="ltr">${esc(it.url)}</div></div>
                <span class="hidden rounded-full bg-aron-50 px-2 py-1 text-[11px] font-bold text-aron-700 sm:inline">${type}</span>
                <div class="flex items-center">
                  <button type="button" data-act="up" ${i === 0 ? 'disabled' : ''} class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 disabled:opacity-30" aria-label="بالا"><i class="ph ph-arrow-up"></i></button>
                  <button type="button" data-act="down" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100" aria-label="پایین"><i class="ph ph-arrow-down"></i></button>
                  <button type="button" data-act="out" ${it.depth === 0 ? 'disabled' : ''} class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 disabled:opacity-30" aria-label="اصلی‌تر" title="اصلی‌تر"><i class="ph ph-arrow-line-right"></i></button>
                  <button type="button" data-act="in" ${i === 0 || it.depth >= MAX || it.depth > items[i - 1].depth ? 'disabled' : ''} class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 disabled:opacity-30" aria-label="زیرمجموعه" title="زیرمجموعه"><i class="ph ph-arrow-line-left"></i></button>
                  <button type="button" data-act="toggle" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100" aria-label="ویرایش"><i class="ph ph-${it.open ? 'caret-up' : 'pencil-simple'}"></i></button>
                </div>
              </div>
              <div class="${it.open ? '' : 'hidden'} space-y-3 border-t border-line p-3.5" data-edit>
                <div><label class="a-label">متن ناوبری</label><input class="a-input" data-f="title" value="${esc(it.title)}" maxlength="190"></div>
                <div><label class="a-label">نشانی (URL)</label><input class="a-input" dir="ltr" data-f="url" value="${esc(it.url)}" maxlength="500"></div>
                <div class="flex justify-end"><button type="button" data-act="del" class="a-btn a-btn-danger-soft a-btn-sm"><i class="ph ph-trash"></i>حذف از منو</button></div>
              </div>`;
            list.appendChild(li);
        });
        emptyEl.classList.toggle('hidden', items.length > 0);
        const c = $('[data-count]'); if (c) c.textContent = items.length;
    }

    const find = (li) => items.findIndex((x) => x.uid == li.dataset.uid);

    function moveBlock(i, to) { // to = insertion index in the array AFTER removing the block
        const e = blockEnd(i);
        const block = items.splice(i, e - i);
        items.splice(to, 0, ...block);
    }

    list.addEventListener('click', (ev) => {
        const b = ev.target.closest('[data-act]'); if (!b) return;
        const li = b.closest('li'); const i = find(li); if (i < 0) return;
        const act = b.dataset.act, it = items[i], d = it.depth, e = blockEnd(i);
        if (act === 'toggle') { it.open = !it.open; render(); return; }
        if (act === 'up') {
            let k = i - 1; while (k >= 0 && items[k].depth > d) k--;
            if (k >= 0 && items[k].depth === d) { moveBlock(i, k); mark(); render(); }
        } else if (act === 'down') {
            if (e < items.length && items[e].depth === d) { const f = blockEnd(e); moveBlock(i, i + (f - e)); mark(); render(); }
        } else if (act === 'out') {
            for (let k = i; k < e; k++) items[k].depth--; mark(); render();
        } else if (act === 'in') {
            const maxd = Math.max(...items.slice(i, e).map((x) => x.depth));
            if (maxd < MAX && i > 0 && d <= items[i - 1].depth) { for (let k = i; k < e; k++) items[k].depth++; mark(); render(); }
        } else if (act === 'del') {
            const kids = e - i - 1;
            if (!confirm(kids ? 'این آیتم زیرمجموعه دارد. با حذف آن، زیرمجموعه‌ها یک سطح بالاتر می‌آیند. ادامه می‌دهید؟' : 'این آیتم از منو حذف شود؟')) return;
            for (let k = i + 1; k < e; k++) items[k].depth--;
            items.splice(i, 1); mark(); render();
        }
    });
    list.addEventListener('input', (ev) => {
        const f = ev.target.closest('[data-f]'); if (!f) return;
        const li = f.closest('li'); const it = items[find(li)]; if (!it) return;
        it[f.dataset.f] = f.value; mark();
        if (f.dataset.f === 'title') li.querySelector('[data-label]').textContent = f.value || '(بدون عنوان)';
        else li.querySelector('[dir=ltr].truncate').textContent = f.value;
    });

    /* ---------- drag & drop (pointer events: mouse + touch) ---------- */
    let drag = null;
    list.addEventListener('pointerdown', (ev) => {
        const h = ev.target.closest('[data-handle]'); if (!h) return;
        const li = h.closest('li'); const i = find(li);
        ev.preventDefault();
        const e = blockEnd(i);
        const rows = Array.from(list.children);
        drag = { i, e, rows, lis: rows.slice(i, e), target: i, line: document.createElement('div') };
        drag.line.className = 'pointer-events-none h-1 rounded-full bg-aron-500';
        drag.lis.forEach((x) => x.classList.add('opacity-40'));
        h.setPointerCapture(ev.pointerId);
        h.dataset.dragging = '1';
    });
    list.addEventListener('pointermove', (ev) => {
        if (!drag) return;
        const y = ev.clientY;
        // candidate insertion slots: before each row not inside the dragged block, or at the end
        let target = items.length, before = null;
        for (let k = 0; k < drag.rows.length; k++) {
            if (k >= drag.i && k < drag.e) continue;
            const r = drag.rows[k].getBoundingClientRect();
            if (y < r.top + r.height / 2) { target = k; before = drag.rows[k]; break; }
        }
        drag.target = target;
        if (before) list.insertBefore(drag.line, before); else list.appendChild(drag.line);
        const r = list.getBoundingClientRect();
        if (ev.clientY < 90) window.scrollBy(0, -14); else if (ev.clientY > innerHeight - 90) window.scrollBy(0, 14);
    });
    function endDrag() {
        if (!drag) return;
        const { i, e, target } = drag; drag.line.remove();
        drag = null;
        if (target < i || target > e) { moveBlock(i, target > e ? target - (e - i) : target); mark(); }
        render();
    }
    list.addEventListener('pointerup', endDrag);
    list.addEventListener('pointercancel', endDrag);

    /* ---------- adding items ---------- */
    function exists(model, id) { return items.some((x) => x.model_type === model && String(x.model_id) === String(id)); }
    function add(it) { items.push({ uid: uid++, open: false, depth: 0, ...it }); }

    root.addEventListener('click', (ev) => {
        const addSel = ev.target.closest('[data-add-selected]');
        if (addSel) {
            const box = addSel.closest('[data-source]');
            let dup = 0, n = 0;
            box.querySelectorAll('input[type=checkbox]:checked').forEach((c) => {
                if (exists(c.dataset.model, c.dataset.id)) dup++; else { add({ title: c.dataset.title, url: c.dataset.url, model_type: c.dataset.model, model_id: c.dataset.id }); n++; }
                c.checked = false;
            });
            if (dup) toast(dup + ' مورد قبلاً در منو بود و دوباره اضافه نشد.', 'warning');
            if (n) { mark(); render(); toast(n + ' آیتم افزوده شد.', 'success'); } else if (!dup) toast('ابتدا چند مورد را تیک بزنید.', 'info');
            return;
        }
        if (ev.target.closest('[data-add-custom]')) {
            const t = $('[data-custom-title]'), u = $('[data-custom-url]');
            if (!t.value.trim()) { toast('متن پیوند را وارد کنید.', 'error'); t.focus(); return; }
            add({ title: t.value.trim(), url: u.value.trim() || '#', model_type: null, model_id: null });
            t.value = ''; u.value = ''; mark(); render(); return;
        }
        const quick = ev.target.closest('[data-quick]');
        if (quick) { add({ title: quick.dataset.title, url: quick.dataset.quick, model_type: null, model_id: null }); mark(); render(); }
    });
    root.querySelectorAll('[data-source-filter]').forEach((inp) => inp.addEventListener('input', () => {
        const q = inp.value.trim().toLowerCase();
        inp.closest('[data-source]').querySelectorAll('[data-src-item]').forEach((li) => li.classList.toggle('hidden', q && !li.textContent.toLowerCase().includes(q)));
    }));
    const sw = $('[data-menu-switch]');
    if (sw) sw.addEventListener('change', () => { if (!dirty || confirm('تغییرات ذخیره‌نشده از بین می‌رود. ادامه؟')) location.href = sw.value; else sw.value = sw.dataset.current; });

    /* ---------- save ---------- */
    saveBtn.addEventListener('click', async () => {
        normalize();
        if (items.some((x) => !x.title.trim())) { toast('متن همه آیتم‌ها باید پر باشد.', 'error'); return; }
        saveBtn.disabled = true; const label = saveBtn.innerHTML; saveBtn.innerHTML = 'در حال ذخیره…';
        try {
            const r = await adminFetch(root.dataset.saveUrl, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items: items.map((x) => ({ title: x.title, url: x.url, depth: x.depth, model_type: x.model_type || null, model_id: x.model_id || null })) }),
            });
            const j = await r.json().catch(() => ({}));
            if (r.ok) { dirty = false; stateEl?.classList.add('hidden'); toast(j.message || 'ذخیره شد.', 'success'); }
            else toast(j.message || 'ذخیره‌سازی ناموفق بود.', 'error');
        } catch (e) { toast('ارتباط با سرور برقرار نشد.', 'error'); }
        saveBtn.disabled = false; saveBtn.innerHTML = label;
    });
    window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    render();
})();
