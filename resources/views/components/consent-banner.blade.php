{{-- GTM و Consent Mode پیش‌فرض اکنون در <head> (front.t-one.partials.gtm) و فقط یک‌بار بارگذاری می‌شوند. --}}
{{-- تغییر: حذف inset-0 و تبدیل به حالت چسبیده به پایین (bottom-0) بدون لایه تاریک --}}
<div id="consent-overlay" role="dialog" aria-labelledby="consent-title" aria-live="polite" class="fixed bottom-0 left-0 right-0 z-[10000] hidden p-3 md:p-6" style="direction: rtl;">
    {{-- تغییر: اضافه کردن سایه سنگین و مرز (Border) برای تفکیک از محتوا بدون نیاز به Overlay --}}
    <div id="consent-box" class="bg-white dark:bg-zinc-900 w-full max-w-5xl mx-auto rounded-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.15)] border border-gray-200 dark:border-zinc-800 overflow-hidden">

        <div id="consent-main" class="p-5 md:p-6">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex-1 text-center md:text-right">
                    <h3 id="consent-title" class="text-base font-bold text-gray-900 dark:text-white mb-1">مدیریت حریم خصوصی</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-6">
                        ما برای بهبود تجربه شما از کوکی‌ها استفاده می‌کنیم. آیا با استفاده از داده‌های مرورگر موافقید؟
                    </p>
                </div>

                <div class="flex flex-wrap justify-center gap-2 shrink-0">
                    <button id="btn-accept-all" class="btn btn-primary">
                        پذیرش همه
                    </button>
                    <button id="btn-manage" class="btn btn-secondary">
                        تنظیمات
                    </button>
                    <button id="btn-reject-all" class="btn btn-ghost">
                        رد کردن
                    </button>
                </div>
            </div>
        </div>

        {{-- بخش تنظیمات (بدون تغییر در منطق، فقط بهینه سازی ظاهر) --}}
        <div id="consent-settings" class="hidden p-6 bg-gray-50 dark:bg-zinc-950 border-t border-gray-100 dark:border-zinc-800">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-md font-bold text-gray-900 dark:text-white">تنظیمات پیشرفته</h4>
                <button id="btn-back" class="btn btn-ghost btn-sm text-tech-blue">بازگشت</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                <label class="flex items-center justify-between p-3 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800">
                    <span class="text-xs font-bold dark:text-gray-200">ضروری</span>
                    <input type="checkbox" checked disabled class="w-4 h-4 rounded text-blue-600">
                </label>
                <label class="flex items-center justify-between p-3 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 cursor-pointer">
                    <span class="text-xs font-bold dark:text-gray-200">تبلیغات</span>
                    <input type="checkbox" id="chk-marketing" class="w-4 h-4 rounded text-blue-600">
                </label>
                <label class="flex items-center justify-between p-3 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 cursor-pointer">
                    <span class="text-xs font-bold dark:text-gray-200">آنالیز</span>
                    <input type="checkbox" id="chk-analytics" class="w-4 h-4 rounded text-blue-600">
                </label>
            </div>
            <button id="btn-save-settings" class="btn btn-primary btn-block">
                ذخیره تنظیمات
            </button>
        </div>
    </div>
</div>

<script>
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function() { dataLayer.push(arguments); };

    document.addEventListener("DOMContentLoaded", function () {
        const consentKey = "consent_status";
        const overlay = document.getElementById('consent-overlay');

        // --- ۱. تشخیص هوشمند ربات‌ها و گوگل‌بات ---
        function isBot() {
            const userAgent = navigator.userAgent.toLowerCase();
            const bots = [
                'googlebot', 'bingbot', 'yandexbot', 'duckduckbot', 'slurp',
                'baiduspider', 'facebookexternalhit', 'twitterbot', 'rogerbot',
                'linkedinbot', 'embedly', 'quora link preview', 'showyoubot',
                'outbrain', 'pinterest/0.', 'developers.google.com/+/web/snippet',
                'slackbot', 'vkshare', 'w3c_validator', 'redditbot', 'applebot'
            ];
            return bots.some(bot => userAgent.includes(bot));
        }

        function updateGtmConsent(status) {
            const consentObj = {
                ad_storage: status.marketing ? 'granted' : 'denied',
                ad_user_data: status.marketing ? 'granted' : 'denied',
                ad_personalization: status.marketing ? 'granted' : 'denied',
                analytics_storage: status.analytics ? 'granted' : 'denied',
                personalization_storage: status.analytics ? 'granted' : 'denied',
                functionality_storage: 'granted',
                security_storage: 'granted'
            };
            localStorage.setItem(consentKey, JSON.stringify(consentObj));
            gtag('consent', 'update', consentObj);
            dataLayer.push({'event': 'consent_updated'});
        }

        function closeBanner() {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            // تغییر: دیگر اسکرول بادی مسدود نمی‌شود
        }

        function showBanner() {
            // تغییر: اگر ربات نبود بنر را نشان بده
            if (!isBot()) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            } else {
                // برای بات‌ها به صورت پیش‌فرض همه چیز را تایید کن تا در آنالیزها اختلال ایجاد نشود (اختیاری)
                updateGtmConsent({ marketing: true, analytics: true });
            }
        }

        // --- منطق بررسی موقعیت و نمایش ---
        const previousConsent = localStorage.getItem(consentKey);

        if (!previousConsent) {
            fetch("https://get.geojs.io/v1/ip/geo.json")
                .then((res) => res.json())
                .then((data) => {
                    if (data.country_code === "IR" || data.country === "Iran") {
                        updateGtmConsent({ marketing: true, analytics: true });
                    } else {
                        showBanner();
                    }
                })
                .catch(() => showBanner());
        }

        // --- دکمه‌ها ---
        document.getElementById('btn-accept-all').onclick = () => {
            updateGtmConsent({ marketing: true, analytics: true });
            closeBanner();
        };

        document.getElementById('btn-reject-all').onclick = () => {
            updateGtmConsent({ marketing: false, analytics: false });
            closeBanner();
        };

        document.getElementById('btn-save-settings').onclick = () => {
            const marketing = document.getElementById('chk-marketing').checked;
            const analytics = document.getElementById('chk-analytics').checked;
            updateGtmConsent({ marketing, analytics });
            closeBanner();
        };

        document.getElementById('btn-manage').onclick = () => {
            document.getElementById('consent-main').classList.add('hidden');
            document.getElementById('consent-settings').classList.remove('hidden');
        };

        document.getElementById('btn-back').onclick = () => {
            document.getElementById('consent-settings').classList.add('hidden');
            document.getElementById('consent-main').classList.remove('hidden');
        };
    });
</script>
