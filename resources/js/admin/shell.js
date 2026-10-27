/*
 * Admin shell behaviour — plain JS, no dependencies (sidebar, drawer, menus, quick search, toasts).
 * Loaded by resources/js/admin.js. Safe to include more than once.
 */
(function () {
    if (window.__admShell) return;
    window.__admShell = true;

    const body = document.body;
    const KEY = 'aron:admin-sidebar-collapsed';
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* ignore */ } },
    };
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    /* ---------- sidebar collapse (desktop) + drawer (mobile) ---------- */
    if (store.get(KEY) === '1') body.classList.add('adm-collapsed');

    const closeDrawer = () => body.classList.remove('adm-drawer-open');
    document.addEventListener('click', (e) => {
        const t = e.target.closest('[data-adm-sidebar-toggle]');
        if (t) {
            const collapsed = body.classList.toggle('adm-collapsed');
            store.set(KEY, collapsed ? '1' : '0');
            return;
        }
        if (e.target.closest('[data-adm-drawer-open]')) { body.classList.add('adm-drawer-open'); return; }
        if (e.target.closest('[data-adm-drawer-close]') || e.target.closest('[data-adm-overlay]')) { closeDrawer(); return; }
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeDrawer(); closeMenus(); closeSearch(); } });

    /* ---------- tiny dropdown menus: [data-dropdown] > [data-dropdown-trigger] + [data-dropdown-panel] ---------- */
    function closeMenus(except) {
        $$('[data-dropdown]').forEach((m) => {
            if (m === except) return;
            $('[data-dropdown-panel]', m)?.classList.add('hidden');
            $('[data-dropdown-trigger]', m)?.setAttribute('aria-expanded', 'false');
        });
    }
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-dropdown-trigger]');
        if (trigger) {
            const menu = trigger.closest('[data-dropdown]');
            const panel = $('[data-dropdown-panel]', menu);
            const open = panel.classList.contains('hidden');
            closeMenus(menu);
            panel.classList.toggle('hidden', !open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }
        if (!e.target.closest('[data-dropdown-panel]')) closeMenus();
        else if (e.target.closest('a,button') && !e.target.closest('[data-dropdown-keep]')) setTimeout(() => closeMenus(), 0);
    });


    /* ---------- modals: [data-modal="name"], open with [data-modal-open="name"], close with [data-modal-close] ---------- */
    const modalEl = (name) => document.querySelector('[data-modal="' + name + '"]');
    const modal = {
        open(name) {
            const m = modalEl(name);
            if (!m) return;
            m.classList.remove('hidden'); m.classList.add('flex');
            body.classList.add('overflow-hidden');
            m.querySelector('input:not([type=hidden]),textarea,select')?.focus();
        },
        close(name) {
            const list = name ? [modalEl(name)] : $$('[data-modal]');
            list.forEach((m) => { if (m) { m.classList.add('hidden'); m.classList.remove('flex'); } });
            if (!$$('[data-modal]:not(.hidden)').length) body.classList.remove('overflow-hidden');
        },
    };
    window.adminModal = modal;
    window.addEventListener('open-modal', (e) => modal.open(e.detail));
    window.addEventListener('close-modal', (e) => modal.close(e.detail));
    document.addEventListener('click', (e) => {
        const o = e.target.closest('[data-modal-open]');
        if (o) { e.preventDefault(); modal.open(o.getAttribute('data-modal-open')); return; }
        const c = e.target.closest('[data-modal-close]');
        if (c) { const m = c.closest('[data-modal]'); modal.close(m ? m.getAttribute('data-modal') : c.getAttribute('data-modal-close') || undefined); }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { const open = $$('[data-modal]:not(.hidden)').pop(); if (open) modal.close(open.getAttribute('data-modal')); }
    });

    /* ---------- tabs: [data-tabs] > [data-tab="key"] buttons + [data-tab-panel="key"] panels ---------- */
    const TAB_ON = ['bg-aron-600', 'text-white', 'shadow-adm-brand'];
    const TAB_OFF = ['text-slate-500', 'hover:bg-aron-50', 'hover:text-aron-700'];
    function activateTab(root, key, pushHash) {
        $$('[data-tab]', root).forEach((b) => {
            const on = b.getAttribute('data-tab') === key;
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            TAB_ON.forEach((c) => b.classList.toggle(c, on));
            TAB_OFF.forEach((c) => b.classList.toggle(c, !on));
        });
        $$('[data-tab-panel]', root).forEach((p) => { p.style.display = p.getAttribute('data-tab-panel') === key ? '' : 'none'; });
        if (pushHash) { try { history.replaceState(null, '', '#' + key); } catch (e) { /* ignore */ } }
    }
    $$('[data-tabs]').forEach((root) => {
        const keys = $$('[data-tab]', root).map((b) => b.getAttribute('data-tab'));
        const hash = location.hash.replace('#', '');
        const errPanel = $$('[data-tab-panel]', root).find((p) => p.querySelector('.has-error'));
        const start = keys.includes(hash) ? hash : errPanel ? errPanel.getAttribute('data-tab-panel') : root.getAttribute('data-default') || keys[0];
        activateTab(root, start, false);
        root.setAttribute('data-ready', '');
    });
    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-tab]');
        if (b) activateTab(b.closest('[data-tabs]'), b.getAttribute('data-tab'), true);
    });

    /* ---------- quick search / jump (filters sidebar links) ---------- */
    const input = $('[data-adm-search]');
    const results = $('[data-adm-search-results]');
    let items = [];
    let activeIdx = -1;

    function closeSearch() { results?.classList.add('hidden'); activeIdx = -1; }
    function buildIndex() {
        items = $$('[data-adm-nav-link]').map((a) => ({
            label: a.getAttribute('data-label') || a.textContent.trim(),
            group: a.getAttribute('data-group') || '',
            icon: a.getAttribute('data-icon') || 'arrow-left',
            href: a.getAttribute('href'),
        }));
    }
    function norm(s) {
        return (s || '').toString().toLowerCase().replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/[‌\s]+/g, ' ').trim();
    }
    function renderResults(q) {
        const nq = norm(q);
        const list = nq ? items.filter((i) => norm(i.label + ' ' + i.group).includes(nq)) : items.slice(0, 8);
        results.innerHTML = '';
        if (!list.length) {
            const d = document.createElement('div');
            d.className = 'px-4 py-5 text-center text-sm text-slate-400';
            d.textContent = 'نتیجه‌ای پیدا نشد';
            results.appendChild(d);
        }
        list.slice(0, 10).forEach((i) => {
            const a = document.createElement('a');
            a.href = i.href;
            a.setAttribute('data-adm-search-item', '');
            a.className = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-aron-50 hover:text-aron-700 aria-selected:bg-aron-50';
            const ic = document.createElement('i');
            ic.className = 'ph ph-' + i.icon + ' text-lg text-aron-500';
            const sp = document.createElement('span');
            sp.className = 'flex-1';
            sp.textContent = i.label;
            const g = document.createElement('small');
            g.className = 'text-xs font-medium text-slate-400';
            g.textContent = i.group;
            a.append(ic, sp, g);
            results.appendChild(a);
        });
        results.classList.remove('hidden');
        activeIdx = -1;
    }
    if (input && results) {
        input.addEventListener('focus', () => { if (!items.length) buildIndex(); renderResults(input.value); });
        input.addEventListener('input', () => renderResults(input.value));
        input.addEventListener('keydown', (e) => {
            const els = $$('[data-adm-search-item]', results);
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!els.length) return;
                activeIdx = (activeIdx + (e.key === 'ArrowDown' ? 1 : -1) + els.length) % els.length;
                els.forEach((el, i) => el.setAttribute('aria-selected', i === activeIdx ? 'true' : 'false'));
                els[activeIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                const target = els[activeIdx] || els[0];
                if (target) { e.preventDefault(); window.location.href = target.href; }
            }
        });
        document.addEventListener('click', (e) => { if (!e.target.closest('[data-adm-search-wrap]')) closeSearch(); });
        document.addEventListener('keydown', (e) => {
            const typing = /input|textarea|select/i.test(document.activeElement?.tagName || '') || document.activeElement?.isContentEditable;
            if ((e.key === 'k' && (e.ctrlKey || e.metaKey)) || (e.key === '/' && !typing)) { e.preventDefault(); input.focus(); input.select(); }
        });
    }

    /* ---------- toasts ---------- */
    const dismiss = (el) => {
        if (!el || el.classList.contains('is-leaving')) return;
        el.classList.add('is-leaving');
        setTimeout(() => el.remove(), 260);
    };
    const armToast = (el) => {
        const ms = parseInt(el.getAttribute('data-timeout') || '6500', 10);
        if (ms > 0) setTimeout(() => dismiss(el), ms);
    };
    $$('[data-adm-toast]').forEach(armToast);
    document.addEventListener('click', (e) => {
        const c = e.target.closest('[data-adm-toast-close]');
        if (c) dismiss(c.closest('[data-adm-toast]'));
    });

    const TOAST = {
        success: ['check-circle', 'border-mint-200 bg-mint-50 text-mint-700'],
        error: ['warning-circle', 'border-coral-200 bg-coral-50 text-coral-700'],
        warning: ['warning', 'border-amber-200 bg-amber-50 text-amber-800'],
        info: ['info', 'border-aron-200 bg-aron-50 text-aron-700'],
    };
    /** window.adminToast('ذخیره شد', 'success' | 'error' | 'warning' | 'info') */
    window.adminToast = function (message, type = 'success') {
        let box = $('[data-adm-toasts]');
        if (!box) {
            box = document.createElement('div');
            box.className = 'adm-toasts';
            box.setAttribute('data-adm-toasts', '');
            body.appendChild(box);
        }
        const [icon, cls] = TOAST[type] || TOAST.info;
        const el = document.createElement('div');
        el.setAttribute('data-adm-toast', '');
        el.setAttribute('role', 'status');
        el.className = 'adm-toast flex items-start gap-3 rounded-2xl border p-4 text-sm font-semibold shadow-adm-pop ' + cls;
        const ic = document.createElement('i');
        ic.className = 'ph-fill ph-' + icon + ' mt-0.5 text-xl';
        const tx = document.createElement('span');
        tx.className = 'flex-1 leading-6';
        tx.textContent = message;
        const bt = document.createElement('button');
        bt.type = 'button';
        bt.setAttribute('data-adm-toast-close', '');
        bt.setAttribute('aria-label', 'بستن');
        bt.className = 'opacity-60 hover:opacity-100';
        bt.innerHTML = '<i class="ph ph-x"></i>';
        el.append(ic, tx, bt);
        box.appendChild(el);
        armToast(el);
    };

    /* ---------- confirm on [data-confirm] (forms or links) ---------- */
    document.addEventListener('submit', (e) => {
        const msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) e.preventDefault();
    });

    /* ---------- double-submit guard: buttons with [data-loading-on-submit] ---------- */
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        $$('[data-loading-on-submit]', e.target).forEach((b) => { b.disabled = true; b.classList.add('opacity-60'); });
    });
})();
