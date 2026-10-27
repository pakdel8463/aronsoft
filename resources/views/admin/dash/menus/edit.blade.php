<x-admin.layout :title="'ویرایش منو: '.$menu->name" :breadcrumbs="[['label' => 'منوها', 'url' => route('panel.menus.index')], ['label' => 'ویرایش']]">
    @include('admin.dash.menus._form')
</x-admin.layout>
