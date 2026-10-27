<x-admin.layout title="محصولات نرم‌افزاری" subtitle="مدیریت سه لندینگ اختصاصی آرون سافت">
<div class="grid gap-4 md:grid-cols-3">@foreach($products as $product)
<a href="{{ route('panel.software-products.edit',$product) }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-400"><div class="mb-3 text-xs text-slate-500">{{ $product->published ? 'منتشر شده' : 'پیش‌نویس' }}</div><div class="text-xl font-bold">{{ $product->brand }}</div><p class="mt-2 text-sm text-slate-600">{{ $product->title }}</p><div class="mt-5 text-blue-600">ویرایش محتوا و تصاویر ←</div></a>
@endforeach</div>
</x-admin.layout>
