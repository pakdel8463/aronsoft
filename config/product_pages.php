<?php

/**
 * اسکیمای «صفحه‌ی محصول» (لندینگ محصولات آرون سافت).
 * فرم پنل، اعتبارسنجی و ویوهای عمومی همه از همین فایل ساخته می‌شوند؛
 * برای افزودن فیلد یا بخش جدید فقط همین‌جا و ویوی همان بخش را تغییر دهید.
 *
 * نوع فیلد: text | textarea | lines (هر خط یک مورد) | url | image | icon | select | toggle | repeater
 */

return [

    /* آیکن‌های مجاز (باید در زیرمجموعه‌ی فونت فرانت و پنل باشند؛ بعد از تغییر: python3 scripts/subset-phosphor.py و subset-phosphor-admin.py) */
    'icons' => [
        'ph-check-circle', 'ph-sparkle', 'ph-lightning', 'ph-rocket-launch', 'ph-shield-check', 'ph-lock-key',
        'ph-lock', 'ph-fingerprint', 'ph-key', 'ph-bell', 'ph-bell-ringing', 'ph-clock',
        'ph-timer', 'ph-calendar-check', 'ph-calendar-blank', 'ph-chart-line-up', 'ph-chart-bar', 'ph-chart-pie',
        'ph-chart-pie-slice', 'ph-trend-up', 'ph-wallet', 'ph-coins', 'ph-money', 'ph-credit-card',
        'ph-receipt', 'ph-bank', 'ph-piggy-bank', 'ph-calculator', 'ph-barbell', 'ph-heartbeat',
        'ph-heart', 'ph-fire', 'ph-person-simple-run', 'ph-apple-logo', 'ph-google-play-logo', 'ph-android-logo',
        'ph-windows-logo', 'ph-users', 'ph-users-three', 'ph-user-circle', 'ph-user-plus', 'ph-chats-circle',
        'ph-chat-circle-dots', 'ph-megaphone', 'ph-share-network', 'ph-trophy', 'ph-medal', 'ph-target',
        'ph-flag', 'ph-map-pin', 'ph-map-trifold', 'ph-device-mobile', 'ph-device-tablet', 'ph-desktop',
        'ph-laptop', 'ph-browser', 'ph-globe', 'ph-cloud', 'ph-cloud-arrow-up', 'ph-database',
        'ph-hard-drives', 'ph-plugs-connected', 'ph-gear-six', 'ph-sliders', 'ph-wrench', 'ph-puzzle-piece',
        'ph-stack', 'ph-squares-four', 'ph-layout', 'ph-list-checks', 'ph-clipboard-text', 'ph-file-text',
        'ph-file-arrow-down', 'ph-download-simple', 'ph-upload-simple', 'ph-qr-code', 'ph-translate', 'ph-headset',
        'ph-lifebuoy', 'ph-seal-check', 'ph-star', 'ph-thumbs-up', 'ph-eye', 'ph-magnifying-glass',
        'ph-funnel', 'ph-arrows-clockwise', 'ph-brain', 'ph-robot', 'ph-magic-wand', 'ph-fork-knife',
        'ph-carrot', 'ph-scales', 'ph-camera', 'ph-image', 'ph-play-circle', 'ph-video-camera',
        'ph-identification-card', 'ph-buildings', 'ph-storefront', 'ph-briefcase', 'ph-coffee', 'ph-handshake',
        'ph-graph', 'ph-infinity', 'ph-link', 'ph-paper-plane-right', 'ph-envelope-simple', 'ph-phone',
        'ph-whatsapp-logo', 'ph-telegram-logo', 'ph-instagram-logo', 'ph-github-logo', 'ph-shopping-cart', 'ph-fingerprint-simple',
        'ph-clock-clockwise', 'ph-calendar-dots', 'ph-hourglass-medium', 'ph-bell-simple', 'ph-cloud-check', 'ph-cpu',
        'ph-code', 'ph-terminal-window', 'ph-shield-star', 'ph-medal-military', 'ph-t-shirt', 'ph-scan',
        'ph-note-pencil', 'ph-pencil-simple', 'ph-swap', 'ph-arrows-left-right', 'ph-currency-circle-dollar', 'ph-tag',
        'ph-percent', 'ph-receipt-x', 'ph-trend-down', 'ph-hand-coins', 'ph-basket', 'ph-package',
        'ph-truck', 'ph-map-pin-area', 'ph-navigation-arrow', 'ph-wifi-high', 'ph-battery-charging', 'ph-flask',
        'ph-leaf', 'ph-drop', 'ph-sun', 'ph-moon', 'ph-smiley', 'ph-lightbulb',
        'ph-rocket', 'ph-crown', 'ph-diamond', 'ph-gift', 'ph-ticket', 'ph-house',
    ],

    /* دکمه‌ها و پیوندهای دانلود/ورود محصول: کلید => [برچسب پیش‌فرض، آیکن] */
    'links' => [
        'website'     => ['وب‌سایت محصول', 'ph-globe'],
        'login'       => ['ورود / ثبت‌نام', 'ph-user-circle'],
        'demo'        => ['دمو زنده', 'ph-play-circle'],
        'android'     => ['دانلود مستقیم اندروید', 'ph-android-logo'],
        'bazaar'      => ['کافه‌بازار', 'ph-download-simple'],
        'myket'       => ['مایکت', 'ph-download-simple'],
        'google_play' => ['Google Play', 'ph-google-play-logo'],
        'ios'         => ['iOS', 'ph-apple-logo'],
        'docs'        => ['مستندات / راهنما', 'ph-file-text'],
    ],

    // ساختار بخش‌های هر صفحه در قالب‌ها تعریف می‌شود: config/product_templates.php و config/product-templates/*.php

];
