<?php

/*
|--------------------------------------------------------------------------
| کتابخانه رسانه (/panel/media)
|--------------------------------------------------------------------------
| MEDIA_DISK:
|   auto           (پیش‌فرض) اگر public/storage (storage:link) وجود دارد «public» وگرنه «public_uploads»
|   public         storage/app/public/uploads/YYYY/MM  ← نیاز به `php artisan storage:link` دارد
|   public_uploads public_path('uploads')/YYYY/MM       ← بدون symlink (هاست اشتراکی). اگر public_path() همان
|                  وب‌روت نیست، MEDIA_PUBLIC_ROOT را مثلاً /home/USER/public_html/uploads بگذارید.
| هر رکورد disk خودش را ذخیره می‌کند؛ تغییر MEDIA_DISK فایل‌های قبلی را خراب نمی‌کند.
*/
return [
    'disk' => env('MEDIA_DISK', 'auto'),

    // ریشه فیزیکی دیسک public_uploads و پیشوند آدرس عمومی آن
    'public_root' => env('MEDIA_PUBLIC_ROOT'),
    'public_url_prefix' => env('MEDIA_PUBLIC_URL_PREFIX', 'uploads'),

    'directory' => 'uploads',

    // حداکثر حجم (کیلوبایت) به‌ازای نوع
    'max_kb' => [
        'image' => (int) env('MEDIA_MAX_IMAGE_KB', 15360),
        'audio' => (int) env('MEDIA_MAX_AUDIO_KB', 51200),
        'video' => (int) env('MEDIA_MAX_VIDEO_KB', 204800),
        'file' => (int) env('MEDIA_MAX_FILE_KB', 30720),
    ],

    // تصویر
    'max_dimension' => 2560,
    'quality' => 85,
    'webp' => true,
    'sizes' => [
        'thumb' => ['width' => 360, 'height' => 360, 'crop' => true],
        'medium' => ['width' => 768],
        'large' => ['width' => 1600],
    ],

    // mime واقعی (از محتوا، نه هدر کلاینت) → پسوندهای مجاز. SVG عمداً مجاز نیست.
    'allowed' => [
        'image/jpeg' => ['jpg', 'jpeg', 'jpe'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'video/mp4' => ['mp4', 'm4v'],
        'video/webm' => ['webm'],
        'video/quicktime' => ['mov'],
        'audio/mpeg' => ['mp3'],
        'audio/ogg' => ['ogg', 'oga'],
        'audio/wav' => ['wav'],
        'audio/x-wav' => ['wav'],
        'audio/mp4' => ['m4a'],
        'audio/x-m4a' => ['m4a'],
        'application/pdf' => ['pdf'],
        'application/zip' => ['zip', 'docx', 'xlsx', 'pptx'],
        'application/x-zip-compressed' => ['zip'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'application/msword' => ['doc'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/x-ole-storage' => ['doc', 'xls', 'ppt'],
        'application/CDFV2' => ['doc', 'xls', 'ppt'],
        'text/plain' => ['txt', 'csv'],
        'text/csv' => ['csv'],
    ],

    // اگر هر بخشی از نام فایل (مثلاً shell.php.jpg) یکی از این‌ها باشد، رد می‌شود
    'blocked_extensions' => [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'pht', 'phps', 'inc', 'pl', 'py', 'cgi', 'asp', 'aspx', 'jsp', 'jspx',
        'sh', 'bash', 'exe', 'com', 'bat', 'cmd', 'msi', 'dll', 'scr', 'vbs', 'js', 'mjs', 'jar', 'htaccess', 'htpasswd', 'ini', 'svg', 'svgz', 'html', 'htm', 'xhtml', 'shtml',
    ],
];
