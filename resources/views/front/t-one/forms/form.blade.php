{{--
    نمایش یک فرم فرم‌ساز در سایت. استفاده: شورت‌کد [form id="…"]، بلوک «فرم» در صفحه‌ساز، یا @include.
    ورودی: $form (App\Models\Form)، $variant = card|plain، $title (عنوان اختیاری)، $dark (روی پس‌زمینه‌ی تیره)
--}}
@php
    use App\Forms\FormSchema;
    $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v);
    $variant = $variant ?? 'card';
    $s = $form->allSettings();
    $id = $form->id;
    $bag = $errors->{'form_'.$id};
    $mine = (string) old('_form_id') === (string) $id;
    $old = fn ($key, $default = null) => $mine ? old('fields.'.$key, $default) : $default;
    $success = session('form_success_'.$id);
    $closed = session('form_closed_'.$id) ?? $form->closedReason();
    $needsLogin = $s['require_login'] && ! auth()->check();
    $steps = FormSchema::steps($form->fields ?? []);
    $stepCount = count($steps);
    $pageLabels = collect($form->fields ?? [])->where('type', 'page')->pluck('label')->values();
    $errorStep = 0;
    if ($bag->any()) {
        foreach ($steps as $i => $stepFields) {
            if (collect($stepFields)->contains(fn ($f) => $bag->has('fields.'.$f['key']) || $bag->has('fields.'.$f['key'].'.*'))) { $errorStep = $i; break; }
        }
    }
    $hideLabels = $s['label_placement'] === 'hidden';
    $width = ['full' => 'fm-full', 'half' => 'fm-half', 'third' => 'fm-third'];
@endphp
<div id="form-{{ $id }}" class="fm {{ $variant === 'card' ? 'card p-5 sm:p-8' : '' }} {{ $s['css_class'] }}" @if ($bag->any() || $success) data-scroll-here @endif>
    @if (! empty($title))<h3 class="mb-5 text-xl font-extrabold text-slate-900">{{ $title }}</h3>@endif

    @if ($success)
        <div class="form-msg is-ok rich flex flex-col gap-2 [&_p]:m-0" role="status">
            <i class="ph-fill ph-check-circle text-2xl text-emerald-600" aria-hidden="true"></i>
            {!! $success !!}
        </div>
    @endif

    @if ($closed && ! $success)
        <div class="form-msg flex items-start gap-3 border-amber-200 bg-amber-50 text-amber-900"><i class="ph ph-info text-xl" aria-hidden="true"></i>{{ $closed }}</div>
    @elseif ($needsLogin)
        <div class="form-msg flex flex-wrap items-center gap-3 border-brand-200 bg-brand-50 text-slate-800">
            <i class="ph ph-lock text-xl text-tech-blue" aria-hidden="true"></i> برای تکمیل این فرم ابتدا وارد حساب کاربری شوید.
            <a href="{{ route('login') }}" class="btn btn-dark btn-sm">ورود / ثبت‌نام</a>
        </div>
    @elseif (! ($success && $s['hide_after_submit']))
        <form method="POST" action="{{ route('forms.submit', $form) }}" enctype="multipart/form-data" novalidate data-fm-form
              data-steps="{{ $stepCount }}" data-start="{{ $errorStep }}" class="{{ $success ? 'mt-5' : '' }}">
            @csrf
            <input type="hidden" name="_form_id" value="{{ $id }}">
            <input type="hidden" name="_source" value="{{ url()->current() }}">
            <div class="hp-field" aria-hidden="true"><label>نام کامل<input type="text" name="_hp" tabindex="-1" autocomplete="off"></label></div>

            @if ($bag->any())
                <div class="form-msg is-err mb-5 flex items-start gap-2" role="alert"><i class="ph ph-warning-circle text-lg" aria-hidden="true"></i> لطفاً موارد مشخص‌شده را اصلاح کنید.
                    @if ($bag->has('captcha'))<span class="block">{{ $bag->first('captcha') }}</span>@endif</div>
            @endif

            @if ($stepCount > 1 && $s['progress'] !== 'none')
                <div class="mb-6" data-progress>
                    @if ($s['progress'] === 'bar')
                        <div class="mb-2 text-xs font-bold text-slate-500"><span data-progress-label>مرحله‌ی ۱ از {{ $fa($stepCount) }}</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div data-progress-bar class="h-full rounded-full bg-tech-blue transition-all duration-500" style="width: {{ 100 / $stepCount }}%"></div></div>
                    @else
                        <ol class="fm-steps">
                            @for ($i = 0; $i < $stepCount; $i++)
                                <li data-progress-step="{{ $i }}">
                                    <span class="fm-step-num">{{ $fa($i + 1) }}</span>
                                    <span class="fm-step-label">{{ $i === 0 ? 'شروع' : ($pageLabels[$i - 1] ?? 'مرحله‌ی '.$fa($i + 1)) }}</span>
                                    @if ($i < $stepCount - 1)<span class="fm-step-line" aria-hidden="true"></span>@endif
                                </li>
                            @endfor
                        </ol>
                    @endif
                </div>
            @endif

            @foreach ($steps as $si => $stepFields)
                <fieldset data-step="{{ $si }}" class="fm-grid" @if ($si !== $errorStep) hidden @endif>
                    <legend class="sr-only">مرحله‌ی {{ $fa($si + 1) }}</legend>
                    @foreach ($stepFields as $f)
                        @php
                            $key = $f['key']; $name = 'fields['.$key.']'; $fid = 'f'.$id.'-'.$key;
                            $err = $bag->first('fields.'.$key) ?: $bag->first('fields.'.$key.'.*');
                            $logic = ! empty($f['logic']['enabled']) ? json_encode($f['logic'], JSON_UNESCAPED_UNICODE) : null;
                            $req = $f['required'];
                            $ph = $hideLabels ? ($f['placeholder'] ?: $f['label']) : $f['placeholder'];
                            $describedBy = trim(($f['help'] ? $fid.'-help ' : '').($err ? $fid.'-err' : ''));
                            $labelHtml = e($f['label']).($req ? ' <span class="text-red-600" aria-hidden="true">*</span>' : '');
                        @endphp
                        <div data-field="{{ $key }}" data-type="{{ $f['type'] }}" @if ($req) data-required @endif @if ($logic) data-logic="{{ $logic }}" @endif
                             class="{{ $f['type'] === 'hidden' ? 'hidden' : $width[$f['width']] ?? 'fm-full' }} {{ $f['css_class'] }}">
                            @switch($f['type'])
                                @case('section')
                                    <div class="mt-2 border-b border-slate-200 pb-2"><h4 class="text-lg font-extrabold text-slate-900">{{ $f['label'] }}</h4>@if ($f['content'])<div class="rich mt-1 text-sm">{!! $f['content'] !!}</div>@endif</div>
                                    @break
                                @case('html')
                                    <div class="rich">{!! $f['content'] !!}</div>
                                    @break
                                @case('hidden')
                                    <input type="hidden" name="{{ $name }}" value="{{ $old($key, $f['default']) }}">
                                    @break
                                @case('textarea')
                                    <label for="{{ $fid }}" @class(['form-label', 'sr-only' => $hideLabels])>{!! $labelHtml !!}</label>
                                    <textarea id="{{ $fid }}" name="{{ $name }}" rows="{{ $f['rows'] ?? 4 }}" placeholder="{{ $ph }}" @if ($req) required @endif @if ($f['max']) maxlength="{{ $f['max'] }}" @endif @if ($err) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif class="form-input">{{ $old($key, $f['default']) }}</textarea>
                                    @break
                                @case('select')
                                    <label for="{{ $fid }}" @class(['form-label', 'sr-only' => $hideLabels])>{!! $labelHtml !!}</label>
                                    <select id="{{ $fid }}" name="{{ $name }}" @if ($req) required @endif @if ($err) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif class="form-input select-rtl-fix">
                                        <option value="">{{ $f['placeholder'] ?: 'انتخاب کنید' }}</option>
                                        @foreach ($f['options'] as $o)<option value="{{ $o['value'] }}" @selected((string) $old($key, $f['default']) === (string) $o['value'])>{{ $o['label'] }}</option>@endforeach
                                    </select>
                                    @break
                                @case('radio')
                                @case('checkbox')
                                    @php $cur = (array) $old($key, $f['type'] === 'checkbox' ? array_filter(array_map('trim', explode(',', $f['default']))) : $f['default']); @endphp
                                    <span id="{{ $fid }}-label" class="form-label">{!! $labelHtml !!}</span>
                                    <div role="{{ $f['type'] === 'radio' ? 'radiogroup' : 'group' }}" aria-labelledby="{{ $fid }}-label" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif class="flex flex-wrap gap-2">
                                        @foreach ($f['options'] as $o)
                                            <label class="fm-choice">
                                                <input type="{{ $f['type'] }}" name="{{ $name }}{{ $f['type'] === 'checkbox' ? '[]' : '' }}" value="{{ $o['value'] }}" @checked(in_array((string) $o['value'], array_map('strval', $cur), true))
                                                       @if ($req && $f['type'] === 'radio') required @endif>
                                                {{ $o['label'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @break
                                @case('consent')
                                    <label class="fm-choice fm-consent">
                                        <input type="checkbox" name="{{ $name }}" value="1" @checked($old($key)) @if ($req) required @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
                                        <span class="rich text-sm [&_p]:m-0">{!! $f['content'] !!}@if ($req)<span class="text-red-600"> *</span>@endif</span>
                                    </label>
                                    @break
                                @case('date')
                                    @php
                                        $dv = $old($key); $parts = is_array($dv) ? $dv : (preg_match('#^(\d{4})/(\d{1,2})/(\d{1,2})$#', (string) ($dv ?: $f['default']), $m) ? ['y' => $m[1], 'm' => $m[2], 'd' => $m[3]] : []);
                                        $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
                                    @endphp
                                    <span id="{{ $fid }}-label" class="form-label">{!! $labelHtml !!}</span>
                                    <div class="fm-date" role="group" aria-labelledby="{{ $fid }}-label">
                                        <select name="{{ $name }}[d]" aria-label="روز" @if ($req) required @endif class="form-input select-rtl-fix"><option value="">روز</option>@for ($d = 1; $d <= 31; $d++)<option value="{{ $d }}" @selected((int) ($parts['d'] ?? 0) === $d)>{{ $fa($d) }}</option>@endfor</select>
                                        <select name="{{ $name }}[m]" aria-label="ماه" @if ($req) required @endif class="form-input select-rtl-fix"><option value="">ماه</option>@foreach ($months as $mi => $mn)<option value="{{ $mi + 1 }}" @selected((int) ($parts['m'] ?? 0) === $mi + 1)>{{ $mn }}</option>@endforeach</select>
                                        <select name="{{ $name }}[y]" aria-label="سال" @if ($req) required @endif class="form-input select-rtl-fix"><option value="">سال</option>@for ($y = (int) $f['max']; $y >= (int) $f['min']; $y--)<option value="{{ $y }}" @selected((int) ($parts['y'] ?? 0) === $y)>{{ $fa($y) }}</option>@endfor</select>
                                    </div>
                                    @break
                                @case('file')
                                    <span id="{{ $fid }}-label" class="form-label">{!! $labelHtml !!}</span>
                                    <label class="fm-file">
                                        <i class="ph ph-cloud-arrow-up text-2xl text-tech-blue" aria-hidden="true"></i>
                                        <span data-file-name>فایل را انتخاب کنید</span>
                                        <span class="text-xs text-slate-400">{{ str_replace(',', '، ', $f['accept']) }} — حداکثر {{ $fa(round($f['max_kb'] / 1024, 1)) }} مگابایت</span>
                                        <input type="file" name="{{ $name }}" accept=".{{ str_replace(',', ',.', $f['accept']) }}" @if ($req) required @endif aria-labelledby="{{ $fid }}-label" class="sr-only">
                                    </label>
                                    @break
                                @default
                                    @php
                                        [$type, $mode, $dir] = match ($f['type']) {
                                            'email' => ['email', 'email', 'ltr'], 'url' => ['url', 'url', 'ltr'],
                                            'mobile', 'tel' => ['tel', 'tel', 'ltr'], 'number', 'national_code' => ['text', 'numeric', 'ltr'],
                                            default => ['text', 'text', 'auto'],
                                        };
                                    @endphp
                                    <label for="{{ $fid }}" @class(['form-label', 'sr-only' => $hideLabels])>{!! $labelHtml !!}</label>
                                    <input id="{{ $fid }}" type="{{ $type }}" inputmode="{{ $mode }}" dir="{{ $dir }}" name="{{ $name }}" value="{{ $old($key, $f['default']) }}" placeholder="{{ $ph }}"
                                           @if ($req) required @endif @if ($f['type'] === 'national_code') maxlength="10" @elseif ($f['type'] === 'mobile') maxlength="11" @elseif ($f['max'] && $f['type'] === 'text') maxlength="{{ $f['max'] }}" @endif
                                           @if ($err) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                                           autocomplete="{{ ['email' => 'email', 'mobile' => 'tel', 'tel' => 'tel'][$f['type']] ?? (str_contains($key, 'name') ? 'name' : 'off') }}"
                                           class="form-input {{ $dir === 'ltr' ? 'text-right' : '' }}">
                            @endswitch
                            @if ($f['help'] && ! in_array($f['type'], ['section', 'html', 'hidden'], true))<p id="{{ $fid }}-help" class="form-help">{{ $f['help'] }}</p>@endif
                            @if ($err)<p id="{{ $fid }}-err" class="form-error">{{ $err }}</p>@endif
                        </div>
                    @endforeach

                    <div class="fm-full mt-1 flex flex-wrap items-center gap-3">
                        @if ($si === $stepCount - 1 && $s['captcha'])
                            <x-captcha class="basis-full" />
                        @endif
                        @if ($si > 0)
                            <button type="button" data-prev class="btn btn-secondary btn-lg"><i class="ph ph-arrow-right" aria-hidden="true"></i> {{ $s['prev_label'] }}</button>
                        @endif
                        @if ($si < $stepCount - 1)
                            <button type="button" data-next class="btn btn-dark btn-lg flex-1 sm:flex-none">{{ $s['next_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></button>
                        @else
                            <button type="submit" class="btn btn-primary btn-lg flex-1 sm:flex-none"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i> {{ $s['submit_label'] }}</button>
                        @endif
                    </div>
                </fieldset>
            @endforeach
        </form>
    @endif
</div>
@once
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ @filemtime(public_path('css/forms.css')) ?: 1 }}">
    <script src="{{ asset('js/forms.js') }}?v={{ @filemtime(public_path('js/forms.js')) ?: 1 }}" defer></script>
@endonce
