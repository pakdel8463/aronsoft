<x-admin.layout title="منوی جدید" :breadcrumbs="[['label' => 'منوها', 'url' => route('panel.menus.index')], ['label' => 'جدید']]">
    @include('admin.dash.menus._form')
</x-admin.layout>
