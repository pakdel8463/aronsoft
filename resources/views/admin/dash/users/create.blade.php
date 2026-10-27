<x-admin.layout title="کاربر جدید" subtitle="یک حساب تازه بسازید" :breadcrumbs="[['label' => 'کاربران', 'url' => route('panel.users.index')], ['label' => 'جدید']]">
    @include('admin.dash.users._form', ['user' => new \App\Models\User(), 'roles' => $roles, 'levels' => $levels])
    <x-slot:scripts>@include('admin.dash.users._avatar-js')</x-slot:scripts>
</x-admin.layout>
