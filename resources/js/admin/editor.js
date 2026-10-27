/*
 * Rich editor: CKEditor 4 (shipped in /public/ckeditor, no CDN) wrapped for the admin panel.
 * Auto-inits every <textarea data-aron-editor> (see <x-admin.editor>). window.AronEditor:
 *   AronEditor.get(nameOrId) -> CKEDITOR instance | null     AronEditor.ready(nameOrId) -> Promise
 *   AronEditor.setData(nameOrId, html)                       Textarea fires 'aron-editor:change' {words, chars}
 * Image button / paste / drag&drop go through our media library (AronMedia). If CKEditor can't load, the textarea stays usable.
 */
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const faNum = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
const toast = (m, t = 'success') => (window.adminToast ? window.adminToast(m, t) : console.log(m));

const instances = new Map();
const waiters = new Map();
let ckPromise = null;

function loadCk(base, v) {
    if (window.CKEDITOR && window.CKEDITOR.status === 'loaded') return Promise.resolve(window.CKEDITOR);
    if (ckPromise) return ckPromise;
    window.CKEDITOR_BASEPATH = base;
    ckPromise = new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = base + 'ckeditor.js' + (v ? '?v=' + v : '');
        s.onload = () => resolve(window.CKEDITOR);
        s.onerror = () => reject(new Error('ckeditor.js not found'));
        document.head.appendChild(s);
    });
    return ckPromise;
}

/** لینک یوتیوب/آپارات/ویمئو → آدرس embed (هم‌منطق با HtmlSanitizer::embedUrl در PHP) */
function embedUrl(url) {
    url = (url || '').trim();
    let m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,15})/);
    if (m) return 'https://www.youtube-nocookie.com/embed/' + m[1];
    m = url.match(/aparat\.com\/v\/([A-Za-z0-9]+)/);
    if (m) return 'https://www.aparat.com/video/video/embed/videohash/' + m[1] + '/vt/frame';
    m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
    if (m) return 'https://player.vimeo.com/video/' + m[1];
    return null;
}

/** پنجره‌ی کوچک دریافت لینک ویدیو */
function askEmbed() {
    return new Promise((resolve) => {
        const w = document.createElement('div');
        w.style.zIndex = '20000';
        w.className = 'fixed inset-0 flex items-center justify-center p-4';
        w.innerHTML = `<div class="absolute inset-0 bg-rail/60 backdrop-blur-[2px]" data-x></div>
          <form class="relative w-full max-w-md rounded-[1.5rem] bg-white p-6 shadow-adm-pop">
            <h3 class="text-base font-extrabold text-ink">درج ویدیو</h3>
            <p class="mt-1 text-xs leading-6 text-slate-500">لینک صفحه‌ی ویدیو در یوتیوب، آپارات یا ویمئو را بچسبانید.</p>
            <input class="a-input mt-4" dir="ltr" placeholder="https://www.aparat.com/v/xxxxx" data-url required>
            <p class="mt-2 hidden text-xs font-semibold text-coral-600" data-err>لینک معتبر نیست؛ فقط یوتیوب، آپارات و ویمئو پشتیبانی می‌شود.</p>
            <div class="mt-5 flex justify-end gap-2"><button type="button" data-x class="a-btn a-btn-secondary a-btn-sm">انصراف</button><button class="a-btn a-btn-primary a-btn-sm">درج</button></div>
          </form>`;
        document.body.appendChild(w);
        const input = $('[data-url]', w);
        input.focus();
        const done = (v) => { w.remove(); resolve(v); };
        w.addEventListener('click', (e) => { if (e.target.closest('[data-x]')) done(null); });
        w.addEventListener('keydown', (e) => { if (e.key === 'Escape') { e.stopPropagation(); done(null); } });
        $('form', w).addEventListener('submit', (e) => {
            e.preventDefault();
            const u = embedUrl(input.value);
            if (!u) { $('[data-err]', w).classList.remove('hidden'); return; }
            done(u);
        });
    });
}

function embedHtml(src) {
    return '<div class="aron-embed" style="aspect-ratio: 16 / 9; max-width: 100%; margin: 1.5rem 0"><iframe src="' + src + '" width="560" height="315" style="width: 100%; height: 100%; border: 0" allowfullscreen="allowfullscreen" loading="lazy"></iframe></div><p></p>';
}

function mediaHtml(m, o) {
    const AM = window.AronMedia;
    if (m.type === 'image') return AM.imageHtml(m, o || {});
    if (m.type === 'video') return '<video controls preload="metadata" src="' + AM.esc(m.rel) + '" style="max-width: 100%"></video><p></p>';
    if (m.type === 'audio') return '<audio controls src="' + AM.esc(m.rel) + '"></audio><p></p>';
    return '<a href="' + AM.esc(m.rel) + '" target="_blank" download>' + AM.esc(m.name || m.file_name) + '</a>&nbsp;';
}

function registerPlugins(CK, assets) {
    if (CK.plugins.get('aronmedia')) return;

    CK.plugins.add('aronmedia', {
        init(editor) {
            editor.addCommand('aronmedia', {
                exec() {
                    window.AronMedia.pick({ multiple: true, insert: true, title: 'درج تصویر یا رسانه' }).then((items) => {
                        if (!items || !items.length) return;
                        editor.focus();
                        items.forEach((m) => editor.insertHtml(mediaHtml(m, m._insert), 'unfiltered_html'));
                        editor.fire('change');
                    });
                },
                canUndo: false,
            });
            editor.ui.addButton('AronMedia', { label: 'درج تصویر / رسانه از کتابخانه', command: 'aronmedia', toolbar: 'insert,0', icon: assets + 'icons/media.svg' });
        },
    });

    CK.plugins.add('aronembed', {
        init(editor) {
            editor.addCommand('aronembed', {
                exec() {
                    askEmbed().then((src) => { if (src) { editor.focus(); editor.insertHtml(embedHtml(src), 'unfiltered_html'); editor.fire('change'); } });
                },
                canUndo: false,
            });
            editor.ui.addButton('AronEmbed', { label: 'درج ویدیو (یوتیوب / آپارات)', command: 'aronembed', toolbar: 'insert,1', icon: assets + 'icons/video.svg' });
        },
    });

    if (!CK.stylesSet.get('aron')) {
        CK.stylesSet.add('aron', [
            { name: 'پاراگراف مقدمه (درشت)', element: 'p', attributes: { style: 'font-size: 1.15em; font-weight: 600' } },
            { name: 'کد درون‌خطی', element: 'code' },
            { name: 'متن هایلایت', element: 'mark' },
            { name: 'متن کوچک', element: 'small' },
            { name: 'جعبه‌ی نکته (زرد)', element: 'div', attributes: { class: 'info-box' } },
            { name: 'جعبه‌ی برجسته (آبی)', element: 'div', attributes: { class: 'highlight-box' } },
            { name: 'جعبه‌ی تعریف', element: 'div', attributes: { class: 'definition-box' } },
            { name: 'بلوک کد (LTR)', element: 'pre' },
        ]);
    }

    // دکمه‌ی «مرور سرور» پنجره‌ی تصویر → کتابخانه‌ی خودمان
    CK.on('dialogDefinition', (ev) => {
        if (ev.data.name !== 'image') return;
        const info = ev.data.definition.getContents('info');
        const browse = info && info.get('browse');
        if (!browse) return;
        browse.hidden = false;
        browse.label = 'انتخاب از کتابخانه';
        browse.filebrowser = null;
        browse.onClick = function () {
            const dlg = this.getDialog();
            window.AronMedia.pick({ type: 'image', title: 'انتخاب تصویر' }).then((items) => {
                if (!items || !items[0]) return;
                const m = items[0];
                dlg.setValueOf('info', 'txtUrl', m.large_rel || m.rel);
                if (m.alt) dlg.setValueOf('info', 'txtAlt', m.alt);
            });
        };
    }, null, null, 20);
}

function stats(ed) {
    const text = (ed.document && ed.document.getBody() ? ed.document.getBody().getText() : '').replace(/‌/g, ' ').trim();
    const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
    return { words, chars: text.replace(/\s/g, '').length, minutes: Math.max(1, Math.ceil(words / 200)) };
}

async function uploadImageFiles(ed, files) {
    const imgs = files.filter((f) => /^image\//.test(f.type));
    if (!imgs.length) return false;
    toast('در حال آپلود تصویر…', 'info');
    const items = await window.AronMedia.upload(imgs, {
        error: (i, msg, f) => toast((f ? f.name + ': ' : '') + msg, 'error'),
    });
    items.forEach((m) => ed.insertHtml(window.AronMedia.imageHtml(m, { size: 'large', align: 'center' }), 'unfiltered_html'));
    if (items.length) { ed.fire('change'); toast('تصویر درج شد؛ متن جایگزین (alt) را فراموش نکنید.', 'success'); }
    return true;
}

function setup(ta) {
    if (ta.dataset.aronReady) return;
    ta.dataset.aronReady = '1';
    const base = ta.dataset.ckBase;
    const assets = ta.dataset.assets;
    const height = parseInt(ta.dataset.height || '480', 10);
    const statEl = $('[data-editor-stats]', ta.closest('[data-aron-editor-wrap]') || document);
    const key = ta.id || ta.name;

    loadCk(base, ta.dataset.v).then((CK) => {
        CK.disableAutoInline = true;
        registerPlugins(CK, assets);

        const ed = CK.replace(ta, {
            customConfig: '',
            language: 'fa',
            contentsLangDirection: 'rtl',
            skin: 'moono-lisa',
            height,
            allowedContent: true, // پاک‌سازی واقعی سمت سرور است (HtmlSanitizer)
            entities: false,
            extraPlugins: 'aronmedia,aronembed',
            removePlugins: 'scayt,wsc,flash,forms,about,smiley,templates,save,newpage,print,preview,elementspath',
            removeDialogTabs: 'link:advanced;table:advanced;image:advanced',
            toolbarCanCollapse: false,
            resize_dir: 'vertical',
            format_tags: 'p;h2;h3;h4;pre',
            stylesSet: 'aron',
            contentsCss: [base + 'contents.css', assets + 'editor-content.css?v=' + (ta.dataset.v || '1')],
            bodyClass: 'aron-content',
            colorButton_enableMore: false,
            fontSize_sizes: 'کوچک/0.85em;معمولی/1em;بزرگ/1.25em;خیلی بزرگ/1.6em',
            pasteFromWordRemoveFontStyles: true,
            pasteFromWordRemoveStyles: true,
            pasteFromWordPromptCleanup: false,
            tabSpaces: 4,
            dialog_backgroundCoverColor: '#0e1330',
            dialog_backgroundCoverOpacity: 0.55,
            toolbar: [
                { name: 'format', items: ['Format', 'Styles'] },
                { name: 'basic', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', 'RemoveFormat'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'lists', items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'] },
                { name: 'align', items: ['JustifyRight', 'JustifyCenter', 'JustifyLeft', 'JustifyBlock', 'BidiRtl', 'BidiLtr'] },
                { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                { name: 'insert', items: ['AronMedia', 'AronEmbed', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak'] },
                { name: 'paste', items: ['PasteText', 'PasteFromWord'] },
                { name: 'tools', items: ['Undo', 'Redo', 'Find', 'Replace', 'SelectAll'] },
                { name: 'view', items: ['ShowBlocks', 'Source', 'Maximize'] },
            ],
        });

        instances.set(key, ed);
        instances.set(ta.name, ed);

        const emit = () => {
            const s = stats(ed);
            if (statEl) statEl.textContent = faNum(s.words) + ' کلمه · ' + faNum(s.chars) + ' نویسه · حدود ' + faNum(s.minutes) + ' دقیقه مطالعه';
            ta.dispatchEvent(new CustomEvent('aron-editor:change', { bubbles: true, detail: Object.assign({ html: ed.getData() }, s) }));
        };
        let t;
        const later = () => { clearTimeout(t); t = setTimeout(emit, 300); };

        ed.on('instanceReady', () => {
            const doc = ed.document.$;
            emit();
            ['change', 'key', 'paste', 'afterCommandExec', 'mode'].forEach((n) => ed.on(n, later));

            // paste / drop of image files → media library
            doc.addEventListener('paste', (e) => {
                const files = Array.from((e.clipboardData && e.clipboardData.files) || []);
                if (files.some((f) => /^image\//.test(f.type))) {
                    e.preventDefault(); e.stopImmediatePropagation();
                    uploadImageFiles(ed, files);
                }
            }, true);
            doc.addEventListener('drop', (e) => {
                const files = Array.from((e.dataTransfer && e.dataTransfer.files) || []);
                if (files.some((f) => /^image\//.test(f.type))) {
                    e.preventDefault(); e.stopImmediatePropagation();
                    uploadImageFiles(ed, files);
                }
            }, true);
            doc.addEventListener('dragover', (e) => { if (e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files')) e.preventDefault(); }, true);

            (waiters.get(key) || []).forEach((r) => r(ed));
            ta.dispatchEvent(new CustomEvent('aron-editor:ready', { bubbles: true }));
        });

        // مقدار textarea قبل از ارسال فرم همگام شود
        const form = ta.closest('form');
        if (form) form.addEventListener('submit', () => ed.updateElement(), true);
    }).catch(() => {
        ta.classList.add('!border-0');
        if (statEl) statEl.textContent = 'ویرایشگر بارگذاری نشد؛ از ویرایشگر ساده استفاده کنید (HTML).';
    });
}

const find = (k) => instances.get(k) || (document.getElementById(k) && instances.get(document.getElementById(k).name)) || null;

window.AronEditor = {
    get: find,
    ready(k) {
        const e = find(k);
        if (e && e.status === 'ready') return Promise.resolve(e);
        return new Promise((r) => { (waiters.get(k) || waiters.set(k, []).get(k)).push(r); });
    },
    setData(k, html) {
        const e = find(k);
        if (e) { e.setData(html); return; }
        const ta = document.getElementById(k) || document.querySelector('[name="' + k + '"]');
        if (ta) ta.value = html;
    },
    getData(k) {
        const e = find(k);
        if (e) return e.getData();
        const ta = document.getElementById(k) || document.querySelector('[name="' + k + '"]');
        return ta ? ta.value : '';
    },
    embedUrl,
};

const init = () => $$('textarea[data-aron-editor]').forEach(setup);
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();
