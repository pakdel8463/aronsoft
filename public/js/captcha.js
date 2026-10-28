/*
 * کپچای نامرئی آرون‌سافت (proof-of-work، سازگار با ALTCHA). بدون build؛ مستقیم از public/js.
 * ویجت: <x-captcha /> (resources/views/components/captcha.blade.php)
 * مرورگر عددی را پیدا می‌کند که sha256(salt + number) = challenge شود و جواب را در input[name=captcha] می‌گذارد.
 */
(function () {
    'use strict';

    /* ---------- SHA-256 (هم‌زمان، برای ورودی‌های کوتاه سریع‌تر از crypto.subtle) ---------- */
    var K = new Uint32Array([
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2]);
    var W = new Uint32Array(64);
    var HEX = [];
    for (var h = 0; h < 256; h++) HEX[h] = (h < 16 ? '0' : '') + h.toString(16);

    function sha256hex(str) {
        // ASCII only (salt + digits)
        var len = str.length, words = ((len + 8) >> 6) + 1, m = new Uint32Array(words * 16), i;
        for (i = 0; i < len; i++) m[i >> 2] |= str.charCodeAt(i) << (24 - (i % 4) * 8);
        m[len >> 2] |= 0x80 << (24 - (len % 4) * 8);
        m[words * 16 - 1] = len * 8;
        var H0 = 0x6a09e667, H1 = 0xbb67ae85, H2 = 0x3c6ef372, H3 = 0xa54ff53a, H4 = 0x510e527f, H5 = 0x9b05688c, H6 = 0x1f83d9ab, H7 = 0x5be0cd19;
        for (var b = 0; b < m.length; b += 16) {
            for (i = 0; i < 16; i++) W[i] = m[b + i];
            for (i = 16; i < 64; i++) {
                var x = W[i - 15], y = W[i - 2];
                var s0 = ((x >>> 7) | (x << 25)) ^ ((x >>> 18) | (x << 14)) ^ (x >>> 3);
                var s1 = ((y >>> 17) | (y << 15)) ^ ((y >>> 19) | (y << 13)) ^ (y >>> 10);
                W[i] = (W[i - 16] + s0 + W[i - 7] + s1) | 0;
            }
            var a = H0, bb = H1, c = H2, d = H3, e = H4, f = H5, g = H6, hh = H7;
            for (i = 0; i < 64; i++) {
                var S1 = ((e >>> 6) | (e << 26)) ^ ((e >>> 11) | (e << 21)) ^ ((e >>> 25) | (e << 7));
                var t1 = (hh + S1 + ((e & f) ^ (~e & g)) + K[i] + W[i]) | 0;
                var S0 = ((a >>> 2) | (a << 30)) ^ ((a >>> 13) | (a << 19)) ^ ((a >>> 22) | (a << 10));
                var t2 = (S0 + ((a & bb) ^ (a & c) ^ (bb & c))) | 0;
                hh = g; g = f; f = e; e = (d + t1) | 0; d = c; c = bb; bb = a; a = (t1 + t2) | 0;
            }
            H0 = (H0 + a) | 0; H1 = (H1 + bb) | 0; H2 = (H2 + c) | 0; H3 = (H3 + d) | 0;
            H4 = (H4 + e) | 0; H5 = (H5 + f) | 0; H6 = (H6 + g) | 0; H7 = (H7 + hh) | 0;
        }
        var out = '', hs = [H0, H1, H2, H3, H4, H5, H6, H7];
        for (i = 0; i < 8; i++) out += HEX[(hs[i] >>> 24) & 255] + HEX[(hs[i] >>> 16) & 255] + HEX[(hs[i] >>> 8) & 255] + HEX[hs[i] & 255];
        return out;
    }

    /* ---------- حل معما در تکه‌های کوچک تا صفحه کند نشود ---------- */
    function solve(c) {
        return new Promise(function (resolve, reject) {
            var n = 0, max = c.maxnumber || 1000000;
            (function chunk() {
                var end = Math.min(max, n + 4000);
                for (; n <= end; n++) {
                    if (sha256hex(c.salt + n) === c.challenge) {
                        return resolve(btoa(JSON.stringify({ algorithm: 'SHA-256', challenge: c.challenge, number: n, salt: c.salt, signature: c.signature })));
                    }
                }
                if (n > max) return reject(new Error('unsolved'));
                setTimeout(chunk, 0);
            })();
        });
    }

    function expired(c) {
        var m = /[?&]expires=(\d+)/.exec(c.salt || '');
        return !m || +m[1] * 1000 < Date.now() + 30000;
    }

    function init(box) {
        if (box.dataset.powReady) return;
        box.dataset.powReady = '1';
        var input = box.querySelector('[data-pow-value]');
        var status = box.querySelector('[data-pow-status]');
        var icon = box.querySelector('[data-pow-icon]');
        var form = box.closest('form');
        var challenge = null;
        try { challenge = JSON.parse(box.dataset.challenge || 'null'); } catch (e) { challenge = null; }
        var job = null;

        function set(state, text) {
            box.dataset.state = state;
            status.textContent = text;
            icon.className = 'ph text-base ' + (state === 'done' ? 'ph-shield-check text-emerald-600' : state === 'error' ? 'ph-warning-circle text-red-600' : 'ph-circle-notch animate-spin');
        }

        function fresh() {
            return fetch(box.dataset.refreshUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error('http'); return r.json(); });
        }

        function run() {
            if (job) return job;
            input.value = '';
            set('working', 'بررسی امنیتی خودکار…');
            var start = challenge && !expired(challenge) ? Promise.resolve(challenge) : fresh();
            job = start.then(function (c) { challenge = c; return solve(c); })
                .then(function (token) { input.value = token; set('done', 'بررسی امنیتی انجام شد'); return token; })
                .catch(function () { job = null; challenge = null; set('error', 'بررسی امنیتی انجام نشد؛ دوباره تلاش می‌شود…'); setTimeout(run, 3000); throw new Error('captcha'); });
            return job;
        }

        // جواب هر معما فقط یک بار پذیرفته می‌شود؛ بعد از هر ارسال یک معمای تازه حل می‌شود
        function renew() { job = null; challenge = null; setTimeout(run, 1200); }

        if (form) {
            // قبل از بقیه‌ی شنونده‌ها (مثلاً ارسال ایجکسی): اگر هنوز حل نشده، صبر کن و دوباره ارسال کن
            form.addEventListener('submit', function (e) {
                if (input.value) { renew(); return; }
                e.preventDefault();
                e.stopImmediatePropagation();
                var submitter = e.submitter || null;
                run().then(function () {
                    if (typeof form.requestSubmit === 'function') form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
                    else form.submit();
                }).catch(function () {});
            }, true);
            ['focusin', 'pointerdown', 'input'].forEach(function (ev) { form.addEventListener(ev, function () { run().catch(function () {}); }, { once: true, passive: true }); });
        }
        // بدون تعامل هم کمی بعد از بارگذاری حل می‌شود
        (window.requestIdleCallback || function (f) { setTimeout(f, 1500); })(function () { run().catch(function () {}); });
    }

    function boot() { Array.prototype.forEach.call(document.querySelectorAll('[data-pow-captcha]'), init); }
    window.AronCaptcha = { init: boot, sha256: sha256hex };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
