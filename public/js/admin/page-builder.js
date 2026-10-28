/*
 * صفحه‌ساز پنل (بدون build؛ مستقیم از public/js بارگذاری می‌شود). Vanilla JS.
 *  - [data-pb-preview]  پیش‌نمایش زنده‌ی صفحه در iframe مقیاس‌شده (دسکتاپ/موبایل، بارگذاری دوباره)
 *  - [data-pb-builder]  فهرست بلوک‌ها: جابه‌جایی با کشیدن (Sortable) + کتابخانه‌ی بلوک‌ها
 *  - [data-pb-form]     فرم ویرایش بلوک: repeater، انتخاب آیکن، تصویر، هشدار تغییرات ذخیره‌نشده
 * نشانه‌گذاری فیلدها همان ویرایشگر صفحات محصول است (data-pp-*؛ resources/views/admin/dash/product-pages/_*.blade.php).
 */
(function () {
    'use strict';

    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const fa = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
    const toast = (m, t = 'success') => (window.adminToast ? window.adminToast(m, t) : null);
    const store = {
        get: (k) => { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set: (k, v) => { try { localStorage.setItem(k, v); } catch (e) { /* private mode */ } },
    };

    /* ================================================================ preview */
    function initPreview(box) {
        const frame = $('[data-pb-frame]', box);
        const wrap = $('[data-pb-box]', box);
        let device = store.get('pb-device') === 'mobile' ? 'mobile' : 'desktop';

        function fit() {
            const width = device === 'mobile' ? 390 : 1280;
            const avail = wrap.clientWidth;
            const scale = Math.min(1, avail / width);
            const h = Math.max(420, window.innerHeight - 200);
            wrap.style.height = h + 'px';
            frame.style.width = width + 'px';
            frame.style.height = (h / scale) + 'px';
            frame.style.transform = 'scale(' + scale + ')';
            frame.style.right = (device === 'mobile' ? Math.max(0, (avail - width * scale) / 2) : 0) + 'px';
            $$('[data-pb-device]', box).forEach((b) => b.setAttribute('aria-pressed', b.dataset.pbDevice === device ? 'true' : 'false'));
        }
        $$('[data-pb-device]', box).forEach((b) => b.addEventListener('click', () => {
            device = b.dataset.pbDevice;
            store.set('pb-device', device);
            fit();
        }));
        $('[data-pb-reload]', box).addEventListener('click', () => reload());
        function reload() {
            try { frame.contentWindow.location.reload(); } catch (e) { frame.src = frame.src; }
        }
        // after load, jump to the #anchor (the preview has a sticky header)
        frame.addEventListener('load', () => {
            try {
                const hash = new URL(frame.src, location.href).hash;
                const el = hash && frame.contentDocument.getElementById(decodeURIComponent(hash.slice(1)));
                if (el) el.scrollIntoView({ block: 'start' });
            } catch (e) { /* cross-origin: ignore */ }
        });
        addEventListener('resize', fit);
        document.addEventListener('pb:reload-preview', reload);
        fit();
    }

    /* ================================================================ block list + library */
    function initBuilder(root) {
        const list = $('[data-pb-list]', root);
        if (list && window.Sortable) {
            window.Sortable.create(list, {
                handle: '[data-pb-handle]',
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd: async (ev) => {
                    if (ev.oldIndex === ev.newIndex) return;
                    const ids = $$(':scope > li[data-id]', list).map((li) => +li.dataset.id);
                    try {
                        const r = await window.adminFetch(root.dataset.sortUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ ids }),
                        });
                        const j = await r.json().catch(() => ({}));
                        if (!r.ok) throw new Error(j.message || 'ذخیره‌ی ترتیب انجام نشد.');
                        toast(j.message || 'ترتیب ذخیره شد.');
                        document.dispatchEvent(new Event('pb:reload-preview'));
                    } catch (e) {
                        toast(e.message, 'error');
                    }
                },
            });
        }

        // library: remember where the new block goes, then open the modal
        document.addEventListener('click', (e) => {
            const b = e.target.closest('[data-pb-add]');
            if (!b) return;
            e.preventDefault();
            $$('[data-pb-after]').forEach((i) => { i.value = b.dataset.after || ''; });
            window.adminModal && window.adminModal.open('pb-library');
        });
        $$('[data-pb-cat]').forEach((btn) => btn.addEventListener('click', () => {
            $$('[data-pb-cat]').forEach((x) => x.setAttribute('aria-pressed', x === btn ? 'true' : 'false'));
            $$('[data-pb-cat-panel]').forEach((p) => { p.hidden = p.dataset.pbCatPanel !== btn.dataset.pbCat; });
        }));
    }

    /* ================================================================ block form (schema fields) */
    function initForm(form) {
        const base = document.querySelector('meta[name="asset-base"]')?.content || (location.origin + '/');
        const imgUrl = (p) => (!p ? '' : /^(https?:)?\/\//.test(p) ? p : base.replace(/\/?$/, '/') + String(p).replace(/^\/+/, ''));

        /* ---- dirty state */
        let dirty = false;
        let submitting = false;
        const dot = $('[data-pb-dirty-dot]', form);
        const dirtyText = $('[data-pb-dirty-text]', form);
        const markDirty = () => {
            if (dirty) return;
            dirty = true;
            if (dot) dot.className = 'size-2 rounded-full bg-amber-500';
            if (dirtyText) dirtyText.textContent = 'تغییرات ذخیره‌نشده';
        };
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        addEventListener('beforeunload', (e) => { if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; } });

        /* ---- repeaters */
        const rowsOf = (rep) => $$(':scope > [data-pp-rows] > [data-pp-row]', rep);
        function rowLabel(rep, row, i) {
            const el = $('[data-pp-f="' + rep.dataset.labelKey + '"]', row);
            const v = el ? String(el.value || '').trim().replace(/\s+/g, ' ') : '';
            return '#' + fa(i + 1) + (v ? ' — ' + (v.length > 48 ? v.slice(0, 48) + '…' : v) : '');
        }
        function refresh(rep) {
            const rows = rowsOf(rep);
            const max = +rep.dataset.max || 99;
            rows.forEach((row, i) => {
                $('[data-pp-row-title]', row).textContent = rowLabel(rep, row, i);
                $('[data-pp-row-up]', row).disabled = i === 0;
                $('[data-pp-row-down]', row).disabled = i === rows.length - 1;
                $('[data-pp-row-dup]', row).disabled = rows.length >= max;
            });
            const count = $('[data-pp-count]', rep);
            if (count) count.textContent = fa(rows.length) + ' از ' + fa(max);
            $('[data-pp-empty]', rep).classList.toggle('hidden', rows.length > 0);
            const add = $('[data-pp-add]', rep);
            add.disabled = rows.length >= max;
        }
        function setRowOpen(row, open) {
            $('[data-pp-row-body]', row).classList.toggle('hidden', !open);
            $('[data-pp-row-toggle]', row).setAttribute('aria-expanded', open ? 'true' : 'false');
            $('[data-pp-caret]', row).classList.toggle('-rotate-90', !open);
        }
        function copyRow(src, dst) {
            $$('[data-pp-f]', src).forEach((el) => {
                const target = $('[data-pp-f="' + el.dataset.ppF + '"]', dst);
                if (!target) return;
                if (el.type === 'checkbox') target.checked = el.checked; else target.value = el.value;
            });
            $$('[data-pp-image]', dst).forEach((b) => syncImage(b, true));
            $$('[data-pp-icon]', dst).forEach(syncIcon);
        }
        function addRow(rep, after) {
            if (rowsOf(rep).length >= (+rep.dataset.max || 99)) return null;
            const n = +rep.dataset.next || 0;
            rep.dataset.next = n + 1;
            const t = document.createElement('template');
            t.innerHTML = $(':scope > [data-pp-tpl]', rep).innerHTML.split('__IDX__').join(String(n));
            const row = t.content.firstElementChild;
            if (after) { copyRow(after, row); after.after(row); } else { $(':scope > [data-pp-rows]', rep).append(row); }
            setRowOpen(row, true);
            refresh(rep);
            markDirty();
            return row;
        }
        $$('[data-pp-repeater]', form).forEach((rep) => {
            const rows = rowsOf(rep);
            rows.forEach((row) => setRowOpen(row, rows.length <= 3 || !!$('.text-coral-600', row)));
            refresh(rep);
        });

        /* ---- images (value is set by the media picker in admin.js: [data-media-pick]) */
        function syncImage(block, fromValue) {
            const val = $('input[type=hidden]', block).value;
            const img = $('img', block);
            if (fromValue) img.src = imgUrl(val);
            img.classList.toggle('hidden', !val);
            $('[data-pp-ph]', block).classList.toggle('hidden', !!val);
            $('[data-pp-clear]', block).classList.toggle('hidden', !val);
        }
        form.addEventListener('input', (e) => {
            const block = e.target.closest && e.target.closest('[data-pp-image]');
            if (block && e.target.type === 'hidden') syncImage(block, false);
        });
        // ids inside cloned rows must stay unique for the picker's data-target
        let uid = 0;
        form.addEventListener('click', (e) => {
            const pickBtn = e.target.closest('[data-media-pick], [data-media-clear]');
            if (!pickBtn) return;
            const block = pickBtn.closest('[data-pp-image]');
            if (!block) return;
            const input = $('input[type=hidden]', block);
            const img = $('img', block);
            if (!input.id || $$('#' + CSS.escape(input.id)).length > 1) {
                input.id = 'pbimg_' + (++uid) + '_' + Date.now();
                img.id = input.id + '_prev';
            }
            $$('[data-media-pick], [data-media-clear]', block).forEach((b) => { b.dataset.target = '#' + input.id; b.dataset.preview = '#' + img.id; });
        }, true);

        /* ---- icon picker (one shared popover: #pp-icon-pop) */
        const pop = $('#pp-icon-pop');
        let active = null;
        function syncIcon(picker) {
            const val = $('input[type=hidden]', picker).value;
            $('[data-pp-icon-cur]', picker).className = 'ph ' + (val || 'ph-plus');
            $('[data-pp-icon-label]', picker).textContent = val ? val.replace(/^ph-/, '') : 'انتخاب آیکن';
            $('[data-pp-icon-clear]', picker).classList.toggle('hidden', !val);
        }
        function setIcon(picker, val) {
            const input = $('input[type=hidden]', picker);
            input.value = val;
            syncIcon(picker);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        function placePop() {
            if (!active || !pop) return;
            const r = $('[data-pp-icon-btn]', active).getBoundingClientRect();
            const w = pop.offsetWidth, h = pop.offsetHeight;
            let top = r.bottom + 6;
            if (top + h > innerHeight - 8 && r.top - h - 6 > 8) top = r.top - h - 6;
            const left = Math.max(8, Math.min(r.right - w, innerWidth - w - 8));
            pop.style.top = Math.max(8, top) + 'px';
            pop.style.left = left + 'px';
        }
        function openPop(picker) {
            closePop(false);
            active = picker;
            const cur = $('input[type=hidden]', picker).value;
            $$('[data-pp-icon-pick]', pop).forEach((b) => b.setAttribute('aria-pressed', cur && b.dataset.ppIconPick === cur ? 'true' : 'false'));
            pop.classList.remove('hidden');
            $('[data-pp-icon-btn]', picker).setAttribute('aria-expanded', 'true');
            placePop();
            const sel = $('[aria-pressed="true"]', pop) || $('[data-pp-icon-pick]:not([data-pp-icon-pick=""])', pop);
            if (sel) { sel.focus({ preventScroll: true }); sel.scrollIntoView({ block: 'nearest' }); }
        }
        function closePop(refocus) {
            if (!pop || !active) return;
            const btn = $('[data-pp-icon-btn]', active);
            btn.setAttribute('aria-expanded', 'false');
            pop.classList.add('hidden');
            if (refocus) btn.focus({ preventScroll: true });
            active = null;
        }
        if (pop) {
            pop.addEventListener('click', (e) => {
                const b = e.target.closest('[data-pp-icon-pick]');
                if (!b || !active) return;
                setIcon(active, b.dataset.ppIconPick);
                closePop(true);
            });
            document.addEventListener('mousedown', (e) => {
                if (active && !pop.contains(e.target) && !e.target.closest('[data-pp-icon-btn]')) closePop(false);
            });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && active) { e.preventDefault(); closePop(true); } });
            addEventListener('resize', placePop);
            addEventListener('scroll', placePop, true);
        }

        /* ---- delegated clicks */
        form.addEventListener('click', (e) => {
            const q = (s) => e.target.closest(s);
            let el;
            if ((el = q('[data-pp-add]'))) {
                const row = addRow(el.closest('[data-pp-repeater]'));
                if (row) { const f = $('input:not([type=hidden]), textarea, select', row); f && f.focus(); }
                return;
            }
            if ((el = q('[data-pp-row-toggle]'))) { setRowOpen(el.closest('[data-pp-row]'), el.getAttribute('aria-expanded') !== 'true'); return; }
            if ((el = q('[data-pp-row-up], [data-pp-row-down], [data-pp-row-dup], [data-pp-row-del]'))) {
                const row = el.closest('[data-pp-row]');
                const rep = row.closest('[data-pp-repeater]');
                if (el.matches('[data-pp-row-up]') && row.previousElementSibling) row.previousElementSibling.before(row);
                else if (el.matches('[data-pp-row-down]') && row.nextElementSibling) row.nextElementSibling.after(row);
                else if (el.matches('[data-pp-row-dup]')) { addRow(rep, row); return; }
                else if (el.matches('[data-pp-row-del]')) { row.remove(); }
                refresh(rep);
                markDirty();
                return;
            }
            if ((el = q('[data-pp-icon-btn]'))) {
                const picker = el.closest('[data-pp-icon]');
                if (active === picker) closePop(false); else openPop(picker);
                return;
            }
            if ((el = q('[data-pp-icon-clear]'))) { setIcon(el.closest('[data-pp-icon]'), ''); }
        });

        // live row titles
        form.addEventListener('input', (e) => {
            const row = e.target.closest && e.target.closest('[data-pp-row]');
            if (!row) return;
            const rep = row.closest('[data-pp-repeater]');
            if (e.target.dataset.ppF === rep.dataset.labelKey) $('[data-pp-row-title]', row).textContent = rowLabel(rep, row, rowsOf(rep).indexOf(row));
        });

        /* ---- submit: re-index repeater rows by their order on screen */
        form.addEventListener('submit', () => {
            submitting = true;
            $$('[data-pp-repeater]', form).forEach((rep) => {
                const name = rep.dataset.name;
                const re = new RegExp('^' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[[^\\]]*\\]');
                rowsOf(rep).forEach((row, i) => {
                    $$('[name]', row).forEach((el) => { el.name = el.name.replace(re, name + '[' + i + ']'); });
                });
            });
        });

        const firstErr = $('.text-coral-600', form);
        if (firstErr) setTimeout(() => firstErr.scrollIntoView({ block: 'center' }), 150);
    }

    function boot() {
        $$('[data-pb-preview]').forEach(initPreview);
        $$('[data-pb-builder]').forEach(initBuilder);
        $$('[data-pb-form]').forEach(initForm);
    }
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
