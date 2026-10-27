<x-admin.layout :title="'ویرایش نقش: '.$role->name" :breadcrumbs="[['label' => 'نقش‌ها', 'url' => route('panel.roles.index')], ['label' => 'ویرایش']]">
    @include('admin.dash.roles._form')
</x-admin.layout>
