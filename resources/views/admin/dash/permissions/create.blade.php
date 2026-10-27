<x-admin.layout title="دسترسی جدید" :breadcrumbs="[['label' => 'دسترسی‌ها', 'url' => route('panel.permissions.index')], ['label' => 'جدید']]">
    @include('admin.dash.permissions._form')
</x-admin.layout>
