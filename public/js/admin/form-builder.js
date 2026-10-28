/*
 * فرم‌ساز پنل (Vanilla JS، بدون build). ساختار فیلدها: App\Forms\FormSchema.
 * حالت (fields) در حافظه است؛ بوم (canvas) و پنل تنظیمات از روی آن رسم می‌شوند و
 * موقع ذخیره، کل فهرست به‌صورت JSON در fields_json فرستاده می‌شود.
 */
(function () {
    'use strict';

    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fa = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
    const uid = () => 'f' + Math.random().toString(36).slice(2, 8);
    const toast = (m, t) => window.adminToast && window.adminToast(m, t || 'success');

    const CHOICE = ['select', 'radio', 'checkbox'];
    const NO_INPUT = ['section', 'html', 'page'];
    const KEY_BASE = {
        text: 'text', textarea: 'message', email: 'email', mobile: 'mobile', tel: 'phone', number: 'number', national_code: 'national_code',
        select: 'choice', radio: 'option', checkbox: 'options', consent: 'consent', date: 'date', file: 'file', url: 'website', hidden: 'hidden',
        section: 'section', html: 'html', page: 'page',
    };
    const MINMAX = {
        text: ['حداقل کاراکتر', 'حداکثر کاراکتر'], textarea: ['حداقل کاراکتر', 'حداکثر کاراکتر'], number: ['حداقل مقدار', 'حداکثر مقدار'],
        checkbox: ['حداقل گزینه‌ی انتخابی', 'حداکثر گزینه‌ی انتخابی'], date: ['از سال', 'تا سال'],
    };

    function init(form) {
        const types = JSON.parse(form.dataset.types || '{}');
        const operators = JSON.parse(form.dataset.operators || '{}');
        let fields = JSON.parse(form.dataset.fields || '[]').map((f) => ({ ...f, logic: f.logic || { enabled: false, action: 'show', match: 'all', rules: [] } }));
        let selected = fields[0] ? fields[0].id : null;

        const canvas = $('[data-fb-canvas]', form);
        const panel = $('[data-fb-settings]', form);
        const empty = $('[data-fb-empty]', form);
        const count = $('[data-fb-count]', form);

        /* ------------------------------------------------ helpers */
        const typeLabel = (t) => (types[t] || [t])[0];
        const typeIcon = (t) => (types[t] || [])[1] || 'text-t';
        const isInput = (f) => f && !NO_INPUT.includes(f.type);
        const isChoice = (f) => f && CHOICE.includes(f.type);
        const current = () => fields.find((f) => f.id === selected) || null;
        const inputFields = () => fields.filter(isInput);
        function has(f, prop) {
            const t = f.type;
            switch (prop) {
                case 'placeholder': return ['text', 'textarea', 'email', 'mobile', 'tel', 'number', 'national_code', 'url', 'select'].includes(t);
                case 'required': return isInput(f) && t !== 'hidden';
                case 'default': return isInput(f) && !['file', 'consent', 'date'].includes(t);
                case 'width': return !['hidden', 'page'].includes(t);
                case 'help': return isInput(f) && t !== 'hidden';
                case 'minmax': return !!MINMAX[t];
                case 'content': return ['html', 'section', 'consent'].includes(t);
                case 'logic': return t !== 'page';
                case 'key': return isInput(f);
            }
            return false;
        }
        function newKey(type) {
            const base = KEY_BASE[type] || type;
            let k = base, i = 2;
            while (fields.some((f) => f.key === k)) k = base + '_' + i++;
            return k;
        }
        function logicSummary(f) {
            if (!f.logic || !f.logic.enabled || !f.logic.rules.length) return '';
            return (f.logic.action === 'show' ? 'نمایش' : 'پنهان') + ' با ' + fa(f.logic.rules.length) + ' شرط';
        }

        /* ------------------------------------------------ dirty state */
        let dirty = false, saving = false;
        function markDirty() {
            if (dirty) return;
            dirty = true;
            $('[data-fb-dirty-dot]', form).className = 'size-2 rounded-full bg-amber-500';
            $('[data-fb-dirty-text]', form).textContent = 'تغییرات ذخیره‌نشده';
        }
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        addEventListener('beforeunload', (e) => { if (dirty && !saving) { e.preventDefault(); e.returnValue = ''; } });

        /* ------------------------------------------------ canvas */
        function cardHtml(f) {
            const sel = f.id === selected ? ' is-selected' : '';
            if (f.type === 'page') {
                return `<div class="fb-card is-page${sel}" data-id="${esc(f.id)}" tabindex="0" role="button" aria-label="شکست صفحه">
                    <span class="fb-handle" title="جابه‌جایی"><i class="ph ph-dots-six-vertical text-lg"></i></span>
                    <span class="h-px flex-1" style="border-top:2px dashed #a5b4fc"></span><i class="ph ph-stack"></i> شکست صفحه — ${esc(f.label)}
                    <span class="h-px flex-1" style="border-top:2px dashed #a5b4fc"></span>${actions()}</div>`;
            }
            const w = f.width === 'half' ? ' w-half' : f.width === 'third' ? ' w-third' : '';
            const logic = logicSummary(f);
            return `<div class="fb-card${w}${sel}" data-id="${esc(f.id)}" tabindex="0" role="button" aria-label="${esc(f.label)}">
                <span class="fb-handle" title="جابه‌جایی"><i class="ph ph-dots-six-vertical text-lg"></i></span>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-lg text-slate-600" style="outline:1px solid #e7eaf4"><i class="ph ph-${esc(typeIcon(f.type))}"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold text-slate-800">${esc(f.label)}${f.required ? '<span class="text-coral-600"> *</span>' : ''}</span>
                    <span class="flex flex-wrap items-center gap-x-2 text-[11px] text-slate-500">${esc(typeLabel(f.type))}
                        ${isInput(f) ? `<code dir="ltr" class="text-slate-400">${esc(f.key)}</code>` : ''}
                        ${logic ? `<span class="fb-logic-badge">${esc(logic)}</span>` : ''}</span>
                </span>${actions()}</div>`;
        }
        function actions() {
            return `<span class="fb-actions">
                <button type="button" data-act="up" title="بالا" aria-label="بالا"><i class="ph ph-caret-up"></i></button>
                <button type="button" data-act="down" title="پایین" aria-label="پایین"><i class="ph ph-caret-down"></i></button>
                <button type="button" data-act="dup" title="کپی" aria-label="کپی"><i class="ph ph-copy-simple"></i></button>
                <button type="button" data-act="del" class="is-danger" title="حذف" aria-label="حذف"><i class="ph ph-trash"></i></button></span>`;
        }
        function renderCanvas() {
            canvas.innerHTML = fields.map(cardHtml).join('');
            empty.hidden = fields.length > 0;
            const steps = fields.filter((f) => f.type === 'page').length + 1;
            count.textContent = fa(fields.length) + ' فیلد' + (steps > 1 ? ' · ' + fa(steps) + ' مرحله' : '');
            renderEmailSelects();
        }
        function renderEmailSelects() {
            $$('[data-fb-email-select]', form).forEach((sel) => {
                const cur = sel.value || sel.dataset.selected || '';
                const emails = fields.filter((f) => f.type === 'email');
                sel.innerHTML = '<option value="">—</option>' + emails.map((f) => `<option value="${esc(f.key)}"${f.key === cur ? ' selected' : ''}>${esc(f.label)}</option>`).join('');
            });
        }

        /* ------------------------------------------------ settings panel */
        function input(label, prop, value, attrs = '') {
            return `<div><label class="a-label">${label}</label><input class="a-input" data-prop="${prop}" value="${esc(value)}" ${attrs}></div>`;
        }
        function renderPanel() {
            const f = current();
            if (!f) {
                panel.innerHTML = '<p class="py-10 text-center text-sm text-slate-400">یک فیلد را انتخاب کنید یا از ستون کناری اضافه کنید.</p>';
                return;
            }
            const h = [];
            h.push(`<div class="mb-4 flex items-center gap-2.5 border-b border-line pb-3">
                <span class="grid size-9 place-items-center rounded-xl bg-aron-600 text-lg text-white"><i class="ph ph-${esc(typeIcon(f.type))}"></i></span>
                <div><p class="text-sm font-extrabold text-ink">تنظیمات فیلد</p><p class="text-xs text-slate-500">${esc(typeLabel(f.type))}</p></div></div>`);
            h.push('<div class="space-y-4">');
            h.push(input(f.type === 'page' ? 'عنوان مرحله‌ی بعد' : 'برچسب', 'label', f.label));
            if (has(f, 'key')) {
                h.push(`<div><label class="a-label">نام فنی (کلید)</label><input class="a-input font-mono text-xs" dir="ltr" data-prop="key" value="${esc(f.key)}">
                    <p class="mt-1 text-xs leading-5 text-slate-400">در خروجی Excel، شرط‌ها و ایمیل‌ها (مثل <code dir="ltr">{field:${esc(f.key)}}</code>) استفاده می‌شود.</p></div>`);
            }
            if (has(f, 'placeholder')) h.push(input('متن راهنمای داخل فیلد', 'placeholder', f.placeholder || ''));
            if (has(f, 'help')) h.push(input('توضیح زیر فیلد', 'help', f.help || ''));
            if (has(f, 'content')) {
                const l = f.type === 'consent' ? 'متن تأیید (HTML مجاز)' : f.type === 'section' ? 'توضیح زیر عنوان' : 'محتوا (HTML مجاز)';
                h.push(`<div><label class="a-label">${l}</label><textarea class="a-input font-mono text-xs" rows="4" dir="auto" data-prop="content">${esc(f.content || '')}</textarea></div>`);
            }
            if (has(f, 'required') || has(f, 'width')) {
                h.push('<div class="grid grid-cols-2 gap-3">');
                if (has(f, 'required')) h.push(`<label class="flex items-center gap-2 rounded-xl border border-line px-3 py-2.5 text-sm font-semibold"><input type="checkbox" class="a-check" data-prop="required" ${f.required ? 'checked' : ''}> الزامی</label>`);
                if (has(f, 'width')) {
                    h.push(`<select class="a-select" data-prop="width" aria-label="عرض" ${has(f, 'required') ? '' : 'style="grid-column:span 2"'}>
                        ${[['full', 'تمام‌عرض'], ['half', 'نصف عرض'], ['third', 'یک‌سوم عرض']].map(([v, l]) => `<option value="${v}"${(f.width || 'full') === v ? ' selected' : ''}>${l}</option>`).join('')}</select>`);
                }
                h.push('</div>');
            }
            if (isChoice(f)) {
                h.push(`<div class="fb-box"><div class="mb-2 flex items-center justify-between"><span class="text-sm font-bold text-slate-700">گزینه‌ها</span>
                    <button type="button" class="text-xs font-bold text-aron-700" data-act="bulk">ورود گروهی</button></div><div class="space-y-1.5">`);
                (f.options || []).forEach((o, i) => {
                    h.push(`<div class="fb-row"><input class="a-input fb-small" placeholder="برچسب" data-opt="${i}" data-opt-prop="label" value="${esc(o.label)}">
                        <input class="a-input fb-small" style="width:7rem" placeholder="مقدار" title="مقدار ذخیره‌شده" dir="auto" data-opt="${i}" data-opt-prop="value" value="${esc(o.value)}">
                        <button type="button" class="fb-icon-btn" data-act="opt-del" data-opt="${i}" aria-label="حذف گزینه"><i class="ph ph-x"></i></button></div>`);
                });
                h.push(`</div><button type="button" class="fb-dashed mt-2" data-act="opt-add"><i class="ph ph-plus"></i> افزودن گزینه</button></div>`);
            }
            if (has(f, 'default')) h.push(input('مقدار پیش‌فرض', 'default', f.default || '') + (f.type === 'checkbox' ? '<p class="-mt-3 text-xs text-slate-400">چند مقدار را با کاما جدا کنید.</p>' : ''));
            if (has(f, 'minmax')) {
                const [a, b] = MINMAX[f.type];
                h.push(`<div class="grid grid-cols-2 gap-3">${input(a, 'min', f.min ?? '', 'type="number" dir="ltr"')}${input(b, 'max', f.max ?? '', 'type="number" dir="ltr"')}</div>`);
            }
            if (f.type === 'textarea') h.push(input('تعداد سطر', 'rows', f.rows || 4, 'type="number" min="2" max="20" dir="ltr"'));
            if (f.type === 'file') {
                h.push(`<div class="grid grid-cols-2 gap-3">${input('پسوندهای مجاز', 'accept', f.accept || 'jpg,jpeg,png,pdf', 'dir="ltr" placeholder="jpg,png,pdf"')}${input('حداکثر حجم (KB)', 'max_kb', f.max_kb || 5120, 'type="number" dir="ltr"')}</div>`);
            }
            if (has(f, 'logic')) h.push(logicHtml(f));
            if (f.type !== 'page') h.push(input('کلاس CSS اضافه (اختیاری)', 'css_class', f.css_class || '', 'dir="ltr"'));
            h.push('</div>');
            panel.innerHTML = h.join('');
        }
        function logicHtml(f) {
            const lg = f.logic;
            const out = [`<div class="fb-logic"><label class="flex items-center justify-between gap-2 text-sm font-bold text-violet-900">
                <span class="flex items-center gap-1.5"><i class="ph ph-funnel"></i> منطق شرطی</span>
                <input type="checkbox" class="a-check" data-logic="enabled" ${lg.enabled ? 'checked' : ''}></label>`];
            if (lg.enabled) {
                out.push(`<div class="mt-3 space-y-2 text-sm"><div class="flex flex-wrap items-center gap-1.5">
                    <select class="a-select fb-small" style="width:auto" data-logic="action"><option value="show"${lg.action === 'show' ? ' selected' : ''}>نمایش بده</option><option value="hide"${lg.action === 'hide' ? ' selected' : ''}>پنهان کن</option></select>
                    <span>اگر</span>
                    <select class="a-select fb-small" style="width:auto" data-logic="match"><option value="all"${lg.match === 'all' ? ' selected' : ''}>همه‌ی</option><option value="any"${lg.match === 'any' ? ' selected' : ''}>یکی از</option></select>
                    <span>شرط‌های زیر برقرار باشد:</span></div>`);
                const targets = inputFields().filter((o) => o.key !== f.key);
                lg.rules.forEach((r, i) => {
                    const t = fields.find((o) => o.key === r.field);
                    const noValue = ['empty', 'notempty'].includes(r.op);
                    const valueCtl = noValue ? '' : (t && t.options && t.options.length
                        ? `<select class="a-select fb-small" data-rule="${i}" data-rule-prop="value"><option value="">—</option>${t.options.map((o) => `<option value="${esc(o.value)}"${o.value === r.value ? ' selected' : ''}>${esc(o.label)}</option>`).join('')}</select>`
                        : `<input class="a-input fb-small" placeholder="مقدار" data-rule="${i}" data-rule-prop="value" value="${esc(r.value)}">`);
                    out.push(`<div class="fb-rule space-y-1.5"><div class="fb-row">
                        <select class="a-select fb-small" data-rule="${i}" data-rule-prop="field">${targets.map((o) => `<option value="${esc(o.key)}"${o.key === r.field ? ' selected' : ''}>${esc(o.label)}</option>`).join('')}</select>
                        <button type="button" class="fb-icon-btn" data-act="rule-del" data-rule="${i}" aria-label="حذف شرط"><i class="ph ph-x"></i></button></div>
                        <div class="fb-row"><select class="a-select fb-small" style="width:9rem;flex-shrink:0" data-rule="${i}" data-rule-prop="op">${Object.entries(operators).map(([k, l]) => `<option value="${k}"${k === r.op ? ' selected' : ''}>${esc(l)}</option>`).join('')}</select>${valueCtl}</div></div>`);
                });
                out.push('<button type="button" class="fb-dashed" style="border-color:#c4b5fd;color:#6d28d9" data-act="rule-add"><i class="ph ph-plus"></i> افزودن شرط</button></div>');
            }
            out.push('</div>');
            return out.join('');
        }

        /* ------------------------------------------------ actions */
        function add(type) {
            const f = { id: uid(), type, key: newKey(type), label: typeLabel(type), placeholder: '', help: '', required: false, default: '',
                width: 'full', min: null, max: null, css_class: '', logic: { enabled: false, action: 'show', match: 'all', rules: [] } };
            if (CHOICE.includes(type)) f.options = [{ label: 'گزینه ۱', value: 'گزینه ۱' }, { label: 'گزینه ۲', value: 'گزینه ۲' }];
            if (type === 'textarea') f.rows = 4;
            if (type === 'file') { f.accept = 'jpg,jpeg,png,pdf'; f.max_kb = 5120; }
            if (type === 'consent') { f.content = 'قوانین و مقررات را مطالعه کرده‌ام و می‌پذیرم.'; f.required = true; }
            if (type === 'html') f.content = '<p>متن دلخواه</p>';
            if (type === 'section') f.content = '';
            if (type === 'page') f.label = 'مرحله‌ی بعد';
            const cur = current();
            const at = cur ? fields.indexOf(cur) + 1 : fields.length;
            fields.splice(at, 0, f);
            selected = f.id;
            renderCanvas(); renderPanel(); markDirty();
            const card = $(`[data-id="${f.id}"]`, canvas);
            card && card.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            const first = $('[data-prop="label"]', panel);
            first && first.focus();
        }
        function remove(f) {
            if (!window.confirm('فیلد «' + f.label + '» حذف شود؟')) return;
            const i = fields.indexOf(f);
            fields.splice(i, 1);
            fields.forEach((o) => { if (o.logic) o.logic.rules = o.logic.rules.filter((r) => r.field !== f.key); });
            selected = (fields[i] || fields[i - 1] || {}).id || null;
            renderCanvas(); renderPanel(); markDirty();
        }
        function move(f, dir) {
            const i = fields.indexOf(f), j = i + dir;
            if (j < 0 || j >= fields.length) return;
            fields.splice(j, 0, fields.splice(i, 1)[0]);
            renderCanvas(); markDirty();
        }
        function duplicate(f) {
            const copy = JSON.parse(JSON.stringify(f));
            copy.id = uid(); copy.key = newKey(f.type); copy.label = f.label + ' (کپی)';
            fields.splice(fields.indexOf(f) + 1, 0, copy);
            selected = copy.id;
            renderCanvas(); renderPanel(); markDirty();
        }

        // palette
        $$('[data-fb-add]', form).forEach((b) => b.addEventListener('click', () => add(b.dataset.fbAdd)));

        // canvas: select / card actions
        canvas.addEventListener('click', (e) => {
            const card = e.target.closest('[data-id]');
            if (!card) return;
            const f = fields.find((x) => x.id === card.dataset.id);
            const act = e.target.closest('[data-act]');
            if (act) {
                e.stopPropagation();
                ({ up: () => move(f, -1), down: () => move(f, 1), dup: () => duplicate(f), del: () => remove(f) })[act.dataset.act]();
                return;
            }
            if (selected !== f.id) { selected = f.id; renderCanvas(); renderPanel(); }
        });
        canvas.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('[data-id]')) { e.preventDefault(); e.target.click(); }
        });

        // settings panel: typing updates the field (canvas re-renders; the panel only when its shape changes)
        panel.addEventListener('input', (e) => {
            const f = current(); if (!f) return;
            const t = e.target;
            if (t.dataset.prop) {
                const p = t.dataset.prop;
                if (t.type === 'checkbox') f[p] = t.checked;
                else if (p === 'key') { f.key = t.value.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/^[0-9_]+/, ''); }
                else if (['min', 'max', 'rows', 'max_kb'].includes(p)) f[p] = t.value === '' ? null : Number(t.value);
                else f[p] = t.value;
                renderCanvas();
                return;
            }
            if (t.dataset.opt !== undefined && t.dataset.optProp) {
                const o = f.options[+t.dataset.opt];
                if (t.dataset.optProp === 'label') { if (o.value === '' || o.value === o.label) { o.value = t.value; const v = $(`[data-opt="${t.dataset.opt}"][data-opt-prop="value"]`, panel); if (v) v.value = t.value; } o.label = t.value; }
                else o.value = t.value;
                return;
            }
            if (t.dataset.rule !== undefined && t.dataset.ruleProp === 'value' && t.tagName === 'INPUT') {
                f.logic.rules[+t.dataset.rule].value = t.value;
            }
        });
        panel.addEventListener('change', (e) => {
            const f = current(); if (!f) return;
            const t = e.target;
            if (t.dataset.prop === 'key') { t.value = f.key; renderCanvas(); }
            if (t.dataset.logic) {
                f.logic[t.dataset.logic] = t.type === 'checkbox' ? t.checked : t.value;
                if (t.dataset.logic === 'enabled' && t.checked && !f.logic.rules.length) addRule(f, true);
                renderPanel(); renderCanvas();
                return;
            }
            if (t.dataset.rule !== undefined && t.dataset.ruleProp) {
                const r = f.logic.rules[+t.dataset.rule];
                r[t.dataset.ruleProp] = t.value;
                if (t.dataset.ruleProp === 'field') r.value = '';
                if (t.dataset.ruleProp !== 'value') renderPanel();
            }
        });
        function addRule(f, quiet) {
            const target = inputFields().find((o) => o.key !== f.key);
            if (!target) { if (!quiet) toast('ابتدا یک فیلد دیگر اضافه کنید.', 'error'); f.logic.enabled = false; return; }
            f.logic.enabled = true;
            f.logic.rules.push({ field: target.key, op: 'is', value: '' });
        }
        panel.addEventListener('click', (e) => {
            const f = current(); if (!f) return;
            const b = e.target.closest('[data-act]'); if (!b) return;
            const a = b.dataset.act;
            if (a === 'opt-add') { const n = f.options.length + 1; f.options.push({ label: 'گزینه ' + fa(n), value: 'گزینه ' + fa(n) }); }
            if (a === 'opt-del') f.options.splice(+b.dataset.opt, 1);
            if (a === 'bulk') {
                const text = window.prompt('هر گزینه را در یک خط بنویسید:', f.options.map((o) => o.label).join('\n'));
                if (text === null) return;
                f.options = text.split('\n').map((l) => l.trim()).filter(Boolean).map((l) => ({ label: l, value: l }));
            }
            if (a === 'rule-add') addRule(f);
            if (a === 'rule-del') { f.logic.rules.splice(+b.dataset.rule, 1); if (!f.logic.rules.length) f.logic.enabled = false; }
            markDirty(); renderPanel(); renderCanvas();
        });

        // drag & drop
        if (window.Sortable) {
            window.Sortable.create(canvas, {
                handle: '.fb-handle', draggable: '[data-id]', animation: 160, ghostClass: 'opacity-40',
                onEnd: () => {
                    const order = $$('[data-id]', canvas).map((el) => el.dataset.id);
                    fields = order.map((id) => fields.find((f) => f.id === id)).filter(Boolean);
                    renderCanvas(); markDirty();
                },
            });
        }

        // remember the open tab across the save redirect
        form.addEventListener('click', (e) => { const t = e.target.closest('[data-tab]'); if (t) $('[data-fb-tab]', form).value = t.dataset.tab; });
        form.addEventListener('submit', () => {
            saving = true;
            $('[data-fb-json]', form).value = JSON.stringify(fields);
            const active = $('[data-tab][aria-selected="true"]', form);
            if (active) $('[data-fb-tab]', form).value = active.dataset.tab;
        });

        renderCanvas();
        renderPanel();
    }

    function boot() { $$('[data-fb-form]').forEach(init); }
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
