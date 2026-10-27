<?php

return [
    /*
    | شماره تیکت: TKT-100001 (prefix + start + id)
    */
    'prefix' => 'TKT-',
    'start' => 100000,

    /*
    | SLA پاسخ اول (ساعت) بر اساس اولویت؛ در لیست مدیریت به‌صورت نشان نمایش داده می‌شود.
    */
    'sla' => [
        'first_response_hours' => [
            'low' => 48,
            'normal' => 24,
            'high' => 8,
            'urgent' => 2,
        ],
    ],

    /*
    | پیوست‌ها: روی دیسک خصوصی (storage/app/private) ذخیره و فقط با مسیر مجاز دانلود می‌شوند.
    */
    'attachments' => [
        'disk' => 'local',
        'max_files' => 3,
        'max_kb' => 5120,
        // پسوند => mime‌های مجاز (بر اساس محتوای فایل بررسی می‌شود، نه نام آن)
        'types' => [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'pdf' => ['application/pdf'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'txt' => ['text/plain'],
        ],
    ],

    'max_open_per_user' => 10,   // حداکثر تیکت باز هم‌زمان برای هر کاربر
    'reopen_days' => 14,         // مهلت بازگشایی تیکت بسته‌شده توسط مشتری
    'per_page' => 20,

    // گیرندهٔ اعلان تیکت‌ها اگر ایمیل دپارتمان خالی باشد (پیش‌فرض: mail.admin_address)
    'notify_email' => env('TICKETS_NOTIFY_EMAIL'),
];
