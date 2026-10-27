// Admin panel entry (Tailwind v3 build, no framework: see resources/js/admin/shell.js)
import './admin/shell.js';
import './admin/permissions.js'; // permission matrix (roles / users)
import './admin/menus.js'; // menu item tree builder
import './admin/proformas.js'; // proforma builder rows
import './admin/content.js'; // posts / media library / rich editor (lazy: acts only when its markup exists)
import './admin/product-page-form.js'; // product landing page editor (acts only on [data-pp-form])

// fetch helper with CSRF + XHR headers:  adminFetch(url, {method, body})
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
window.adminFetch = (url, opts = {}) =>
    fetch(url, {
        credentials: 'same-origin',
        ...opts,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            ...(opts.headers || {}),
        },
    });
