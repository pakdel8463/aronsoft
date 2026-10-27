<x-admin.layout :title="'ویرایش پیش‌فاکتور #'.($proforma->id + 1000)" :subtitle="$proforma->project_title" :breadcrumbs="[['label' => 'پیش‌فاکتورها', 'url' => route('panel.proformas.index')], ['label' => '#'.($proforma->id + 1000)]]">
    @include('admin.dash.proformas._form')
</x-admin.layout>
