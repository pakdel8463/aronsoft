/* بلوک‌های تعاملی صفحه‌ساز: کاروسل (اسلایدر تمام‌عرض و اسلایدر کارت) و تب‌ها.
   کاروسل روی scroll-snap ساخته شده: کشیدن با انگشت و اسکرول افقی بومی کار می‌کند؛ این اسکریپت
   فلش‌ها، نقطه‌ها، پخش خودکار و کیبورد را اضافه می‌کند. در RTL هم درست کار می‌کند (جابه‌جایی نسبی). */
(function () {
    'use strict';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function carousel(root) {
        if (root.dataset.bkReady) return;
        root.dataset.bkReady = '1';
        var track = root.querySelector('[data-track]');
        var slides = Array.prototype.slice.call(track.querySelectorAll('[data-slide]'));
        if (!slides.length) return;
        var prev = root.querySelector('[data-prev]');
        var next = root.querySelector('[data-next]');
        var dots = Array.prototype.slice.call(root.querySelectorAll('[data-dot]'));
        var pauseBtn = root.querySelector('[data-pause]');
        var isCards = root.hasAttribute('data-cards');
        var rtl = getComputedStyle(track).direction === 'rtl';
        var delay = parseInt(root.dataset.autoplay || '0', 10) || 0;
        var timer = null, paused = false, userPaused = false, current = 0;

        // فاصله‌ی لبه‌ی شروع (راست در RTL) اسلاید از لبه‌ی شروع مسیر
        function offset(el) {
            var a = el.getBoundingClientRect(), t = track.getBoundingClientRect();
            return rtl ? t.right - a.right : a.left - t.left;
        }
        function perView() {
            var w = slides[0].getBoundingClientRect().width || 1;
            return Math.max(1, Math.round(track.clientWidth / w));
        }
        function lastStart() { return Math.max(0, slides.length - perView()); }
        function indexNow() {
            var best = 0, min = Infinity;
            slides.forEach(function (s, i) { var d = Math.abs(offset(s)); if (d < min) { min = d; best = i; } });
            return best;
        }
        function goTo(i, wrap) {
            var last = lastStart();
            if (i > last) i = wrap ? 0 : last;
            if (i < 0) i = wrap ? last : 0;
            var d = offset(slides[i]);
            track.scrollBy({ left: rtl ? -d : d, behavior: reduce ? 'auto' : 'smooth' });
            update(i);
        }
        function update(i) {
            current = i;
            dots.forEach(function (dot, k) { dot.setAttribute('aria-current', k === i ? 'true' : 'false'); });
            if (!isCards) {
                // فقط لینک‌های اسلاید فعال با Tab قابل دسترسی‌اند
                slides.forEach(function (s, k) {
                    s.setAttribute('aria-hidden', k === i ? 'false' : 'true');
                    s.querySelectorAll('[data-slide-focusable]').forEach(function (a) { a.tabIndex = k === i ? 0 : -1; });
                });
            } else {
                var fits = lastStart() === 0;
                if (prev) { prev.disabled = i <= 0; prev.hidden = fits; }
                if (next) { next.disabled = i >= lastStart(); next.hidden = fits; }
            }
        }
        function step(dir) { goTo(current + dir * (isCards ? perView() : 1), !isCards); }

        if (prev) prev.addEventListener('click', function () { step(-1); restart(); });
        if (next) next.addEventListener('click', function () { step(1); restart(); });
        dots.forEach(function (dot) { dot.addEventListener('click', function () { goTo(+dot.dataset.dot); restart(); }); });

        var raf = null;
        track.addEventListener('scroll', function () {
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(function () { var i = indexNow(); if (i !== current) update(i); else if (isCards) update(i); });
        }, { passive: true });

        root.addEventListener('keydown', function (e) {
            if (e.target.closest('input,textarea,select')) return;
            if (e.key === 'ArrowLeft') { e.preventDefault(); step(rtl ? 1 : -1); restart(); }
            if (e.key === 'ArrowRight') { e.preventDefault(); step(rtl ? -1 : 1); restart(); }
        });

        // پخش خودکار: با hover/focus، تب پنهان یا ترجیح «حرکت کمتر» متوقف می‌شود
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function start() {
            stop();
            if (!delay || reduce || paused || userPaused || document.hidden) return;
            timer = setInterval(function () { step(1); }, delay);
        }
        function restart() { if (timer) start(); }
        if (delay && !reduce) {
            root.addEventListener('mouseenter', function () { paused = true; stop(); });
            root.addEventListener('mouseleave', function () { paused = false; start(); });
            root.addEventListener('focusin', function () { paused = true; stop(); });
            root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) { paused = false; start(); } });
            track.addEventListener('pointerdown', function () { paused = true; stop(); }, { passive: true });
            document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
            if ('IntersectionObserver' in window) {
                new IntersectionObserver(function (en) { en[0].isIntersecting ? (paused = false, start()) : (paused = true, stop()); }, { threshold: 0.3 }).observe(root);
            } else start();
        } else if (pauseBtn) {
            pauseBtn.hidden = true;
        }
        if (pauseBtn) {
            pauseBtn.addEventListener('click', function () {
                userPaused = !userPaused;
                pauseBtn.setAttribute('aria-label', userPaused ? 'ادامه‌ی پخش خودکار' : 'توقف پخش خودکار');
                pauseBtn.innerHTML = '<i class="ph ' + (userPaused ? 'ph-play' : 'ph-pause') + '" aria-hidden="true"></i>';
                userPaused ? stop() : start();
            });
        }
        update(0);
        window.addEventListener('resize', function () { update(indexNow()); });
    }

    function tabs(root) {
        if (root.dataset.bkReady) return;
        root.dataset.bkReady = '1';
        var list = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
        function select(tab, focus) {
            list.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                var panel = document.getElementById(t.getAttribute('aria-controls'));
                if (panel) panel.hidden = !on;
            });
            if (focus) tab.focus();
            if (tab.scrollIntoView && tab.parentNode.scrollWidth > tab.parentNode.clientWidth) tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }
        list.forEach(function (t, i) {
            t.addEventListener('click', function () { select(t); });
            t.addEventListener('keydown', function (e) {
                var rtl = getComputedStyle(t).direction === 'rtl', n = list.length, j = null;
                if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') j = e.key === 'ArrowDown' ? i + 1 : (rtl ? i + 1 : i - 1);
                if (e.key === 'ArrowRight' || e.key === 'ArrowUp') j = e.key === 'ArrowUp' ? i - 1 : (rtl ? i - 1 : i + 1);
                if (e.key === 'Home') j = 0;
                if (e.key === 'End') j = n - 1;
                if (j === null) return;
                e.preventDefault();
                select(list[(j + n) % n], true);
            });
        });
    }

    function boot() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-carousel]'), carousel);
        Array.prototype.forEach.call(document.querySelectorAll('[data-tabs-block]'), tabs);
    }
    window.AronBlocks = { init: boot };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
