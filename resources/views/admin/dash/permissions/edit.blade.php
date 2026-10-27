<x-admin.layout :title="'ویرایش دسترسی: '.$permission->name" :breadcrumbs="[['label' => 'دسترسی‌ها', 'url' => route('panel.permissions.index')], ['label' => 'ویرایش']]">
    @include('admin.dash.permissions._form')
</x-admin.layout>
