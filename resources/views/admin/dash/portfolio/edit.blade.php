<x-admin.layout :title="'ویرایش: ' . $portfolio->title" subtitle="بروزرسانی اطلاعات، گالری و سئوی پروژه" :breadcrumbs="[['label' => 'نمونه‌کارها', 'url' => route('panel.portfolios.index')], ['label' => 'ویرایش']]">
    @include('admin.dash.portfolio._form')
</x-admin.layout>
