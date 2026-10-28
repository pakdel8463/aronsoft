<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContactSettingsController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductPageController;
use App\Http\Controllers\Admin\ProformaController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdminController::class,'index'])->name('home');
Route::resource('users', UserController::class);
Route::resource('portfolios', PortfolioController::class);
Route::resource('landing', LandingPageController::class);
Route::post('permissions/seed-defaults', [PermissionController::class, 'seedDefaults'])->name('permissions.seed');
Route::resource('permissions', PermissionController::class)->except(['show']);
Route::resource('roles', RoleController::class)->except(['show']);
Route::post('proformas/{proforma}/send', [ProformaController::class, 'send'])->whereNumber('proforma')->name('proformas.send');
Route::resource('proformas', ProformaController::class)->except(['show']);

Route::post('/portfolio/upload-image', [PortfolioController::class, 'uploadImage'])->name('portfolio.upload-image');
Route::post('/portfolio/team-members', [PortfolioController::class, 'storeTeamMember'])->name('portfolio.team.store');



Route::post('/users/{user}/avatar-upload', [UserController::class, 'avatarUpload'])->name('users.avatar.upload');


Route::prefix('leads')->name('leads.')->group(function () {

    // صفحه لیست لیدها
    Route::get('/', [LeadController::class, 'index'])->name('index');

    // صفحه نمایش جزئیات یک لید
    Route::get('/{lead}', [LeadController::class, 'show'])->name('show');

    // تغییر وضعیت (تماس گرفته شده / نیاز به پیگیری)
    Route::post('/{lead}/status', [LeadController::class, 'updateStatus'])->name('status');

    // حذف لید
    Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');

    // یادداشت داخلی
    Route::patch('/{lead}/note', [LeadController::class, 'updateNote'])->name('note');

    // داده JSON برای کشوی جزئیات لیست
    Route::get('/{lead}/json', [LeadController::class, 'json'])->name('json');

});


Route::prefix('menus')->name('menus.')->group(function () {

    // لیست منوها
    Route::get('/', [MenuController::class, 'index'])->name('index');

    // ساخت منو
    Route::get('/create', [MenuController::class, 'create'])->name('create');
    Route::post('/store', [MenuController::class, 'store'])->name('store');

    // ویرایش منو
    Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
    Route::put('/{menu}/update', [MenuController::class, 'update'])->name('update');

    // حذف منو
    Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');

    // مدیریت آیتم‌های منو (صفحه drag & drop)
    Route::get('/{menu}/items', [MenuController::class, 'items'])->name('items');

    // ذخیره آیتم‌ها (JSON)
    Route::post('/{menu}/save', [MenuController::class, 'save'])->name('save');
});


Route::prefix('contact-messages')->name('contact-messages.')->group(function () {
    Route::get('/', [ContactMessageController::class, 'index'])->name('index');
    Route::get('{contactMessage}', [ContactMessageController::class, 'show'])->name('show');
    Route::patch('{contactMessage}/status', [ContactMessageController::class, 'updateStatus'])->name('status');
    Route::patch('{contactMessage}/spam', [ContactMessageController::class, 'toggleSpam'])->name('spam');
    Route::delete('{contactMessage}', [ContactMessageController::class, 'destroy'])->name('destroy');
});



// ================= محتوا: نوشته‌ها، دسته‌بندی‌ها، برچسب‌ها، دیدگاه‌ها، رسانه (agent P) =================
Route::get('posts/slug-check', [PostController::class, 'slugCheck'])->name('posts.slugCheck');
Route::get('posts/live-search', [PostController::class, 'liveSearch'])->name('posts.liveSearch');
Route::post('posts/upload', [PostController::class, 'upload'])->name('posts.upload');
Route::post('posts/quick-update', [PostController::class, 'quickUpdate'])->name('posts.quickUpdate');
Route::post('posts/bulk', [PostController::class, 'bulk'])->name('posts.bulk');
Route::delete('posts/{post}/force', [PostController::class, 'forceDelete'])->name('posts.forceDelete');
Route::post('posts/{post}/restore', [PostController::class, 'restore'])->name('posts.restore');
Route::post('posts/{post}/duplicate', [PostController::class, 'duplicate'])->name('posts.duplicate');
Route::get('posts/{post}/preview', [PostController::class, 'preview'])->name('posts.preview');
Route::get('posts/{post}/revisions/{revision}', [PostController::class, 'revision'])->name('posts.revision');
Route::resource('posts', PostController::class)->except(['show']);

Route::post('categories/ajax-store', [CategoryController::class, 'ajaxStore'])->name('categories.ajaxStore');
Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
Route::post('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
Route::resource('categories', CategoryController::class);

Route::get('tags/suggest', [TagController::class, 'suggest'])->name('tags.suggest');
Route::post('tags/bulk', [TagController::class, 'bulk'])->name('tags.bulk');
Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

Route::prefix('comments')->name('comments.')->group(function () {
    Route::get('/', [CommentController::class, 'index'])->name('index');
    Route::post('/bulk', [CommentController::class, 'bulk'])->name('bulk');
    Route::get('/{comment}', [CommentController::class, 'show'])->name('show');
    Route::post('/{comment}/approve', [CommentController::class, 'approve'])->name('approve');
    Route::post('/{comment}/reject', [CommentController::class, 'reject'])->name('reject');
    Route::post('/{comment}/spam', [CommentController::class, 'spam'])->name('spam');
    Route::post('/{comment}/restore', [CommentController::class, 'restore'])->name('restore');
    Route::post('/{comment}/reply', [CommentController::class, 'reply'])->name('reply');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
});

Route::get('media', [MediaController::class, 'index'])->name('media.index');
Route::get('media/list', [MediaController::class, 'list'])->name('media.list');
Route::post('media', [MediaController::class, 'store'])->name('media.store');
Route::post('media/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('media.bulkDestroy');
Route::get('media/{media}', [MediaController::class, 'show'])->name('media.show');
Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
Route::post('media/{media}/replace', [MediaController::class, 'replace'])->name('media.replace');
Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
// =====================================================================================================

Route::post('products/upload-image', [ProductController::class, 'uploadImage'])->name('products.upload-image');
Route::resource('products', ProductController::class);
Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle');


Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
Route::post('newsletter/{newsletter}/toggle', [NewsletterController::class, 'toggleStatus'])->name('newsletter.toggle');
Route::delete('newsletter/{newsletter}', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');
Route::get('newsletter/compose', [NewsletterController::class, 'compose'])->name('newsletter.compose');
Route::post('newsletter/send', [NewsletterController::class, 'send'])->name('newsletter.send');
Route::get('newsletter-export', [NewsletterController::class, 'export'])->name('newsletter.export');

// ---- سفارش‌ها / درخواست‌های مشاوره (فرم /order) ----
Route::get('orders-export', [OrderController::class, 'export'])->name('orders.export');
Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
Route::patch('orders/{order}/note', [OrderController::class, 'updateNote'])->name('orders.note');
Route::get('orders/{order}/attachment', [OrderController::class, 'attachment'])->name('orders.attachment');
Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

// ---- نقشه سایت ----
Route::post('sitemap/generate', [SitemapController::class, 'generate'])->name('sitemap.generate');

// ---- تنظیمات اطلاعات تماس شرکت — دسترسی: settings.contact (سوپرکاربر همیشه) ----
Route::middleware('can:settings.contact')->group(function () {
//     Route::get('settings/contact', [ContactSettingsController::class, 'edit'])->name('settings.contact');
//     Route::put('settings/contact', [ContactSettingsController::class, 'update'])->name('settings.contact.update');
//     Route::delete('settings/contact', [ContactSettingsController::class, 'reset'])->name('settings.contact.reset');
});

// ---- صفحات محصول (لندینگ کالریتو/مانیار/...) — دسترسی: product-pages.manage ----
Route::middleware('can:product-pages.manage')->group(function () {
    Route::post('product-pages/{product_page}/load-sample', [ProductPageController::class, 'loadSample'])->name('product-pages.load-sample');
    Route::post('product-pages/seed-defaults', [ProductPageController::class, 'seedDefaults'])->name('product-pages.seed');
    Route::post('product-pages/{product_page}/toggle', [ProductPageController::class, 'toggle'])->name('product-pages.toggle');
    Route::post('product-pages/{product_page}/duplicate', [ProductPageController::class, 'duplicate'])->name('product-pages.duplicate');
    Route::resource('product-pages', ProductPageController::class);
});

// ---- تیکتینگ (پشتیبانی) — دسترسی: tickets.manage ----
Route::prefix('tickets')->name('tickets.')->middleware('can:viewAny,App\Models\Ticket')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
    Route::post('/bulk', [\App\Http\Controllers\Admin\TicketController::class, 'bulk'])->name('bulk');

    Route::get('/departments', [\App\Http\Controllers\Admin\TicketDepartmentController::class, 'index'])->name('departments');
    Route::post('/departments', [\App\Http\Controllers\Admin\TicketDepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [\App\Http\Controllers\Admin\TicketDepartmentController::class, 'update'])->whereNumber('department')->name('departments.update');
    Route::delete('/departments/{department}', [\App\Http\Controllers\Admin\TicketDepartmentController::class, 'destroy'])->whereNumber('department')->name('departments.destroy');

    Route::get('/canned', [\App\Http\Controllers\Admin\TicketCannedResponseController::class, 'index'])->name('canned');
    Route::post('/canned', [\App\Http\Controllers\Admin\TicketCannedResponseController::class, 'store'])->name('canned.store');
    Route::put('/canned/{canned}', [\App\Http\Controllers\Admin\TicketCannedResponseController::class, 'update'])->whereNumber('canned')->name('canned.update');
    Route::delete('/canned/{canned}', [\App\Http\Controllers\Admin\TicketCannedResponseController::class, 'destroy'])->whereNumber('canned')->name('canned.destroy');

    Route::get('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->middleware('throttle:60,1')->name('reply');
    Route::patch('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'update'])->name('update');
    Route::delete('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'destroy'])->name('destroy');
    Route::get('/{ticket}/attachments/{attachment}', [\App\Http\Controllers\Admin\TicketController::class, 'attachment'])->whereNumber('attachment')->name('attachment');
});

// ---- برگردانده‌شده از بکاپ: اعلان‌ها و محصولات نرم‌افزاری ----
Route::prefix('notifications')->name('notifications.')->controller(\App\Http\Controllers\Admin\NotificationSettingsController::class)->group(function () {
    Route::get('/', 'history')->name('index');
    Route::get('/settings', 'index')->name('settings');
    Route::post('/settings', 'update')->name('update');
    Route::post('/test', 'test')->name('test');
    Route::post('/{id}/read', 'markRead')->name('read');
});

Route::get('software-products', [\App\Http\Controllers\Admin\SoftwareProductController::class, 'index'])->name('software-products.index');
Route::get('software-products/{softwareProduct}/edit', [\App\Http\Controllers\Admin\SoftwareProductController::class, 'edit'])->name('software-products.edit');
Route::put('software-products/{softwareProduct}', [\App\Http\Controllers\Admin\SoftwareProductController::class, 'update'])->name('software-products.update');

// ---- تنظیمات اطلاعات تماس شرکت (settings.contact) ----
Route::middleware('can:settings.contact')->group(function () {
    Route::get('settings/contact', [\App\Http\Controllers\Admin\ContactSettingsController::class, 'edit'])->name('settings.contact');
    Route::put('settings/contact', [\App\Http\Controllers\Admin\ContactSettingsController::class, 'update'])->name('settings.contact.update');
    Route::delete('settings/contact', [\App\Http\Controllers\Admin\ContactSettingsController::class, 'reset'])->name('settings.contact.reset');
});

// ---- صفحه‌ساز: صفحه‌ها، قالب‌ها و بلوک‌ها — دسترسی: pages.manage ----
Route::middleware('can:pages.manage')->group(function () {
    $pages = \App\Http\Controllers\Admin\PageController::class;
    $builder = \App\Http\Controllers\Admin\PageBuilderController::class;

    Route::get('pages', [$pages, 'index'])->name('pages.index');
    Route::post('pages', [$pages, 'store'])->name('pages.store');
    Route::get('pages/{page}/edit', [$pages, 'edit'])->whereNumber('page')->name('pages.edit');
    Route::put('pages/{page}', [$pages, 'update'])->whereNumber('page')->name('pages.update');
    Route::delete('pages/{page}', [$pages, 'destroy'])->whereNumber('page')->name('pages.destroy');
    Route::post('pages/{page}/duplicate', [$pages, 'duplicate'])->whereNumber('page')->name('pages.duplicate');
    Route::post('pages/{page}/template', [$pages, 'saveAsTemplate'])->whereNumber('page')->name('pages.template');
    Route::post('pages/{page}/publish', [$pages, 'togglePublish'])->whereNumber('page')->name('pages.publish');
    Route::post('pages/{page}/home', [$pages, 'setHome'])->whereNumber('page')->name('pages.home');
    Route::get('pages/{page}/preview', [$pages, 'preview'])->whereNumber('page')->name('pages.preview');

    Route::get('pages/{page}/builder', [$builder, 'show'])->whereNumber('page')->name('pages.builder');
    Route::post('pages/{page}/blocks', [$builder, 'store'])->whereNumber('page')->name('pages.blocks.store');
    Route::post('pages/{page}/blocks/sort', [$builder, 'sort'])->whereNumber('page')->name('pages.blocks.sort');
    Route::get('pages/{page}/blocks/{block}/edit', [$builder, 'edit'])->whereNumber(['page', 'block'])->name('pages.blocks.edit');
    Route::put('pages/{page}/blocks/{block}', [$builder, 'update'])->whereNumber(['page', 'block'])->name('pages.blocks.update');
    Route::post('pages/{page}/blocks/{block}/reset', [$builder, 'reset'])->whereNumber(['page', 'block'])->name('pages.blocks.reset');
    Route::patch('pages/{page}/blocks/{block}/toggle', [$builder, 'toggle'])->whereNumber(['page', 'block'])->name('pages.blocks.toggle');
    Route::post('pages/{page}/blocks/{block}/duplicate', [$builder, 'duplicate'])->whereNumber(['page', 'block'])->name('pages.blocks.duplicate');
    Route::delete('pages/{page}/blocks/{block}', [$builder, 'destroy'])->whereNumber(['page', 'block'])->name('pages.blocks.destroy');
});

// ---- فرم‌ساز — دسترسی: forms.manage (فرم‌ها) و forms.entries (ورودی‌ها) ----
Route::middleware('can:forms.manage')->group(function () {
    $forms = \App\Http\Controllers\Admin\FormController::class;
    Route::get('forms', [$forms, 'index'])->name('forms.index');
    Route::post('forms', [$forms, 'store'])->name('forms.store');
    Route::get('forms/{form}/edit', [$forms, 'edit'])->whereNumber('form')->name('forms.edit');
    Route::put('forms/{form}', [$forms, 'update'])->whereNumber('form')->name('forms.update');
    Route::post('forms/{form}/duplicate', [$forms, 'duplicate'])->whereNumber('form')->name('forms.duplicate');
    Route::get('forms/{form}/preview', [$forms, 'preview'])->whereNumber('form')->name('forms.preview');
    Route::delete('forms/{form}', [$forms, 'destroy'])->whereNumber('form')->name('forms.destroy');
});
Route::middleware('can:forms.entries')->group(function () {
    $entries = \App\Http\Controllers\Admin\FormEntryController::class;
    Route::get('form-entries', [$entries, 'index'])->name('form-entries.index');
    Route::get('form-entries/export', [$entries, 'export'])->name('form-entries.export');
    Route::post('form-entries/bulk', [$entries, 'bulk'])->name('form-entries.bulk');
    Route::get('form-entries/{entry}', [$entries, 'show'])->whereNumber('entry')->name('form-entries.show');
    Route::patch('form-entries/{entry}', [$entries, 'update'])->whereNumber('entry')->name('form-entries.update');
    Route::delete('form-entries/{entry}', [$entries, 'destroy'])->whereNumber('entry')->name('form-entries.destroy');
    Route::get('form-entries/{entry}/files/{key}', [$entries, 'file'])->whereNumber('entry')->where('key', '[a-z0-9_]+')->name('form-entries.file');
});
