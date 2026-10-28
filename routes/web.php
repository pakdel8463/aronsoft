<?php

use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProformaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class,'index'])->name('home');
require __DIR__.'/auth.php';
require __DIR__.'/tickets.php';

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/blog',[PostController::class,'index'])->name('blog.index');
Route::get('/blog/category/{category:slug}', [BlogCategoryController::class, 'show'])->name('blog.category');
Route::get('/blog/{post}',[PostController::class,'show'])->name('blog.show');


Route::get('/portfolios',[PortfolioController::class,'index'])->name('portfolio.index');
Route::get('/portfolio/category/{slug}',[PortfolioController::class,'category'])->name('portfolio.category');
Route::get('/portfolio/{portfolio}',[PortfolioController::class,'show'])->name('portfolio.show');

// ---- landing pages (آدرس‌های قدیمی landing/* به services/* منتقل می‌شوند) ----
Route::redirect('/landing/about-us', '/about-us', 301);
Route::redirect('/services/about-us', '/about-us', 301);

Route::get('/about-us', [LandingPageController::class, 'show'])
    ->defaults('slug', 'about-us') // به کنترلر می‌گوید که اسلاگ این صفحه about-us است
    ->name('about');

Route::get('/services/{slug}', [LandingPageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('landing.show');

Route::get('/landing/{slug}', fn (string $slug) => redirect()->route('landing.show', $slug, 301))
    ->where('slug', '[A-Za-z0-9\-_]+');

// ---- public forms (همه با throttle) ----
Route::post('/leads/store', [LeadController::class, 'store'])->middleware('throttle:8,10')->name('leads.store');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');

Route::get('/order', [OrderController::class, 'create'])->name('order.create');
Route::post('/order', [OrderController::class, 'store'])->middleware('throttle:5,10')->name('order.store');
Route::get('/order/{code}', [OrderController::class, 'show'])
    ->where('code', '[A-Za-z0-9\-]+')
    ->middleware('throttle:30,1')
    ->name('order.show');

Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware('throttle:6,10')->name('comments.store');


// ---- محصولات آرون سافت (لندینگ‌های قابل‌مدیریت از پنل) ----
Route::get('/products', [ProductPageController::class, 'index'])->name('product-pages.index');
Route::get('/products/{slug}', [ProductPageController::class, 'show'])->where('slug', '[A-Za-z0-9\x{0600}-\x{06FF}\-]+')->name('product-pages.show');

Route::get('/shop', [ProductController::class, 'index'])->name('products.index');
Route::get('/shop/category/{type}', [ProductController::class, 'filterByType'])->name('products.filter');
Route::get('/shop/{slug}', [ProductController::class, 'show'])->name('products.show');

// خبرنامه با تأیید دو مرحله‌ای (double opt-in)
Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->middleware('throttle:5,10')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->middleware('throttle:20,1')->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->middleware('throttle:20,1')->name('newsletter.unsubscribe');

// File manager: panel users only (plain 'auth' let any registered customer upload files)
Route::group(['prefix' => 'panel/laravel-filemanager', 'middleware' => ['web', 'auth', 'admin.access']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// پیش‌نمایش قالب پیش‌فاکتور (فقط ادمین)
Route::view('/inv-8x2k9z', 'front.t-one.landings.inv-8x2k9z')
    ->middleware(['auth', 'admin.access'])
    ->name('invoice.preview');
Route::get('/invoice/{uuid}', [ProformaController::class, 'show'])->name('proforma.show');
Route::post('/inv/store', [ProformaController::class, 'store'])->middleware('throttle:10,10')->name('proforma.store');

// sitemap.xml: اگر فایل استاتیک وجود نداشته باشد یک‌بار ساخته می‌شود (بازسازی دستی از پنل یا `php artisan sitemap:generate`)
Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');
    if (! is_file($path)) {
        \Illuminate\Support\Facades\Artisan::call('sitemap:generate');
    }
    abort_unless(is_file($path), 404);

    return response()->file($path, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

// فرم‌های فرم‌ساز
Route::post('/forms/{form}', [\App\Http\Controllers\FormController::class, 'submit'])->whereNumber('form')->middleware('throttle:10,1')->name('forms.submit');

// کپچای نامرئی: معمای تازه برای ویجت <x-captcha />
Route::get('/captcha/challenge', \App\Http\Controllers\CaptchaController::class)->middleware('throttle:30,1')->name('captcha.challenge');

// صفحه‌های صفحه‌ساز: /{slug}. Route::fallback همیشه آخر از همه بررسی می‌شود، پس هیچ مسیر موجودی را نمی‌پوشاند.
Route::fallback([PageController::class, 'show'])->name('page.show');
