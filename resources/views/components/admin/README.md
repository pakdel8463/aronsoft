# Admin UI kit (`/panel`) — Tailwind v3.4, vanilla JS (no Alpine), RTL, Phosphor icons

Everything lives in `resources/views/components/admin/`. Use as `<x-admin.NAME>`. **Names/props below are stable.**
Icons = Phosphor name without prefix (`icon="trash"` → `ph ph-trash`; `ph-fill ph-x` for filled). Tokens: `aron-*` (600 = #2F4FF5, the brand blue; NOT `brand-*`, that is the public site palette), `coral-*`, `mint-*`, `canvas`, `line`, `ink`; shadows `shadow-adm`, `shadow-adm-lift`, `shadow-adm-brand`, `shadow-adm-pop`; radius `rounded-[var(--radius-card)]`.
Raw-HTML helper classes (from `resources/css/admin.css`): `a-card`, `a-btn a-btn-{primary|secondary|soft|danger|danger-soft|success|ghost}` (+`a-btn-sm|lg|icon`), `a-input`, `a-select`, `a-textarea`, `a-label`, `a-check`, `a-switch`, `a-table`.
Use `border-line` for borders. After adding a NEW icon name, run `python3 scripts/subset-phosphor-admin.py` (icons are a local subset, see Build).

## Migrating a page (old DashLite → new shell)
Old:
```blade
@component('admin.dash.layouts.content') ... @endcomponent      {{-- or @extends('admin.dash.master') --}}
```
New:
```blade
<x-admin.layout title="نوشته‌ها" subtitle="مدیریت مطالب وبلاگ" :breadcrumbs="[['label'=>'نوشته‌ها']]">
    <x-slot:actions><x-admin.button :href="route('panel.posts.create')" icon="plus">نوشته جدید</x-admin.button></x-slot:actions>
    ...content (cards, tables, forms)...
    <x-slot:scripts> <script>…page JS…</script> </x-slot:scripts>
</x-admin.layout>
```
Then replace Bootstrap/DashLite markup (`card`, `btn`, `form-control`, `row/col`, `nk-*`, `em.icon.ni`) with the components below. Flash messages (`session('success'|'error'|'status'|'warning'|'info')`) and `$errors` are shown automatically as toasts — remove per-page alert markup. jQuery, Bootstrap and Alpine are NOT loaded: page scripts must be vanilla JS (`<x-slot:scripts>`). The old `admin.dash.master` still works for not-yet-migrated pages.

## `<x-admin.layout>`
Props: `title` (tab title + H1), `subtitle`, `breadcrumbs` (`[['label'=>..,'url'=>..]]`, last without url), `header` (bool, default true; `:header="false"` to skip auto page header), `width` (container class, default `max-w-[1600px]`; use `max-w-4xl` for forms).
Slots: default, `actions` (header buttons), `head` (extra `<head>` tags), `scripts` (end of body).
Sidebar is defined in `resources/views/admin/shell/sidebar.blade.php` (add rows there; items are skipped when `Route::has()` is false; optional permission list per row). Quick-jump search (`/` or Ctrl+K) filters sidebar links.
JS helpers: `adminModal.open('name')`/`.close('name')`, `adminToast('پیام', 'success|error|warning|info')`, `adminFetch(url, opts)` (adds CSRF + XHR headers), . Forms with `data-confirm="متن"` ask for confirmation; buttons with `data-loading-on-submit` are disabled after submit.

## Components
| Component | Props / slots |
|---|---|
| `card` | `title`, `subtitle`, `icon`, `padding` (default true; `:padding="false"` for tables/lists). Slots: default, `actions`, `footer`. |
| `page-header` | `title`, `subtitle`, `breadcrumbs`. Slots: default, `actions`. (Layout renders it for you.) |
| `button` | `variant` primary\|secondary\|soft\|danger\|danger-soft\|success\|ghost, `size` sm\|md\|lg, `icon`, `icon-only`, `href` (→ `<a>`), `type` (button\|submit), `loading`. |
| `field` | `label`, `name` (error lookup), `hint`, `required`, `for`. Wraps any control; shows validation error automatically. |
| `input` | `name`, `type`, `value`, `label`, `hint`, `required`, `id`, `icon`, `ltr`. Uses `old()` automatically; with `label`/`hint` it wraps itself in `field`. Extra attributes pass through (`placeholder`, `x-model`…). |
| `textarea` | `name`, `value`, `label`, `hint`, `required`, `id`, `rows`. |
| `select` | `name`, `options` (`[value=>label]`), `selected`, `label`, `hint`, `required`, `placeholder`; or `<option>` tags in the slot. `old()` aware; `multiple` works with `name="x[]"`. |
| `checkbox` | `name`, `label`, `hint`, `value` ('1'), `checked`, `switch`, `unchecked` (hidden fallback value), `type` checkbox\|radio. |
| `toggle` | = checkbox as a switch; posts `1`/`0`. `name`, `label`, `hint`, `checked`, `value`, `unchecked`. |
| `table` | `hover` (true), `flush`. Slots: default (`<tr>` rows), `head` (put `<x-admin.th>` cells). Helpers `th`, `td` (`muted`, `nowrap`). |
| `badge` | `color` brand\|mint\|coral\|amber\|sky\|violet\|slate, `dot`, `icon`. |
| `empty-state` | `icon`, `title`, `description`; default slot = action buttons. |
| `modal` | `name`, `title`, `max-width` sm..2xl, `show`. Open: `<button data-modal-open="name">` (or `adminModal.open('name')`); close: any `[data-modal-close]` inside, backdrop, Esc, or `adminModal.close('name')`. Slots: default, `footer`. |
| `confirm-delete` | `action`, `method` (DELETE), `message`, `label`, `icon`, `icon-only`, `variant`, `size`. Renders a form with CSRF + `@method` + confirm. |
| `stat-card` | `label`, `value`, `icon`, `color`, `hint`, `delta`, `delta-type` up\|down\|neutral, `href`. |
| `tabs` + `tab` | `tabs` (`['key'=>'Label']` or `['key'=>['label'=>..,'icon'=>..]]`), `default`; panels `<x-admin.tab name="key">` (all stay in the DOM, so one form can span tabs; URL hash remembers the tab; a tab containing a validation error opens first). |
| `pagination` | `paginator`. Same as `{{ $items->links('vendor.pagination.admin') }}`. |
| `dropdown` + `dropdown-item` | `dropdown`: `align` end\|start, `width`; slots `trigger`, default. `dropdown-item`: `href` (else button), `icon`, `danger`, `type`. |

## Examples
```blade
<x-admin.card title="اطلاعات پایه" icon="note-pencil">
    <form method="POST" action="{{ route('panel.x.store') }}" class="grid gap-4 sm:grid-cols-2">
        @csrf
        <x-admin.input name="title" label="عنوان" required :value="$post->title ?? ''" />
        <x-admin.select name="status" label="وضعیت" :options="['draft'=>'پیش‌نویس','published'=>'منتشرشده']" :selected="$post->status ?? 'draft'" />
        <x-admin.textarea name="excerpt" label="خلاصه" class="sm:col-span-2" />   {{-- class goes on the control --}}
        <x-admin.toggle name="is_active" label="فعال" :checked="$post->is_active ?? true" />
        <div class="sm:col-span-2"><x-admin.button type="submit" icon="floppy-disk" loading>ذخیره</x-admin.button></div>
    </form>
</x-admin.card>

<x-admin.card :padding="false">
    <x-admin.table flush>
        <x-slot:head><x-admin.th>عنوان</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
        @forelse ($posts as $p)
            <tr><x-admin.td>{{ $p->title }}</x-admin.td>
                <x-admin.td><x-admin.badge :color="$p->status==='published' ? 'mint' : 'slate'" dot>{{ $p->status }}</x-admin.badge></x-admin.td>
                <x-admin.td class="text-end"><x-admin.confirm-delete :action="route('panel.posts.destroy',$p)" icon-only /></x-admin.td></tr>
        @empty
            <tr><td colspan="3"><x-admin.empty-state title="نوشته‌ای نیست" /></td></tr>
        @endforelse
    </x-admin.table>
    <x-slot:footer><x-admin.pagination :paginator="$posts" class="!mt-0" /></x-slot:footer>
</x-admin.card>
```

## Build
- Real machine: `npm install && npm run build` (single Vite config, Tailwind v3; admin entries `resources/css/admin.css` + `resources/js/admin.js` are in `vite.config.js`). Layout reads `public/build/manifest.json`.
- Sandbox without npm: `/home/claude/aron-tools/build-admin.sh` (standalone tailwind + esbuild; writes `public/build/assets/admin-*.{css,js}` and merges the manifest; leaves `front-*` alone).
- Icons: `python3 scripts/subset-phosphor-admin.py` regenerates `public/vendor/phosphor/phosphor-admin.css` from every icon name found in admin views/components/js.
- Mirror `public/build` and `public/vendor` to `public_html/` afterwards (the scripts do it when `public_html/` exists).
