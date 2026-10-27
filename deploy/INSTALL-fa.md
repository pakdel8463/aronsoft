# راهنمای نصب و به‌روزرسانی روی هاست (آرون سافت)

> این راهنما برای هاست اشتراکی (cPanel/DirectAdmin) است. ریشه‌ی وب‌سایت شما `public_html` است.

## پیش‌نیازها
- PHP ‏8.2 یا بالاتر (در cPanel ← Select PHP Version / MultiPHP Manager)
- افزونه‌ها: `mbstring, openssl, pdo_mysql, fileinfo, tokenizer, xml, ctype, curl` و برای تصاویر `gd`
- دیتابیس MySQL ساخته‌شده (نام، کاربر، رمز)
- دسترسی SSH یا «Terminal» در cPanel (اگر ندارید، بخش «بدون SSH» را ببینید)

## نصب تازه
1. محتوای zip را در **ریشه‌ی اکانت هاست** (کنار پوشه‌ی `public_html`، نه داخل آن) آپلود و Extract کنید. ساختار باید این‌طور شود:
   ```
   app/  bootstrap/  config/  database/  deploy/  resources/  routes/  storage/  vendor/ (در صورت وجود)
   public_html/   ← ریشه‌ی سایت
   artisan  composer.json  .env.example
   ```
2. در Terminal:
   ```bash
   cd ~            # پوشه‌ای که artisan در آن است
   bash deploy/install.sh --admin you@example.com
   ```
   اسکریپت مرحله‌به‌مرحله: نسخه‌ی PHP و افزونه‌ها را چک می‌کند ← `composer install --no-dev` ← فایل `.env` را با پرسش می‌سازد (آدرس سایت، دیتابیس، ایمیل) ← کلید برنامه ← بکاپ دیتابیس ← `migrate` ← کپی فایل‌های `public` به `public_html` ← کش‌ها ← نقشه‌ی سایت ← تست سلامت.
3. رمز مدیر یک‌بار روی صفحه نمایش داده می‌شود؛ بعد از ورود عوضش کنید.
4. Cron (هر یک دقیقه) در cPanel ← Cron Jobs:
   ```
   * * * * * cd /home/USER && php artisan schedule:run >> /dev/null 2>&1
   ```
5. ورود به `/panel` ← بخش «دسترسی‌ها» ← «ساخت دسترسی‌های استاندارد» (فقط بار اول).

## به‌روزرسانی (نسخه‌ی جدید روی سایت فعلی)
1. از فایل‌ها و دیتابیس بکاپ بگیرید (اسکریپت خودش هم بکاپ SQL در `storage/backups` می‌گذارد).
2. فایل `.env` فعلی را **دست نزنید** و پاک نکنید؛ zip جدید `.env` ندارد.
3. فایل‌های zip را روی ریشه Extract کنید (Overwrite)، سپس:
   ```bash
   bash deploy/install.sh --yes
   ```
   (`--no-composer` اگر `vendor/` را خودتان آپلود کرده‌اید.)
4. بازگشت (Rollback): فایل‌های قبلی را برگردانید و بکاپ SQL را در phpMyAdmin Import کنید.

## بدون SSH
- `vendor/` را روی کامپیوتر خودتان با `composer install --no-dev --optimize-autoloader` بسازید و همراه فایل‌ها آپلود کنید.
- `.env` را از `.env.example` بسازید و پر کنید (`APP_ENV=production`, `APP_DEBUG=false`, اطلاعات DB و ایمیل).
- در cPanel ← Cron Jobs یک‌بار (برای همین یک دقیقه‌ی بعد) این را بگذارید و بعد پاک کنید:
  `cd /home/USER && php artisan key:generate --force && php artisan migrate --force && php artisan aron:make-admin you@example.com && php artisan optimize && php artisan sitemap:generate`
- محتوای پوشه‌ی `public/` را داخل `public_html/` کپی کنید (به‌جز `.htaccess` موجود).

## نکته‌های امنیتی
- فایل `.env` را هرگز در zip/ایمیل/گیت نگذارید. اگر قبلاً در فایلی رد و بدل شده، **رمز دیتابیس، رمز SMTP و `APP_KEY`** را عوض کنید.
- `APP_DEBUG` باید `false` باشد. پوشه‌ی `storage/` و `.env` نباید از بیرون قابل دانلود باشند (فایل `public_html/.htaccess` این‌ها را می‌بندد).
- مسیر `/_generate-sitemap` حذف شده؛ سایت‌مپ را از پنل یا cron بسازید (`php artisan sitemap:generate`).

## ایمیل و اعلان‌ها
در `.env`: `MAIL_*` (SMTP)، `ADMIN_NOTIFY_EMAIL` (گیرنده‌ی اعلان لید/سفارش/تیکت). اختیاری: `TELEGRAM_BOT_TOKEN` و `TELEGRAM_CHAT_ID`.

## عیب‌یابی
| مشکل | راه حل |
|---|---|
| صفحه‌ی ۵۰۰ | `storage/logs/laravel.log` را ببینید؛ مجوز `storage` و `bootstrap/cache` (755/775) |
| استایل‌ها نمی‌آیند | `public/build` داخل `public_html/build` کپی شده؟ دوباره `bash deploy/install.sh --yes` |
| عکس‌های آپلودی نمایش داده نمی‌شوند | وجود `public_html/storage` (لینک یا پوشه) — اسکریپت می‌سازد |
| `composer install` خطا می‌دهد | حافظه PHP ≥ 512M؛ پکیج خصوصی `aronlabs/captcha` باید در دسترس یا از قبل در `vendor/` باشد |
| بعد از تغییر `.env` تغییری دیده نمی‌شود | `php artisan config:clear` سپس `config:cache` |

## فایل‌های قدیمی پنل (`aron-legacy-admin-assets.zip`)
بخش‌های «نوشته‌ها، دسته‌بندی‌ها، دیدگاه‌ها، رسانه» هنوز با ظاهر قدیمی پنل کار می‌کنند و به پوشه‌ی `public_html/admin` نیاز دارند. روی سایت فعلی این پوشه از قبل هست و لازم نیست کاری کنید. فقط در **نصب تازه**، zip جداگانه را روی ریشه‌ی اکانت Extract کنید (پوشه‌ی `public/admin` را می‌سازد) و سپس `bash deploy/install.sh --yes` را اجرا کنید تا به `public_html` کپی شود.

## نصب بدون Terminal (فقط File Manager و مرورگر) — روش پیشنهادی وقتی SSH ندارید
1. از دیتابیس در phpMyAdmin بکاپ (Export) بگیرید و از فایل‌ها هم بکاپ بگیرید.
2. zip را در ریشه‌ی اکانت (کنار `public_html`) Extract کنید. `.env` و `vendor/` فعلی دست نمی‌خورند.
3. فایل `deploy/web-install.php` را به داخل `public_html/` منتقل کنید (Move)، با Editor باز کنید و به‌جای `CHANGE_ME` یک رمز طولانی تصادفی (حداقل ۱۶ حرف) بگذارید.
4. در مرورگر باز کنید: `https://سایت-شما/web-install.php?token=رمز-شما` (چک‌لیست نشان می‌دهد).
5. اگر همه‌چیز `[ok]` بود: `https://سایت-شما/web-install.php?token=رمز-شما&run=1` (برای ساخت مدیر: `&admin=you@mail.com`). این کار فایل‌های `public` را به `public_html` کپی می‌کند، migrate می‌زند، کش‌ها و سایت‌مپ را می‌سازد و **خودش را پاک می‌کند**. اگر پاک نشد، حتماً دستی پاکش کنید.
6. در cPanel ← Cron Jobs یک کار «هر دقیقه»: `php /home/USER/artisan schedule:run`
7. ورود به `/panel` ← دسترسی‌ها ← «ساخت دسترسی‌های استاندارد».
نکته: این روش `composer install` و `.env` نمی‌سازد؛ برای نصب تازه، `vendor/` و `.env` باید از قبل آماده باشند.
