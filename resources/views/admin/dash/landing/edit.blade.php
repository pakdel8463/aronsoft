<x-admin.layout :title="'ویرایش: ' . $landing->title" :breadcrumbs="[['label' => 'صفحات لندینگ', 'url' => route('panel.landing.index')], ['label' => 'ویرایش']]">
    @include('admin.dash.landing._form')
</x-admin.layout>
