/*
 | Aron Soft — اسکریپت عمومی فرانت (بدون وابستگی)
 | شامل: منوی موبایل، انیمیشن ظهور ملایم، فرم‌های ajax (لید / خبرنامه / تماس)،
 | محاسبه‌گر قیمت و چند ابزار کوچک. همه‌چیز در برابر نبودن المان‌ها ایمن است.
 */
(function () {
    'use strict';

    var d = document;
    var $ = function (s, r) { return (r || d).querySelector(s); };
    var $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };
    var csrf = function () { var m = $('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; };
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------------------------------------------------------- منوی موبایل */
    var lastFocus = null;
    function setMenu(open) {
        var menu = $('#mobile-menu'), overlay = $('#menu-overlay');
        if (!menu || !overlay) return;
        var btn = $('[data-menu-toggle]');
        menu.classList.toggle('translate-x-full', !open);
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        if ('inert' in menu) menu.inert = !open;
        overlay.classList.toggle('hidden', !open);
        requestAnimationFrame(function () { overlay.classList.toggle('opacity-0', !open); });
        d.documentElement.classList.toggle('overflow-hidden', open);
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            lastFocus = d.activeElement;
            var first = $('a,button', menu);
            if (first) setTimeout(function () { first.focus(); }, 50);
        } else if (lastFocus) {
            lastFocus.focus();
        }
    }
    window.toggleMenu = function () {
        var menu = $('#mobile-menu');
        setMenu(menu ? menu.classList.contains('translate-x-full') : false);
    };

    /* ----------------------------------------------------- ظهور ملایم بخش‌ها */
    function initReveal() {
        var items = $$('.reveal');
        if (!items.length) return;
        if (reduceMotion || !('IntersectionObserver' in window)) { items.forEach(function (e) { e.classList.add('is-in'); }); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
        items.forEach(function (e) { io.observe(e); });
    }

    /* ----------------------------------------------- نوار CTA چسبان (لندینگ‌ها) */
    function initStickyCta() {
        var bar = $('[data-sticky-cta]');
        if (!bar) return;
        var shown = false;
        function onScroll() {
            var show = window.scrollY > 520;
            if (show !== shown) { shown = show; bar.classList.toggle('is-visible', show); }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ------------------------------------------------ فرم‌های ajax عمومی (لید) */
    function showMsg(box, text, ok) {
        if (!box) return;
        box.textContent = text;
        box.className = 'form-msg mt-3 ' + (ok ? 'is-ok' : 'is-err');
        box.hidden = false;
        box.setAttribute('role', ok ? 'status' : 'alert');
    }
    function firstError(json) {
        if (json && json.errors) { var k = Object.keys(json.errors)[0]; if (k) return json.errors[k][0]; }
        return json && json.message ? json.message : 'خطا در ارسال. لطفاً دوباره تلاش کنید.';
    }
    function initAjaxForms() {
        $$('form[data-ajax-form]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn = $('[type=submit]', form), msg = $('[data-form-msg]', form);
                var label = btn ? btn.innerHTML : '';
                $$('[aria-invalid]', form).forEach(function (i) { i.removeAttribute('aria-invalid'); });
                if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i><span>در حال ارسال…</span>'; }
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: new FormData(form),
                    credentials: 'same-origin'
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (json) { return { ok: res.ok, json: json }; });
                }).then(function (r) {
                    if (r.ok) {
                        showMsg(msg, (r.json && r.json.message) || 'درخواست شما ثبت شد.', true);
                        form.reset();
                        if (form.hasAttribute('data-hide-on-success')) { $$('[data-form-fields]', form).forEach(function (f) { f.hidden = true; }); }
                        refreshCaptchas(form);
                        var rid = $('[name=parent_id]', form); if (rid) rid.value = '';
                        var ri = $('[data-reply-indicator]'); if (ri) ri.hidden = true;
                    } else {
                        refreshCaptchas(form);
                        if (r.json && r.json.errors) {
                            Object.keys(r.json.errors).forEach(function (k) {
                                var el = form.elements[k]; if (el && el.setAttribute) el.setAttribute('aria-invalid', 'true');
                            });
                        }
                        showMsg(msg, firstError(r.json), false);
                    }
                }).catch(function () {
                    showMsg(msg, 'ارتباط با سرور برقرار نشد. اتصال اینترنت را بررسی کنید.', false);
                }).then(function () {
                    if (btn) { btn.disabled = false; btn.innerHTML = label; }
                });
            });
        });
    }

    /* ---------------------------------------------------- کپی کد (مقالات) */
    window.copyCode = function (button) {
        var holder = button.nextElementSibling || button.previousElementSibling;
        if (!holder || !navigator.clipboard) return;
        navigator.clipboard.writeText(holder.innerText).then(function () {
            var t = button.innerText;
            button.innerText = 'کپی شد';
            setTimeout(function () { button.innerText = t; }, 1800);
        });
    };

    /* ---------------------------------------------- پنل محاسبه‌گر قیمت پروژه */
    var est = { open: false, cat: null, catalog: null, lastFocus: null };
    function fmt(n) { return new Intl.NumberFormat('fa-IR').format(n); }
    function estEls() {
        return { panel: $('#estimator-panel'), backdrop: $('#estimator-backdrop'), list: $('#features-list'), wrap: $('#features-container'),
            total: $('#est-total'), msg: $('#est-message'), select: $('#service-category') };
    }
    window.toggleEstimator = function (force) {
        var el = estEls();
        if (!el.panel) return;
        var open = typeof force === 'boolean' ? force : !est.open;
        est.open = open;
        el.panel.classList.toggle('translate-x-full', !open);
        el.panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if ('inert' in el.panel) el.panel.inert = !open;
        el.backdrop.classList.toggle('hidden', !open);
        requestAnimationFrame(function () { el.backdrop.classList.toggle('opacity-0', !open); });
        d.documentElement.classList.toggle('overflow-hidden', open);
        $$('[data-estimator-open]').forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        if (open) {
            est.lastFocus = d.activeElement;
            setTimeout(function () { var s = $('#service-category'); if (s) s.focus(); }, 60);
        } else if (est.lastFocus) { est.lastFocus.focus(); }
    };
    function itemHtml(it, child) {
        var checked = it.required ? 'checked disabled' : '';
        return '<label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border bg-white ' +
            (child ? 'border-slate-100 p-2.5' : 'border-slate-200 p-3.5 hover:border-tech-blue/50') + ' min-h-[44px]">' +
            '<span class="flex items-center gap-3"><input type="checkbox" class="est-item h-5 w-5 rounded border-slate-300 text-tech-blue focus:ring-tech-blue" ' +
            'data-id="' + it.id + '" data-price="' + it.price + '" data-parent="' + (it.parent || '') + '" ' + checked + '>' +
            '<span class="text-sm ' + (child ? 'text-slate-600' : 'font-bold text-slate-800') + '">' + it.title +
            (it.required ? ' <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-bold text-red-600">الزامی</span>' : '') + '</span></span>' +
            '<span class="num shrink-0 text-xs font-bold text-slate-500">' + fmt(it.price / 1000000) + ' م</span></label>';
    }
    window.loadFeatures = function (cat) {
        var el = estEls();
        var data = est.catalog && est.catalog[cat];
        el.list.innerHTML = '';
        if (!data) { el.wrap.classList.add('hidden'); calc(); return; }
        el.wrap.classList.remove('hidden');
        var html = '';
        data.items.filter(function (i) { return !i.parent; }).forEach(function (it) {
            html += itemHtml(it, false);
            var kids = data.items.filter(function (c) { return c.parent === it.id; });
            if (kids.length) {
                html += '<div class="hidden space-y-2 border-s-2 border-slate-200 ps-3" data-children-of="' + it.id + '">' + kids.map(function (k) { return itemHtml(k, true); }).join('') + '</div>';
            }
        });
        el.list.innerHTML = html;
        calc();
    };
    function calc() {
        var el = estEls(), total = 0;
        $$('.est-item').forEach(function (c) {
            var box = c.closest('[data-children-of]');
            if (box && box.classList.contains('hidden')) return;
            if (c.checked) total += parseInt(c.getAttribute('data-price'), 10) || 0;
        });
        if (el.total) el.total.textContent = fmt(total);
    }
    function initEstimator() {
        var el = estEls();
        if (!el.panel || !window.__ESTIMATOR__) return;
        est.catalog = window.__ESTIMATOR__;
        el.select.addEventListener('change', function () { window.loadFeatures(this.value); });
        el.list.addEventListener('change', function (e) {
            var c = e.target;
            if (!c.classList.contains('est-item')) return;
            var kids = $('[data-children-of="' + c.getAttribute('data-id') + '"]', el.list);
            if (kids) {
                kids.classList.toggle('hidden', !c.checked);
                if (!c.checked) $$('input', kids).forEach(function (i) { i.checked = false; });
            }
            calc();
        });
        d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { if (est.open) window.toggleEstimator(false); setMenu(false); } });
        // ارسال
        window.submitEstimation = function () {
            var msg = el.msg, btn = $('#btn-submit-est'), text = $('#btn-text'), spin = $('#btn-loading');
            var items = [];
            $$('.est-item:checked').forEach(function (c) {
                var box = c.closest('[data-children-of]');
                if (box && box.classList.contains('hidden')) return;
                items.push({ id: c.getAttribute('data-id') });
            });
            function say(t, ok) { msg.textContent = t; msg.className = 'form-msg mt-3 text-center ' + (ok ? 'is-ok' : 'is-err'); msg.hidden = false; }
            if (!items.length) { say('لطفاً دست‌کم یک خدمت را انتخاب کنید.', false); return; }
            var name = $('#est-name').value.trim(), mobile = $('#est-mobile').value.trim(), email = $('#est-email').value.trim();
            if (!mobile && !email) { say('برای صدور پیش‌فاکتور، شماره موبایل یا ایمیل را وارد کنید.', false); return; }
            btn.disabled = true; text.classList.add('hidden'); spin.classList.remove('hidden'); msg.hidden = true;
            fetch(btn.getAttribute('data-url'), {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ client_name: name, client_email: email, client_phone: mobile, website: $('#est-website').value, items: items })
            }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (r) {
                  if (r.ok) {
                      say(r.j.message || 'پیش‌فاکتور صادر شد.', true);
                      if (r.j.redirect) {
                          var a = d.createElement('a'); a.href = r.j.redirect; a.target = '_blank'; a.rel = 'noopener';
                          a.className = 'btn btn-primary btn-block mt-3'; a.textContent = 'مشاهده پیش‌فاکتور';
                          msg.appendChild(d.createElement('br')); msg.appendChild(a);
                      }
                  } else { say(firstError(r.j), false); }
              }).catch(function () { say('ارتباط با سرور برقرار نشد.', false); })
              .then(function () { btn.disabled = false; text.classList.remove('hidden'); spin.classList.add('hidden'); });
        };
    }

    /* ---------------------------------- فرم تماس (/contact) — سازگار با نسخه قبلی */
    function initLegacyContact() {
        var form = $('#contact-form');
        if (!form || form.hasAttribute('data-ajax-form')) return;
        var wrapper = $('#contact-form-wrapper'), success = $('#contact-success'),
            btn = $('#contact-btn'), loading = $('#contact-loading'), text = $('#contact-text');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (btn) btn.disabled = true;
            if (text) text.classList.add('hidden');
            if (loading) loading.classList.remove('hidden');
            fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: new FormData(form) })
                .then(function (res) { if (!res.ok) throw new Error(); return res.text(); })
                .then(function () {
                    if (wrapper) wrapper.classList.add('hidden');
                    if (success) success.classList.remove('hidden');
                })
                .catch(function () {
                    var m = $('[data-form-msg]', form);
                    if (m) showMsg(m, 'خطا در ارسال پیام. لطفاً دوباره تلاش کنید.', false);
                    if (btn) btn.disabled = false;
                    if (loading) loading.classList.add('hidden');
                    if (text) text.classList.remove('hidden');
                });
        });
    }

    /* ---------------------------------------------------- کپچا */
    var capOwner = null;
    function refreshCaptcha(field) {
        var img = $('[data-captcha-image]', field), inp = $('[data-captcha-input]', field);
        if (!img || !field.dataset.refreshUrl) return Promise.resolve();
        capOwner = field;
        return fetch(field.dataset.refreshUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j && j.image) { img.src = j.image; if (inp) inp.value = ''; field.removeAttribute('data-lazy'); } })
            .catch(function () {});
    }
    function refreshCaptchas(scope) {
        // کد کپچا در session یکی است؛ فقط نمونه‌ی همین فرم تازه می‌شود
        $$('[data-captcha]', scope || d).forEach(refreshCaptcha);
    }
    window.refreshCaptcha = function () { var f = $('[data-captcha]'); if (f) refreshCaptcha(f); };
    function initCaptcha() {
        var first = $('[data-captcha]'); if (first && !first.dataset.lazy) capOwner = first;
        d.addEventListener('click', function (e) {
            var t = e.target.closest && e.target.closest('[data-captcha-refresh], [data-captcha-image]');
            if (!t) return;
            e.preventDefault(); refreshCaptcha(t.closest('[data-captcha]'));
        });
        d.addEventListener('focusin', function (e) {
            var f = e.target.closest && e.target.closest('[data-captcha]');
            if (f && f !== capOwner) refreshCaptcha(f);
        });
    }

    /* ---------------------------------------------------- فهرست مطالب مقاله */
    function initToc() {
        var body = $('[data-post-body]'), box = $('[data-toc]'), list = $('[data-toc-list]');
        if (!body || !box || !list) return;
        var hs = $$('h2, h3', body);
        if (hs.length < 2) return;
        var n = 0;
        hs.forEach(function (h, i) {
            if (!h.id) h.id = 'sec-' + (i + 1);
            var a = d.createElement('a'), li = d.createElement('li');
            a.href = '#' + h.id; a.textContent = h.textContent.trim();
            a.className = 'toc-link' + (h.tagName === 'H3' ? ' ms-4' : '');
            li.appendChild(a); list.appendChild(li);
        });
        box.hidden = false;
        if (!('IntersectionObserver' in window)) return;
        var links = $$('.toc-link', list);
        var io = new IntersectionObserver(function (en) {
            en.forEach(function (x) {
                if (!x.isIntersecting) return;
                links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + x.target.id); });
            });
        }, { rootMargin: '-80px 0px -70% 0px' });
        hs.forEach(function (h) { io.observe(h); });
    }

    /* ---------------------------------------------------- پاسخ به دیدگاه */
    function initReply() {
        var form = $('#comment-form'); if (!form) return;
        var pid = $('[name=parent_id]', form), ind = $('[data-reply-indicator]');
        d.addEventListener('click', function (e) {
            var b = e.target.closest && e.target.closest('[data-reply-id]');
            if (b) {
                pid.value = b.dataset.replyId;
                if (ind) { ind.hidden = false; var t = $('[data-reply-name]', ind); if (t) t.textContent = b.dataset.replyName; }
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var ta = $('textarea', form); if (ta) ta.focus({ preventScroll: true });
            }
            if (e.target.closest && e.target.closest('[data-reply-cancel]')) { pid.value = ''; if (ind) ind.hidden = true; }
        });
    }

    d.addEventListener('DOMContentLoaded', function () {
        initReveal();
        initCaptcha();
        initToc();
        initReply();
        initStickyCta();
        initAjaxForms();
        initEstimator();
        initLegacyContact();
        var ov = $('#menu-overlay'); if (ov) ov.addEventListener('click', function () { setMenu(false); });
        $$('#mobile-menu a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
        d.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
        // بستن <details> منوها با کلیک بیرون یا Escape
        d.addEventListener('click', function (e) {
            $$('details.menu-details[open]').forEach(function (x) { if (!x.contains(e.target)) x.removeAttribute('open'); });
        });
        d.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') $$('details.menu-details[open]').forEach(function (x) { x.removeAttribute('open'); var s = x.querySelector('summary'); if (s) s.focus(); });
        });
        // ESC برای منوهای کشویی دسکتاپ: blur روی لینک فعال
        $$('[data-dropdown]').forEach(function (dd) {
            dd.addEventListener('keydown', function (e) { if (e.key === 'Escape' && d.activeElement) d.activeElement.blur(); });
        });
    });
})();
