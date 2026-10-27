<x-admin.layout :title="'ویرایش '.$user->name" subtitle="مشخصات، نقش‌ها و دسترسی‌ها" :breadcrumbs="[['label' => 'کاربران', 'url' => route('panel.users.index')], ['label' => $user->name, 'url' => route('panel.users.show', $user)], ['label' => 'ویرایش']]">
    @include('admin.dash.users._form')
    <x-slot:scripts>@include('admin.dash.users._avatar-js')</x-slot:scripts>
</x-admin.layout>
