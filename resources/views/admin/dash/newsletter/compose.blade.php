<x-admin.layout title="ارسال خبرنامه" subtitle="پیام برای همه اعضای تأییدشده در صف ارسال قرار می‌گیرد" :breadcrumbs="[['label' => 'خبرنامه', 'url' => route('panel.newsletter.index')], ['label' => 'ارسال']]" width="max-w-4xl">
    <form method="POST" action="{{ route('panel.newsletter.send') }}" id="nl-form" class="space-y-6">
        @csrf
        <x-admin.card title="محتوا" icon="pencil-line">
            <div class="space-y-4">
                <x-admin.input name="subject" label="موضوع" required maxlength="190" placeholder="مثلاً: مقاله تازه درباره سئو فنی" />
                <x-admin.textarea name="content" label="متن (HTML مجاز است)" required rows="14" id="nl-content" class="font-mono !text-[13px]" dir="auto" hint="تگ‌های مجاز: p، br، a، strong، em، ul، ol، li، h1–h4، img، table و… . لینک لغو عضویت خودکار به انتهای ایمیل اضافه می‌شود." />
                <div class="flex flex-wrap gap-2">
                    <x-admin.button type="button" variant="secondary" size="sm" icon="eye" id="nl-preview-btn">پیش‌نمایش</x-admin.button>
                </div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="text-sm text-slate-600">گیرندگان: <strong class="text-ink">{{ number_format($recipients) }}</strong> عضو تأییدشده</div>
                <div class="flex flex-wrap gap-2">
                    <x-admin.button type="submit" variant="secondary" icon="flask" name="test" value="1">ارسال آزمایشی برای خودم</x-admin.button>
                    <x-admin.button type="submit" icon="paper-plane-tilt" id="nl-send" :disabled="$recipients === 0">شروع ارسال گروهی</x-admin.button>
                </div>
            </div>
        </x-admin.card>
    </form>
    <x-admin.modal name="nl-preview" title="پیش‌نمایش ایمیل" max-width="2xl">
        <iframe id="nl-frame" class="h-[60vh] w-full rounded-xl border border-line bg-white" sandbox=""></iframe>
    </x-admin.modal>
    <x-slot:scripts>
        <script>
        (function () {
            document.getElementById('nl-preview-btn').addEventListener('click', () => {
                const html = document.getElementById('nl-content').value;
                document.getElementById('nl-frame').srcdoc = '<html dir="rtl"><body style="font-family:Tahoma,sans-serif;line-height:1.9;padding:16px">' + html + '</body></html>';
                adminModal.open('nl-preview');
            });
            document.getElementById('nl-form').addEventListener('submit', (e) => {
                const t = e.submitter;
                if (t && t.id === 'nl-send' && !confirm('خبرنامه برای همه اعضای تأییدشده ارسال شود؟ این کار قابل بازگشت نیست.')) e.preventDefault();
            });
        })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
