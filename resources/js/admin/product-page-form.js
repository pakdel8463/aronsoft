/*
 * Product landing page editor (admin/dash/product-pages/form.blade.php).
 * Section reorder / collapse / enable, schema-driven repeaters (add, move, duplicate, remove, re-index on submit),
 * shared icon picker popover, media-library image blocks, brand colours, slug helper, SEO counters + snippet.
 * Page-scoped: does nothing unless a [data-pp-form] element exists. Vanilla JS.
 */
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const fa = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
const HEX = /^#[0-9a-f]{6}$/i;

function slugify(v) {
    return String(v || '')
        .toLowerCase()
        .replace(/[^a-z0-9؀-ۿ]+/g, '-')
        .replace(/-{2,}/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 100);
}

function onAccent(hex) {
    const n = parseInt(hex.slice(1), 16);
    const lum = (0.299 * ((n >> 16) & 255) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) / 255;
    return lum > 0.72 ? '#0F172A' : '#FFFFFF';
}

function init(form) {
    const base = form.dataset.base || '/';
    const imgUrl = (p) => (!p ? '' : /^(https?:)?\/\//.test(p) ? p : base + String(p).replace(/^\/+/, ''));
    const live = $('[data-pp-live]', form);
    const announce = (m) => { if (!live) return; live.textContent = ''; setTimeout(() => { live.textContent = m; }, 30); };

    /* ---------------------------------------------------------------- dirty state */
    let dirty = false;
    let submitting = false;
    const dot = $('[data-pp-dirty-dot]', form);
    const dirtyText = $('[data-pp-dirty-text]', form);
    const markDirty = () => {
        if (dirty) return;
        dirty = true;
        dot && dot.classList.replace('bg-slate-300', 'bg-amber-500');
        if (dirtyText) dirtyText.textContent = 'تغییرات ذخیره‌نشده';
    };
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    window.addEventListener('beforeunload', (e) => { if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; } });

    /* ---------------------------------------------------------------- sections */
    const secWrap = $('[data-pp-sections]', form);
    const sections = () => $$('[data-pp-section]', secWrap);

    function setOpen(sec, open) {
        const btn = $('[data-pp-toggle]', sec);
        $('[data-pp-body]', sec).classList.toggle('hidden', !open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        $('[data-pp-caret]', sec).classList.toggle('rotate-180', open);
    }
    function setEnabled(sec, on) {
        sec.dataset.enabled = on ? '1' : '0';
        sec.classList.toggle('bg-slate-50/70', !on);
        $('[data-pp-sec-icon]', sec).classList.toggle('opacity-50', !on);
        $('[data-pp-off-badge]', sec).classList.toggle('hidden', on);
    }
    function updateMoveButtons() {
        sections().forEach((sec) => {
            if (sec.dataset.locked) return;
            const prev = sec.previousElementSibling;
            const next = sec.nextElementSibling;
            $('[data-pp-up]', sec).disabled = !prev || !!prev.dataset.locked;
            $('[data-pp-down]', sec).disabled = !next || !!next.dataset.locked;
        });
    }
    sections().forEach((sec) => {
        setOpen(sec, $('[data-pp-toggle]', sec).getAttribute('aria-expanded') === 'true');
        setEnabled(sec, sec.dataset.enabled === '1');
    });
    updateMoveButtons();

    function moveSection(sec, dir, btn) {
        const sib = dir < 0 ? sec.previousElementSibling : sec.nextElementSibling;
        if (!sib || sib.dataset.locked) return;
        if (dir < 0) sib.before(sec); else sib.after(sec);
        updateMoveButtons();
        const target = btn.disabled ? $(dir < 0 ? '[data-pp-down]' : '[data-pp-up]', sec) : btn;
        target && target.focus({ preventScroll: true });
        sec.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        const pos = sections().indexOf(sec) + 1;
        announce('بخش «' + $('[id$="_title"]', sec).textContent.trim() + '» به جایگاه ' + fa(pos) + ' منتقل شد.');
        markDirty();
    }

    /* ---------------------------------------------------------------- repeaters */
    const rowsOf = (rep) => $$(':scope > [data-pp-rows] > [data-pp-row]', rep);

    function rowLabel(rep, row, i) {
        const el = $('[data-pp-f="' + rep.dataset.labelKey + '"]', row);
        const v = el ? el.value.trim().replace(/\s+/g, ' ') : '';
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
        $('[data-pp-count]', rep).textContent = fa(rows.length) + ' از ' + fa(max);
        $('[data-pp-empty]', rep).classList.toggle('hidden', rows.length > 0);
        const add = $('[data-pp-add]', rep);
        add.disabled = rows.length >= max;
        add.title = rows.length >= max ? 'به حداکثر تعداد مجاز رسیده‌اید' : '';
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
        const rows = rowsOf(rep);
        if (rows.length >= (+rep.dataset.max || 99)) return null;
        const n = +rep.dataset.next || 0;
        rep.dataset.next = n + 1;
        const t = document.createElement('template');
        t.innerHTML = $('[data-pp-tpl]', rep).innerHTML.split('__IDX__').join(String(n));
        const row = t.content.firstElementChild;
        if (after) {
            copyRow(after, row);
            after.after(row);
        } else {
            $(':scope > [data-pp-rows]', rep).append(row);
        }
        setRowOpen(row, true);
        refresh(rep);
        markDirty();
        return row;
    }

    // collapse long lists on load (rows with errors stay open)
    $$('[data-pp-repeater]', form).forEach((rep) => {
        const rows = rowsOf(rep);
        rows.forEach((row) => {
            const hasErr = !!$('.has-error, .text-coral-600', row);
            setRowOpen(row, rows.length <= 4 || hasErr);
        });
        refresh(rep);
    });

    /* ---------------------------------------------------------------- images */
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

    /* ---------------------------------------------------------------- icon picker (one shared popover) */
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
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function placePop() {
        if (!active || !pop) return;
        const r = $('[data-pp-icon-btn]', active).getBoundingClientRect();
        const w = pop.offsetWidth, h = pop.offsetHeight;
        let top = r.bottom + 6;
        if (top + h > innerHeight - 8 && r.top - h - 6 > 8) top = r.top - h - 6;
        let left = document.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl' ? r.right - w : r.left;
        left = Math.max(8, Math.min(left, innerWidth - w - 8));
        pop.style.top = Math.max(8, top) + 'px';
        pop.style.left = left + 'px';
    }
    function openPop(picker) {
        if (!pop) return;
        closePop(false);
        active = picker;
        const cur = $('input[type=hidden]', picker).value;
        $$('[data-pp-icon-pick]', pop).forEach((b) => b.setAttribute('aria-pressed', b.dataset.ppIconPick === cur && cur ? 'true' : 'false'));
        pop.classList.remove('hidden');
        $('[data-pp-icon-btn]', picker).setAttribute('aria-expanded', 'true');
        placePop();
        const sel = $('[aria-pressed="true"]', pop) || $('[data-pp-icon-pick]:not([data-pp-icon-pick=""])', pop);
        sel && sel.focus({ preventScroll: true });
        sel && sel.scrollIntoView({ block: 'nearest' });
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
            const picker = active;
            setIcon(picker, b.dataset.ppIconPick);
            closePop(true);
        });
        pop.addEventListener('keydown', (e) => {
            const items = $$('[data-pp-icon-pick]', pop);
            const i = items.indexOf(document.activeElement);
            const step = { ArrowLeft: 1, ArrowRight: -1, ArrowDown: 7, ArrowUp: -7 }[e.key]; // RTL grid: left = next
            if (step && i > -1) {
                e.preventDefault();
                const n = items[i + step];
                if (n) n.focus();
            }
        });
        document.addEventListener('mousedown', (e) => {
            if (active && !pop.contains(e.target) && !e.target.closest('[data-pp-icon-btn]')) closePop(false);
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && active) { e.preventDefault(); closePop(true); } });
        addEventListener('resize', placePop);
        addEventListener('scroll', placePop, true);
    }

    /* ---------------------------------------------------------------- delegated clicks */
    form.addEventListener('click', (e) => {
        const t = e.target;
        const q = (s) => t.closest(s);
        let el;

        if ((el = q('[data-pp-toggle]'))) { setOpen(el.closest('[data-pp-section]'), el.getAttribute('aria-expanded') !== 'true'); return; }
        if ((el = q('[data-pp-up]'))) { moveSection(el.closest('[data-pp-section]'), -1, el); return; }
        if ((el = q('[data-pp-down]'))) { moveSection(el.closest('[data-pp-section]'), 1, el); return; }
        if ((el = q('[data-pp-expand]'))) { const open = el.dataset.ppExpand === 'all'; sections().forEach((s) => setOpen(s, open)); return; }

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
            else if (el.matches('[data-pp-row-dup]')) { const c = addRow(rep, row); c && $('[data-pp-row-toggle]', c).focus(); return; }
            else if (el.matches('[data-pp-row-del]')) {
                const sibling = row.nextElementSibling || row.previousElementSibling;
                row.remove();
                refresh(rep);
                markDirty();
                (sibling ? $('[data-pp-row-toggle]', sibling) : $('[data-pp-add]', rep)).focus();
                return;
            }
            refresh(rep);
            el.disabled ? $(el.matches('[data-pp-row-up]') ? '[data-pp-row-down]' : '[data-pp-row-up]', row).focus() : el.focus();
            markDirty();
            return;
        }
        if ((el = q('[data-pp-icon-btn]'))) {
            const picker = el.closest('[data-pp-icon]');
            if (active === picker) closePop(false); else openPop(picker);
            return;
        }
        if ((el = q('[data-pp-icon-clear]'))) { setIcon(el.closest('[data-pp-icon]'), ''); return; }
    });

    // live row titles
    form.addEventListener('input', (e) => {
        const row = e.target.closest && e.target.closest('[data-pp-row]');
        if (!row) return;
        const rep = row.closest('[data-pp-repeater]');
        if (e.target.dataset.ppF === rep.dataset.labelKey) $('[data-pp-row-title]', row).textContent = rowLabel(rep, row, rowsOf(rep).indexOf(row));
    });

    // enabled switch
    form.addEventListener('change', (e) => {
        if (e.target.matches && e.target.matches('[data-pp-enabled]')) setEnabled(e.target.closest('[data-pp-section]'), e.target.checked);
    });

    /* ---------------------------------------------------------------- brand colours + preview */
    const accentTxt = $('[data-pp-color-text="accent"]', form);
    const accent2Txt = $('[data-pp-color-text="accent_2"]', form);
    const bg = $('[data-pp-brand-bg]', form);
    const btn = $('[data-pp-brand-btn]', form);
    const nameEl = $('#pp_name', form);
    function renderBrand() {
        const a = HEX.test(accentTxt.value.trim()) ? accentTxt.value.trim() : '#2F4FF5';
        const b = HEX.test(accent2Txt.value.trim()) ? accent2Txt.value.trim() : a;
        bg.style.background = 'linear-gradient(135deg,' + a + ',' + b + ')';
        bg.style.color = onAccent(a);
        btn.style.background = a;
        btn.style.color = onAccent(a);
        $('[data-pp-brand-name]', form).textContent = nameEl.value.trim() || 'نام محصول';
        $('[data-pp-brand-name]', form).style.color = a;
        const iconVal = $('#pp_icon', form).value;
        $('[data-pp-brand-icon]', form).className = 'ph ' + (iconVal || 'ph-rocket-launch');
    }
    $$('[data-pp-color]', form).forEach((picker) => {
        const txt = $('[data-pp-color-text="' + picker.dataset.ppColor + '"]', form);
        picker.addEventListener('input', () => { txt.value = picker.value.toUpperCase(); txt.dispatchEvent(new Event('input', { bubbles: true })); });
        txt.addEventListener('input', () => { if (HEX.test(txt.value.trim())) picker.value = txt.value.trim().toLowerCase(); renderBrand(); });
        txt.addEventListener('blur', () => {
            let v = txt.value.trim().toUpperCase();
            if (/^[0-9A-F]{6}$/.test(v)) v = '#' + v;
            txt.value = v;
            if (HEX.test(v)) picker.value = v.toLowerCase();
            renderBrand();
        });
    });
    nameEl.addEventListener('input', renderBrand);
    $('#pp_icon', form).addEventListener('input', renderBrand);
    renderBrand();

    /* ---------------------------------------------------------------- slug helper */
    const slug = $('#pp_slug', form);
    const nameEn = $('#pp_name_en', form);
    let slugTouched = slug.value.trim() !== '';
    slug.addEventListener('input', () => { slugTouched = slug.value.trim() !== ''; });
    slug.addEventListener('blur', () => { slug.value = slugify(slug.value); renderSnippet(); });
    nameEn.addEventListener('input', () => { if (!slugTouched) { slug.value = slugify(nameEn.value); renderSnippet(); } });

    /* ---------------------------------------------------------------- SEO counters + snippet */
    const seoTitle = $('#pp_seo_title', form);
    const seoDesc = $('#pp_seo_description', form);
    const tagline = $('#pp_tagline', form);
    function renderCounters() {
        $$('[data-pp-count-for]', form).forEach((c) => {
            const inp = document.getElementById(c.dataset.ppCountFor);
            const limit = +c.dataset.limit;
            const len = inp.value.length;
            c.textContent = fa(len) + ' / ' + fa(limit);
            c.classList.toggle('text-coral-600', len > limit * 1.2);
            c.classList.toggle('text-amber-600', len > limit && len <= limit * 1.2);
            const bar = $('[data-pp-bar-for="' + c.dataset.ppCountFor + '"]', form);
            if (bar) {
                bar.style.width = Math.min(100, (len / limit) * 100) + '%';
                bar.className = 'h-full rounded-full transition-all ' + (len > limit * 1.2 ? 'bg-coral-500' : len > limit ? 'bg-amber-500' : 'bg-mint-500');
            }
        });
    }
    function renderSnippet() {
        const host = form.dataset.site || 'example.com';
        $('[data-pp-snip-url]', form).textContent = host + ' › products › ' + (slug.value.trim() || slugify(nameEn.value) || '…');
        $('[data-pp-snip-title]', form).textContent = seoTitle.value.trim() || nameEl.value.trim() || 'عنوان صفحه';
        $('[data-pp-snip-desc]', form).textContent = seoDesc.value.trim() || tagline.value.trim() || '';
    }
    [seoTitle, seoDesc, tagline, nameEl, nameEn, slug].forEach((el) => el.addEventListener('input', () => { renderCounters(); renderSnippet(); }));
    renderCounters();
    renderSnippet();

    /* ---------------------------------------------------------------- submit: re-index repeaters by DOM order */
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

    // scroll to the first validation error
    const firstErr = $('.has-error, [data-pp-body] .text-coral-600', form);
    if (firstErr) setTimeout(() => firstErr.scrollIntoView({ block: 'center' }), 120);
}

const boot = () => { const f = document.querySelector('[data-pp-form]'); if (f) init(f); };
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
