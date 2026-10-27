# نصب لندینگ محصولات آرون سافت

فایل‌ها را در ریشه `/var/www/aron` از حالت فشرده خارج کنید. پیش از این کار، از کد و دیتابیس بکاپ تهیه کنید. فایل‌های فعلی `routes/web.php` و `routes/admin.php` را جایگزین نکنید؛ خطوط زیر را در آنها اضافه کنید:

## routes/web.php

```php
Route::get('/products', [\App\Http\Controllers\SoftwareProductController::class, 'index'])->name('software-products.index');
Route::get('/products/{slug}', [\App\Http\Controllers\SoftwareProductController::class, 'show'])->name('software-products.show');
```

## routes/admin.php

```php
Route::get('software-products', [\App\Http\Controllers\Admin\SoftwareProductController::class, 'index'])->name('software-products.index');
Route::get('software-products/{softwareProduct}/edit', [\App\Http\Controllers\Admin\SoftwareProductController::class, 'edit'])->name('software-products.edit');
Route::put('software-products/{softwareProduct}', [\App\Http\Controllers\Admin\SoftwareProductController::class, 'update'])->name('software-products.update');
```

در پروژه شما `routes/admin.php` زیر گروه `panel.` و محافظت `auth` و `admin.access` لود می‌شود؛ کنترلر نیز Super Admin را بررسی می‌کند.

## دستورات

```bash
cd /var/www/aron
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan route:list --path=products
php artisan route:list --path=software-products
```

قبل از `storage:link` مطمئن شوید `public_html/storage` یا مسیر public سایت به‌درستی به `storage/app/public` متصل می‌شود. اگر لینک وجود دارد یا داکیومنت‌روت متفاوت است، ابتدا بررسی کنید.

ادمین: `/panel/software-products`، لندینگ‌ها: `/products/attendance` و `/products/moneyiar` و `/products/calorieto`.

نکته: امکان تغییر نام محصول در پنل هست، ولی slugهای اولیه ثابت هستند. تصاویر در دیسک public ذخیره می‌شوند و آپلود تصاویر JPG/PNG/WebP تا 4MB است.
