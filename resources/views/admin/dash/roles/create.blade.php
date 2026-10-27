<x-admin.layout title="نقش جدید" :breadcrumbs="[['label' => 'نقش‌ها', 'url' => route('panel.roles.index')], ['label' => 'جدید']]">
    @include('admin.dash.roles._form')
</x-admin.layout>
