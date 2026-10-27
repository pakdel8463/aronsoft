<x-app-layout>
    <x-slot name="header">داشبورد</x-slot>
    <p class="lead mb-8">سلام {{ auth()->user()->name }}، به حساب کاربری خود در آرون سافت خوش آمدید.</p>
    <ul class="grid gap-5 sm:grid-cols-2">
        <li><a href="{{ route('order.create') }}" class="card card-hover flex h-full gap-4 p-6"><span class="icon-tile"><i class="ph ph-rocket-launch" aria-hidden="true"></i></span><span><span class="block font-bold text-slate-900">ثبت سفارش پروژه</span><span class="mt-1 block text-sm leading-7 text-slate-600">نیازتان را ثبت کنید تا پیش‌فاکتور و زمان‌بندی اولیه ارسال شود.</span></span></a></li>
        <li><a href="{{ route('profile.edit') }}" class="card card-hover flex h-full gap-4 p-6"><span class="icon-tile"><i class="ph ph-user-gear" aria-hidden="true"></i></span><span><span class="block font-bold text-slate-900">ویرایش پروفایل</span><span class="mt-1 block text-sm leading-7 text-slate-600">نام، ایمیل و رمز عبور حساب خود را مدیریت کنید.</span></span></a></li>
        <li><a href="{{ route('blog.index') }}" class="card card-hover flex h-full gap-4 p-6"><span class="icon-tile"><i class="ph ph-article" aria-hidden="true"></i></span><span><span class="block font-bold text-slate-900">وبلاگ</span><span class="mt-1 block text-sm leading-7 text-slate-600">آخرین مقالات تخصصی تیم آرون سافت.</span></span></a></li>
        @php $__tu = \App\Models\Ticket::unreadCountFor(auth()->user()); @endphp
        <li><a href="{{ route('tickets.index') }}" class="card card-hover flex h-full gap-4 p-6"><span class="icon-tile"><i class="ph ph-chats-circle" aria-hidden="true"></i></span><span><span class="block font-bold text-slate-900">تیکت‌های پشتیبانی @if($__tu)<span class="chip chip-blue ms-1">{{ \App\Helpers\Fa::toFa($__tu) }} پاسخ جدید</span>@endif</span><span class="mt-1 block text-sm leading-7 text-slate-600">درخواست پشتیبانی ثبت کنید و گفتگو را پیگیری کنید.</span></span></a></li>
        <li><a href="{{ route('contact') }}" class="card card-hover flex h-full gap-4 p-6"><span class="icon-tile"><i class="ph ph-headset" aria-hidden="true"></i></span><span><span class="block font-bold text-slate-900">تماس با ما</span><span class="mt-1 block text-sm leading-7 text-slate-600">سؤال یا درخواستی دارید؟ با ما تماس بگیرید.</span></span></a></li>
    </ul>
</x-app-layout>
