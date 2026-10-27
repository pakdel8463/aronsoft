<x-admin.layout title="پیش‌فاکتور جدید" subtitle="اقلام را وارد کنید؛ جمع کل خودکار محاسبه می‌شود" :breadcrumbs="[['label' => 'پیش‌فاکتورها', 'url' => route('panel.proformas.index')], ['label' => 'جدید']]">
    @include('admin.dash.proformas._form')
</x-admin.layout>
