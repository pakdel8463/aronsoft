/* فرم‌های فرم‌ساز در سایت: منطق شرطی، چندمرحله‌ای، نام فایل انتخاب‌شده. معادل App\Forms\FormSchema (از پنل ستاره ماندگار). */
(function () {
  function num(v) { v = String(v).replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }); return v !== '' && !isNaN(v) ? parseFloat(v) : null; }

  function matches(value, op, target) {
    var list = Array.isArray(value) ? value.map(String) : [String(value == null ? '' : value)];
    var joined = list.join(',');
    var low = joined.toLowerCase(), t = String(target || '').toLowerCase();
    switch (op) {
      case 'is': return Array.isArray(value) ? list.indexOf(target) !== -1 : joined === target;
      case 'isnot': return Array.isArray(value) ? list.indexOf(target) === -1 : joined !== target;
      case 'contains': return t !== '' && low.indexOf(t) !== -1;
      case 'notcontains': return t === '' || low.indexOf(t) === -1;
      case 'gt': return num(joined) !== null && num(target) !== null && num(joined) > num(target);
      case 'lt': return num(joined) !== null && num(target) !== null && num(joined) < num(target);
      case 'empty': return joined.trim() === '';
      case 'notempty': return joined.trim() !== '';
    }
    return false;
  }

  function init(form) {
    var wrappers = Array.prototype.slice.call(form.querySelectorAll('[data-field]'));
    var byKey = {};
    wrappers.forEach(function (w) { byKey[w.dataset.field] = w; });

    function valueOf(key) {
      var w = byKey[key];
      if (!w || w.hidden) return '';
      var type = w.dataset.type;
      var els = w.querySelectorAll('input, select, textarea');
      if (type === 'checkbox') return Array.prototype.filter.call(els, function (e) { return e.checked; }).map(function (e) { return e.value; });
      if (type === 'radio') { var c = w.querySelector('input:checked'); return c ? c.value : ''; }
      if (type === 'consent') { return w.querySelector('input').checked ? '1' : ''; }
      if (type === 'date') {
        var p = {}; els.forEach(function (e) { p[e.name.slice(-2, -1)] = e.value; });
        return p.y && p.m && p.d ? p.y + '/' + ('0' + p.m).slice(-2) + '/' + ('0' + p.d).slice(-2) : '';
      }
      return els[0] ? els[0].value : '';
    }

    function setVisible(w, show) {
      if (w.hidden === !show) return false;
      w.hidden = !show;
      w.querySelectorAll('input, select, textarea').forEach(function (e) { e.disabled = !show; });
      return true;
    }

    function applyLogic() {
      for (var pass = 0; pass < 10; pass++) {
        var changed = false;
        wrappers.forEach(function (w) {
          if (!w.dataset.logic) return;
          var logic = JSON.parse(w.dataset.logic);
          var results = logic.rules.map(function (r) { return matches(valueOf(r.field), r.op, r.value); });
          var hit = logic.match === 'any' ? results.indexOf(true) !== -1 : results.indexOf(false) === -1;
          if (setVisible(w, logic.action === 'show' ? hit : !hit)) changed = true;
        });
        if (!changed) break;
      }
    }

    // ---- steps
    var steps = Array.prototype.slice.call(form.querySelectorAll('[data-step]'));
    var current = parseInt(form.dataset.start || '0', 10);
    var total = steps.length;
    var root = form.closest('.fm') || form;

    function showStep(i, focus) {
      current = Math.max(0, Math.min(total - 1, i));
      steps.forEach(function (s, k) { s.hidden = k !== current; });
      root.querySelectorAll('[data-progress-step]').forEach(function (li) {
        var k = parseInt(li.dataset.progressStep, 10);
        li.dataset.state = k === current ? 'active' : (k < current ? 'done' : '');
      });
      var bar = root.querySelector('[data-progress-bar]');
      if (bar) bar.style.width = ((current + 1) / total * 100) + '%';
      var label = root.querySelector('[data-progress-label]');
      if (label) label.textContent = 'مرحله‌ی ' + (current + 1).toLocaleString('fa-IR') + ' از ' + total.toLocaleString('fa-IR');
      if (focus) {
        root.scrollIntoView({ block: 'start', behavior: 'smooth' });
        var first = steps[current].querySelector('input:not([type=hidden]):not(:disabled), select:not(:disabled), textarea:not(:disabled)');
        if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 250);
      }
    }

    function stepValid(i) {
      var ok = true;
      var fields = steps[i].querySelectorAll('input, select, textarea');
      for (var k = 0; k < fields.length; k++) {
        var el = fields[k];
        if (el.disabled || el.type === 'hidden' || el.closest('[data-field][hidden]')) continue;
        if (!el.checkValidity()) {
          if (ok) { el.reportValidity ? el.reportValidity() : null; el.focus(); }
          el.setAttribute('aria-invalid', 'true');
          ok = false;
        } else if (el.getAttribute('aria-invalid') === 'true' && el.type !== 'file') {
          el.removeAttribute('aria-invalid');
        }
      }
      // required checkbox groups (HTML has no native support)
      steps[i].querySelectorAll('[data-type=checkbox]').forEach(function (w) {
        if (w.hidden || !w.hasAttribute('data-required')) return;
        if (!w.querySelector('input:checked')) {
          if (ok) w.querySelector('input').focus();
          ok = false;
        }
      });
      return ok;
    }

    form.addEventListener('click', function (e) {
      if (e.target.closest('[data-next]')) { e.preventDefault(); applyLogic(); if (stepValid(current)) showStep(current + 1, true); }
      if (e.target.closest('[data-prev]')) { e.preventDefault(); showStep(current - 1, true); }
    });
    form.addEventListener('submit', function (e) {
      applyLogic();
      if (current < total - 1) {
        e.preventDefault(); e.stopImmediatePropagation();
        if (stepValid(current)) showStep(current + 1, true);
        return;
      }
      if (!stepValid(current)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);

    form.addEventListener('input', applyLogic);
    form.addEventListener('change', function (e) {
      applyLogic();
      if (e.target.type === 'file') {
        var label = e.target.closest('label').querySelector('[data-file-name]');
        if (label) label.textContent = e.target.files.length ? e.target.files[0].name : 'فایل را انتخاب کنید';
      }
    });

    applyLogic();
    showStep(current, false);
  }

  function boot() { document.querySelectorAll('form[data-fm-form]').forEach(init);
    var here = document.querySelector('[data-scroll-here]');
    if (here && !location.hash) here.scrollIntoView({ block: 'start' }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
