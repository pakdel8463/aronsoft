<x-admin.layout title="تنظیمات تماس" subtitle="اطلاعات تماس شرکت در صفحه تماس، فوتر، ایمیل‌ها و داده‌های ساختاریافته گوگل نمایش داده می‌شود" :breadcrumbs="[['label'=>'تنظیمات تماس']]" width="max-w-5xl">
    @if ($isCustom)
        <x-slot:actions>
            <form method="POST" action="{{ route('panel.settings.contact.reset') }}" data-confirm="همه‌ی اطلاعات تماس به مقادیر پیش‌فرض بازگردانده می‌شود. ادامه می‌دهید؟" class="inline-flex">
                @csrf
                @method('DELETE')
                <x-admin.button type="submit" variant="danger-soft" icon="arrow-counter-clockwise">بازنشانی به پیش‌فرض</x-admin.button>
            </form>
        </x-slot:actions>
    @endif

    @php($m = $info['map'] ?? [])

    <form method="POST" action="{{ route('panel.settings.contact.update') }}" data-contact-form>
        @csrf
        @method('PUT')

        <x-admin.tabs :tabs="[
            'main' => ['label' => 'اطلاعات اصلی', 'icon' => 'phone'],
            'social' => ['label' => 'شبکه‌های اجتماعی', 'icon' => 'share-network'],
            'address' => ['label' => 'آدرس و ساعات کاری', 'icon' => 'map-pin'],
            'map' => ['label' => 'نقشه', 'icon' => 'map-trifold'],
        ]">
            <x-admin.tab name="main">
                <div class="grid gap-5 lg:grid-cols-3">
                    <x-admin.card title="اطلاعات اصلی" icon="phone" class="lg:col-span-2">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-admin.input name="phone" id="c_phone" label="شماره تلفن (برای لینک تماس)" hint="فقط ارقام و خط تیره؛ مثال: 021-22451912" required :value="$info['phone'] ?? ''" :ltr="true" inputmode="tel" />
                            <x-admin.input name="phone_display" id="c_phone_display" label="شماره تلفن (نمایش در سایت)" hint="همان‌طور که تایپ شود نمایش داده می‌شود؛ مثال: ۰۲۱-۲۲۴۵۱۹۱۲" required :value="$info['phone_display'] ?? ''" />
                            <x-admin.input name="email" id="c_email" type="email" label="ایمیل" required :value="$info['email'] ?? ''" />
                            <x-admin.input name="mobile" id="c_mobile" label="موبایل (اختیاری)" hint="مثال: 09121234567" :value="$info['mobile'] ?? ''" :ltr="true" inputmode="tel" />
                            <x-admin.input name="whatsapp" id="c_whatsapp" label="واتس‌اپ (اختیاری)" hint="شماره‌ی ایرانی؛ لینک wa.me ساخته می‌شود" :value="$info['whatsapp'] ?? ''" :ltr="true" inputmode="tel" />
                        </div>
                    </x-admin.card>

                    <x-admin.card title="پیش‌نمایش" subtitle="نمونه‌ی نمایش در فوتر سایت" icon="eye">
                        <ul class="space-y-3 text-sm text-slate-600" data-contact-preview>
                            <li class="flex items-start gap-2"><i class="ph ph-map-pin mt-0.5 text-lg text-slate-400"></i><span data-pv="address" class="leading-7"></span></li>
                            <li class="flex items-center gap-2"><i class="ph ph-phone text-lg text-slate-400"></i><span dir="ltr" data-pv="phone_display" class="font-bold"></span></li>
                            <li class="text-xs text-slate-400" dir="ltr" data-pv="tel"></li>
                            <li class="flex items-center gap-2"><i class="ph ph-envelope text-lg text-slate-400"></i><span dir="ltr" data-pv="email"></span></li>
                        </ul>
                    </x-admin.card>
                </div>
            </x-admin.tab>

            <x-admin.tab name="social">
                <x-admin.card title="شبکه‌های اجتماعی" subtitle="فیلد خالی = آیکن آن در سایت نمایش داده نمی‌شود" icon="share-network">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="instagram" label="اینستاگرام (نام کاربری)" hint="بدون @؛ مثال: aron.soft" :value="$info['instagram'] ?? ''" :ltr="true" icon="instagram-logo" />
                        <x-admin.input name="telegram" label="تلگرام (نام کاربری)" hint="بدون @؛ مثال: aronsoft" :value="$info['telegram'] ?? ''" :ltr="true" icon="telegram-logo" />
                        <x-admin.input name="linkedin" type="url" label="لینکدین (آدرس کامل)" :value="$info['linkedin'] ?? ''" placeholder="https://www.linkedin.com/company/..." icon="linkedin-logo" />
                        <x-admin.input name="github" type="url" label="گیت‌هاب (آدرس کامل)" :value="$info['github'] ?? ''" placeholder="https://github.com/..." icon="github-logo" />
                        <x-admin.input name="x" type="url" label="ایکس / توییتر (آدرس کامل)" :value="$info['x'] ?? ''" placeholder="https://x.com/..." icon="x-logo" />
                    </div>
                </x-admin.card>
            </x-admin.tab>

            <x-admin.tab name="address">
                <x-admin.card title="آدرس و ساعات کاری" icon="map-pin">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><x-admin.textarea name="address" id="c_address" label="آدرس کامل" hint="در صفحه تماس، فوتر و ایمیل‌ها نمایش داده می‌شود" required rows="2" :value="$info['address'] ?? ''" /></div>
                        <x-admin.input name="address_street" label="خیابان و پلاک (برای گوگل)" hint="بدون نام شهر؛ در داده‌ی ساختاریافته (streetAddress) استفاده می‌شود" :value="$info['address_street'] ?? ''" />
                        <x-admin.input name="city" label="شهر" :value="$info['city'] ?? ''" />
                        <x-admin.input name="postal_code" label="کدپستی (اختیاری)" :value="$info['postal_code'] ?? ''" :ltr="true" inputmode="numeric" />
                        <div class="hidden sm:block"></div>
                        <x-admin.input name="hours" label="ساعات کاری" hint="مثال: شنبه تا چهارشنبه، ۰۹:۰۰ تا ۱۷:۰۰" :value="$info['hours'] ?? ''" />
                        <x-admin.input name="hours_note" label="توضیح ساعات کاری" hint="مثال: پشتیبانی پروژه‌های فعال: ۲۴ ساعته" :value="$info['hours_note'] ?? ''" />
                    </div>
                </x-admin.card>
            </x-admin.tab>

            <x-admin.tab name="map">
                <div class="grid gap-5 lg:grid-cols-3">
                    <x-admin.card title="مختصات" subtitle="مختصات دقیق دفتر را از OpenStreetMap یا گوگل‌مپ کپی کنید" icon="map-trifold">
                        <div class="grid gap-4">
                            <x-admin.input name="map[lat]" id="c_lat" label="عرض جغرافیایی (lat)" hint="بین ۹۰- تا ۹۰" required :value="$m['lat'] ?? ''" :ltr="true" inputmode="decimal" />
                            <x-admin.input name="map[lng]" id="c_lng" label="طول جغرافیایی (lng)" hint="بین ۱۸۰- تا ۱۸۰" required :value="$m['lng'] ?? ''" :ltr="true" inputmode="decimal" />
                            <x-admin.input name="map[zoom]" id="c_zoom" type="number" min="1" max="19" label="بزرگنمایی (۱ تا ۱۹)" required :value="$m['zoom'] ?? 15" />
                        </div>
                    </x-admin.card>
                    <x-admin.card title="پیش‌نمایش نقشه" icon="globe" class="lg:col-span-2">
                        <div class="h-80 overflow-hidden rounded-2xl border border-line bg-slate-100">
                            <iframe id="c_map_frame" title="پیش‌نمایش نقشه" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="h-full w-full border-0"></iframe>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">با تغییر مختصات، نقشه به‌صورت زنده به‌روز می‌شود.</p>
                    </x-admin.card>
                </div>
            </x-admin.tab>
        </x-admin.tabs>

        <div class="mt-5 flex justify-end">
            <x-admin.button type="submit" icon="floppy-disk" loading>ذخیره‌ی تنظیمات</x-admin.button>
        </div>
    </form>

    <x-slot:scripts>
        <script>
            (function () {
                var $ = function (id) { return document.getElementById(id); };

                // ---- پیش‌نمایش متنی (فوتر) ----
                function tel(v) {
                    var d = (v || '').replace(/[۰-۹]/g, function (c) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(c); }).replace(/\D/g, '');
                    if (!d) return '';
                    if (d.indexOf('0098') === 0) d = d.slice(4);
                    else if (d.indexOf('98') === 0 && d.length >= 11) d = d.slice(2);
                    return '+98' + d.replace(/^0+/, '');
                }
                function pv() {
                    var set = function (k, v) { var el = document.querySelector('[data-pv="' + k + '"]'); if (el) el.textContent = v; };
                    set('address', $('c_address').value);
                    set('phone_display', $('c_phone_display').value);
                    set('tel', tel($('c_phone').value) ? 'tel:' + tel($('c_phone').value) : '');
                    set('email', $('c_email').value);
                }
                ['c_address', 'c_phone_display', 'c_phone', 'c_email'].forEach(function (id) { $(id).addEventListener('input', pv); });
                pv();

                // ---- پیش‌نمایش زنده‌ی نقشه (OpenStreetMap) ----
                var frame = $('c_map_frame');
                function map() {
                    var lat = parseFloat($('c_lat').value), lng = parseFloat($('c_lng').value);
                    if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
                    var d = 0.006, x = d * 1.6;
                    var bbox = [lng - x, lat - d, lng + x, lat + d].map(function (n) { return n.toFixed(6); }).join(',');
                    var src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&layer=mapnik&marker=' + lat + '%2C' + lng;
                    if (frame.getAttribute('src') !== src) frame.setAttribute('src', src);
                }
                var t;
                ['c_lat', 'c_lng'].forEach(function (id) { $(id).addEventListener('input', function () { clearTimeout(t); t = setTimeout(map, 400); }); });
                map();
            })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
