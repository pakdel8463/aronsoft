<x-admin.layout title="محصول جدید" subtitle="افزودن قالب، افزونه، پکیج یا اسکریپت به فروشگاه" :breadcrumbs="[['label' => 'محصولات', 'url' => route('panel.products.index')], ['label' => 'جدید']]">
    @include('admin.dash.products._form')
</x-admin.layout>
