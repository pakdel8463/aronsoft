@component('front.t-one.layouts.content')
    @include('front.t-one.tickets.partials.head', ['heading' => 'ثبت تیکت جدید', 'sub' => 'مشکل یا درخواست خود را شرح دهید؛ پاسخ را در همین حساب و از طریق ایمیل دریافت می‌کنید.'])
    <div class="section !py-8 md:!py-10">
        <div class="container-x max-w-3xl">
            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="card space-y-5 p-5 sm:p-8" data-ticket-form>
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="form-label" for="t-dept">دپارتمان <span class="text-red-500">*</span></label>
                        <select id="t-dept" name="department_id" class="form-input select-rtl-fix" required>
                            <option value="">انتخاب کنید…</option>
                            @foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>@endforeach
                        </select>
                        @error('department_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="t-pri">اولویت</label>
                        <select id="t-pri" name="priority" class="form-input select-rtl-fix">
                            @foreach(['low' => 'کم', 'normal' => 'معمولی', 'high' => 'مهم'] as $k => $l)<option value="{{ $k }}" @selected(old('priority', 'normal') === $k)>{{ $l }}</option>@endforeach
                        </select>
                        @error('priority')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="form-label" for="t-subject">موضوع <span class="text-red-500">*</span></label>
                    <input class="form-input" id="t-subject" name="subject" value="{{ old('subject') }}" maxlength="190" required placeholder="خلاصه‌ای از درخواست شما">
                    @error('subject')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                @if($orders->isNotEmpty())
                    <div>
                        <label class="form-label" for="t-order">سفارش مرتبط <span class="font-normal text-slate-500">(اختیاری)</span></label>
                        <select id="t-order" name="order_code" class="form-input select-rtl-fix">
                            <option value="">—</option>
                            @foreach($orders as $o)<option value="{{ $o->code }}" @selected(old('order_code') === $o->code)>{{ $o->code }}</option>@endforeach
                        </select>
                        @error('order_code')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div>
                    <label class="form-label" for="t-body">متن پیام <span class="text-red-500">*</span></label>
                    <textarea class="form-input" id="t-body" name="body" rows="8" minlength="10" maxlength="10000" required placeholder="توضیح کامل مشکل، آدرس صفحه، مراحل تکرار…">{{ old('body') }}</textarea>
                    @error('body')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                @include('front.t-one.tickets.partials.uploader')
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">انصراف</a>
                    <button type="submit" class="btn btn-primary" data-once><i class="ph ph-paper-plane-right" aria-hidden="true"></i> ارسال تیکت</button>
                </div>
            </form>
        </div>
    </div>
    @slot('bottomscript')
        <script>document.querySelectorAll('[data-ticket-form]').forEach(function (f) { f.addEventListener('submit', function () { var b = f.querySelector('[data-once]'); if (b) setTimeout(function () { b.disabled = true; }, 0); }); });</script>
    @endslot
@endcomponent
