{{-- مسیرهای کتابخانه رسانه برای media.js (window.AronMediaRoutes). یک‌بار در هر صفحه چاپ می‌شود. --}}
@once
<script>
window.AronMediaRoutes = {
    list: @json(route('panel.media.list')),
    store: @json(route('panel.media.store')),
    show: @json(route('panel.media.show', '__ID__')),
    update: @json(route('panel.media.update', '__ID__')),
    replace: @json(route('panel.media.replace', '__ID__')),
    destroy: @json(route('panel.media.destroy', '__ID__')),
    bulkDestroy: @json(route('panel.media.bulkDestroy')),
};
</script>
@endonce
