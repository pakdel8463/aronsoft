<x-admin.layout :title="'ویرایش: ' . $product->title" subtitle="بروزرسانی اطلاعات، قیمت و فایل محصول" :breadcrumbs="[['label' => 'محصولات', 'url' => route('panel.products.index')], ['label' => 'ویرایش']]">
    @include('admin.dash.products._form')
</x-admin.layout>
