<x-admin.layout title="نمونه‌کار جدید" subtitle="ثبت یک پروژه‌ی جدید در پورتفولیو" :breadcrumbs="[['label' => 'نمونه‌کارها', 'url' => route('panel.portfolios.index')], ['label' => 'جدید']]">
    @include('admin.dash.portfolio._form')
</x-admin.layout>
