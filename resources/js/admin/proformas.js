/* Proforma builder rows + live total + catalog quick-add. Acts on [data-proforma-builder] only. */
(function () {
    const root = document.querySelector('[data-proforma-builder]');
    if (!root) return;
    const body = root.querySelector('[data-rows]');
    const tpl = root.querySelector('template[data-row-tpl]');
    const totalEl = root.querySelector('[data-total]');
    const fmt = (n) => new Intl.NumberFormat('fa-IR').format(n);
    let idx = 1 + Math.max(-1, ...Array.from(body.querySelectorAll('tr[data-row]')).map((r) => +r.dataset.row));

    function total() {
        let t = 0;
        body.querySelectorAll('[data-price]').forEach((i) => { const v = parseFloat(i.value); if (!isNaN(v) && v > 0) t += v; });
        totalEl.textContent = fmt(Math.round(t));
        const c = root.querySelector('[data-row-count]'); if (c) c.textContent = body.querySelectorAll('tr[data-row]').length;
    }
    function addRow(title = '', price = '', desc = '') {
        const html = tpl.innerHTML.replaceAll('__I__', idx++);
        const t = document.createElement('tbody'); t.innerHTML = html.trim();
        const tr = t.firstElementChild;
        tr.querySelector('[data-title]').value = title;
        tr.querySelector('[data-desc]').value = desc;
        tr.querySelector('[data-price]').value = price;
        body.appendChild(tr); total();
        if (!title) tr.querySelector('[data-title]').focus();
    }
    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-add-row]')) { addRow(); return; }
        const del = e.target.closest('[data-del-row]');
        if (del) {
            if (body.querySelectorAll('tr[data-row]').length <= 1) { const r = del.closest('tr'); r.querySelectorAll('input').forEach((i) => (i.value = '')); total(); return; }
            del.closest('tr').remove(); total();
        }
        const up = e.target.closest('[data-move]');
        if (up) {
            const r = up.closest('tr'); const dir = up.dataset.move;
            if (dir === 'up' && r.previousElementSibling) body.insertBefore(r, r.previousElementSibling);
            if (dir === 'down' && r.nextElementSibling) body.insertBefore(r.nextElementSibling, r);
        }
    });
    root.addEventListener('input', (e) => { if (e.target.matches('[data-price]')) total(); });
    const cat = root.querySelector('[data-catalog]');
    if (cat) cat.addEventListener('change', () => {
        if (!cat.value) return;
        const o = cat.selectedOptions[0];
        // اگر تنها ردیف موجود خالی است، همان را پر کن
        const first = body.querySelector('tr[data-row]');
        if (body.querySelectorAll('tr[data-row]').length === 1 && !first.querySelector('[data-title]').value && !first.querySelector('[data-price]').value) {
            first.querySelector('[data-title]').value = o.dataset.title; first.querySelector('[data-price]').value = o.dataset.price; total();
        } else addRow(o.dataset.title, o.dataset.price);
        cat.value = '';
    });
    const copy = document.querySelector('[data-copy]');
    if (copy) copy.addEventListener('click', async () => { try { await navigator.clipboard.writeText(copy.dataset.copy); window.adminToast?.('لینک کپی شد.', 'success'); } catch (e) { window.prompt('لینک:', copy.dataset.copy); } });
    total();
})();
