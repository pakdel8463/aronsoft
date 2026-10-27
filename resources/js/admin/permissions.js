/* Permission matrix: search, per-group select-all, counters. Acts on [data-perm-matrix] only. */
(function () {
    const roots = document.querySelectorAll('[data-perm-matrix]');
    if (!roots.length) return;
    roots.forEach((root) => {
        const boxes = () => Array.from(root.querySelectorAll('input[data-pm-box]'));
        const groups = Array.from(root.querySelectorAll('[data-pm-group]'));
        const counter = root.querySelector('[data-pm-count]');
        const search = root.querySelector('[data-pm-search]');
        const empty = root.querySelector('[data-pm-empty]');
        const visible = (el) => !el.closest('[data-pm-item]').classList.contains('hidden');

        function refresh() {
            const all = boxes();
            if (counter) counter.textContent = all.filter((b) => b.checked).length + ' / ' + all.length;
            groups.forEach((g) => {
                const bs = Array.from(g.querySelectorAll('input[data-pm-box]'));
                const on = bs.filter((b) => b.checked).length;
                const t = g.querySelector('[data-pm-group-toggle]');
                if (t) { t.checked = on === bs.length && bs.length > 0; t.indeterminate = on > 0 && on < bs.length; }
                const c = g.querySelector('[data-pm-group-count]');
                if (c) c.textContent = on + '/' + bs.length;
            });
        }
        function filter() {
            const q = (search?.value || '').trim().toLowerCase();
            let shown = 0;
            groups.forEach((g) => {
                let any = 0;
                g.querySelectorAll('[data-pm-item]').forEach((it) => {
                    const hit = !q || (it.dataset.pmLabel || '').toLowerCase().includes(q) || (g.dataset.pmGroup || '').toLowerCase().includes(q);
                    it.classList.toggle('hidden', !hit);
                    if (hit) any++;
                });
                g.classList.toggle('hidden', any === 0);
                shown += any;
            });
            empty?.classList.toggle('hidden', shown > 0);
        }
        root.addEventListener('change', (e) => {
            const t = e.target;
            if (t.matches('[data-pm-group-toggle]')) {
                t.closest('[data-pm-group]').querySelectorAll('input[data-pm-box]').forEach((b) => { if (visible(b)) b.checked = t.checked; });
            }
            refresh();
        });
        root.addEventListener('click', (e) => {
            const a = e.target.closest('[data-pm-all],[data-pm-none]');
            if (!a) return;
            boxes().forEach((b) => { if (visible(b)) b.checked = a.hasAttribute('data-pm-all'); });
            refresh();
        });
        search?.addEventListener('input', filter);
        search?.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
        refresh();
    });
})();
