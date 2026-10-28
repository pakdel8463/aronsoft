@component('front.t-one.layouts.content', ['title' => 'پیش‌نمایش فرم: '.$form->title])
    <section class="section">
        <div class="container-x max-w-3xl">
            <p class="mb-4 rounded-xl bg-slate-900 px-4 py-2.5 text-sm text-white"><i class="ph ph-eye align-middle text-blue-300"></i> پیش‌نمایش فرم «{{ $form->title }}» — ارسال این فرم یک ورودی واقعی ثبت می‌کند.</p>
            @include('front.t-one.forms.form', ['form' => $form])
        </div>
    </section>
@endcomponent
