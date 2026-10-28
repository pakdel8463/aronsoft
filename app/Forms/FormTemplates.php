<?php

namespace App\Forms;

/** نقطه‌های شروع آماده هنگام ساخت فرم جدید در پنل. */
final class FormTemplates
{
    public static function all(): array
    {
        return [
            'blank' => ['label' => 'فرم خالی', 'icon' => 'plus', 'description' => 'از صفر شروع کنید.', 'fields' => [], 'settings' => []],

            'contact' => [
                'label' => 'تماس با ما', 'icon' => 'envelope-simple', 'description' => 'نام، موبایل، ایمیل، موضوع و متن پیام.',
                'fields' => [
                    self::f('text', 'name', 'نام و نام خانوادگی', ['required' => true, 'width' => 'half']),
                    self::f('mobile', 'mobile', 'شماره موبایل', ['required' => true, 'width' => 'half', 'placeholder' => '09123456789']),
                    self::f('email', 'email', 'ایمیل', ['width' => 'half']),
                    self::f('select', 'subject', 'موضوع', ['width' => 'half', 'options' => self::opts(['مشاوره', 'پشتیبانی', 'همکاری', 'سایر'])]),
                    self::f('textarea', 'message', 'متن پیام', ['required' => true, 'rows' => 5]),
                ],
                'settings' => ['submit_label' => 'ارسال پیام', 'confirmation_message' => '<p>پیام شما ثبت شد. همکاران ما به‌زودی پاسخ می‌دهند.</p>'],
            ],

            'project' => [
                'label' => 'درخواست پروژه (دو مرحله‌ای)', 'icon' => 'rocket-launch',
                'description' => 'نوع پروژه، امکانات و بودجه؛ سپس اطلاعات تماس. با فیلد شرطی برای فروشگاه.',
                'fields' => [
                    self::f('section', 'project_info', 'درباره‌ی پروژه'),
                    self::f('radio', 'project_type', 'نوع پروژه', ['required' => true, 'options' => self::opts(['سایت شرکتی', 'فروشگاه اینترنتی', 'نرم‌افزار اختصاصی', 'اپلیکیشن موبایل', 'سایر'])]),
                    self::f('checkbox', 'shop_features', 'امکانات فروشگاه', ['options' => self::opts(['درگاه پرداخت', 'مدیریت انبار', 'اتصال به حسابداری', 'باشگاه مشتریان']),
                        'logic' => ['enabled' => true, 'action' => 'show', 'match' => 'all', 'rules' => [['field' => 'project_type', 'op' => 'is', 'value' => 'فروشگاه اینترنتی']]]]),
                    self::f('select', 'budget', 'بودجه‌ی تقریبی', ['width' => 'half', 'options' => self::opts(['کمتر از ۵۰ میلیون تومان', '۵۰ تا ۱۵۰ میلیون تومان', 'بیشتر از ۱۵۰ میلیون تومان', 'هنوز مشخص نیست'])]),
                    self::f('date', 'deadline', 'زمان موردنظر برای تحویل', ['width' => 'half']),
                    self::f('textarea', 'description', 'توضیح پروژه', ['rows' => 4, 'placeholder' => 'هدف، مخاطبان، نمونه‌سایت‌هایی که دوست دارید…']),
                    self::f('page', 'step2', 'اطلاعات تماس'),
                    self::f('text', 'name', 'نام و نام خانوادگی', ['required' => true, 'width' => 'half']),
                    self::f('mobile', 'mobile', 'شماره موبایل', ['required' => true, 'width' => 'half']),
                    self::f('text', 'company', 'نام شرکت / کسب‌وکار', ['width' => 'half']),
                    self::f('email', 'email', 'ایمیل', ['width' => 'half']),
                    self::f('file', 'attachment', 'فایل پیوست (اختیاری)', ['accept' => 'pdf,doc,docx,jpg,jpeg,png,zip', 'max_kb' => 10240, 'help' => 'مثلاً نقشه‌ی سایت یا توضیحات کامل‌تر']),
                ],
                'settings' => ['submit_label' => 'ثبت درخواست', 'progress' => 'steps', 'confirmation_message' => '<p>درخواست شما ثبت شد. برای بررسی و ارسال پیش‌فاکتور با شما تماس می‌گیریم.</p>'],
            ],

            'job' => [
                'label' => 'همکاری و استخدام', 'icon' => 'briefcase', 'description' => 'اطلاعات فردی، حوزه‌ی تخصص، سابقه و آپلود رزومه.',
                'fields' => [
                    self::f('text', 'name', 'نام و نام خانوادگی', ['required' => true, 'width' => 'half']),
                    self::f('mobile', 'mobile', 'شماره موبایل', ['required' => true, 'width' => 'half']),
                    self::f('email', 'email', 'ایمیل', ['required' => true, 'width' => 'half']),
                    self::f('select', 'role', 'حوزه‌ی تخصص', ['required' => true, 'width' => 'half', 'options' => self::opts(['بک‌اند (Laravel)', 'فرانت‌اند', 'طراحی UI/UX', 'DevOps', 'سئو و محتوا', 'سایر'])]),
                    self::f('number', 'experience', 'سابقه‌ی کار (سال)', ['width' => 'half', 'min' => 0, 'max' => 40]),
                    self::f('url', 'portfolio', 'لینک نمونه‌کار / گیت‌هاب', ['width' => 'half']),
                    self::f('file', 'resume', 'رزومه', ['required' => true, 'accept' => 'pdf,doc,docx', 'max_kb' => 5120]),
                    self::f('textarea', 'about', 'درباره‌ی خودتان', ['rows' => 4]),
                    self::f('consent', 'privacy', 'حریم خصوصی', ['required' => true, 'content' => '<p>اجازه می‌دهم اطلاعاتم برای بررسی همکاری نگه‌داری شود.</p>']),
                ],
                'settings' => ['submit_label' => 'ارسال درخواست همکاری', 'confirmation_message' => '<p>درخواست شما دریافت شد. در صورت تناسب با شما تماس می‌گیریم.</p>'],
            ],
        ];
    }

    public static function f(string $type, string $key, string $label, array $extra = []): array
    {
        return array_merge(['id' => 'f'.substr(md5($key.$type), 0, 6), 'type' => $type, 'key' => $key, 'label' => $label], $extra);
    }

    public static function opts(array $labels): array
    {
        return array_map(fn ($l) => ['label' => $l, 'value' => $l], $labels);
    }
}
