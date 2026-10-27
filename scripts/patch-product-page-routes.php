<?php
/**
 * پچ امن و idempotent برای routes/admin.php: فقط دو روت جدید صفحه‌ی محصول را اضافه می‌کند (چیزی حذف یا بازنویسی نمی‌شود).
 * استفاده:  php scripts/patch-product-page-routes.php routes/admin.php
 */
$file = $argv[1] ?? 'routes/admin.php';
$src = file_get_contents($file);
if ($src === false) { fwrite(STDERR, "cannot read $file\n"); exit(1); }

$lines = [
    'product-pages.seed' => "    Route::post('product-pages/seed-defaults', [ProductPageController::class, 'seedDefaults'])->name('product-pages.seed');",
    'product-pages.load-sample' => "    Route::post('product-pages/{product_page}/load-sample', [ProductPageController::class, 'loadSample'])->name('product-pages.load-sample');",
];

$anchor = "Route::middleware('can:product-pages.manage')->group(function () {";
if (! str_contains($src, $anchor)) {
    // گروه وجود ندارد (نصب اولیه): کل گروه را اضافه کن
    $import = 'use App\Http\Controllers\Admin\ProductPageController;';
    if (! str_contains($src, $import)) {
        $src = preg_replace('/^(<\?php\s*\n)/', "$1\n$import\n", $src, 1);
    }
    $block = "\n// ---- صفحات محصول (لندینگ‌ها) — دسترسی: product-pages.manage ----\n$anchor\n"
        .$lines['product-pages.seed']."\n"
        ."    Route::post('product-pages/{product_page}/toggle', [ProductPageController::class, 'toggle'])->name('product-pages.toggle');\n"
        .$lines['product-pages.load-sample']."\n"
        ."    Route::post('product-pages/{product_page}/duplicate', [ProductPageController::class, 'duplicate'])->name('product-pages.duplicate');\n"
        ."    Route::resource('product-pages', ProductPageController::class);\n});\n";
    $src = rtrim($src)."\n".$block;
    echo "group added\n";
} else {
    foreach ($lines as $name => $line) {
        if (str_contains($src, "->name('".$name."')")) { echo "ok: $name already present\n"; continue; }
        $src = str_replace($anchor, $anchor."\n".$line, $src);
        echo "added: $name\n";
    }
}
if (! str_contains($src, 'ProductPageController') ) { fwrite(STDERR, "ProductPageController import missing\n"); exit(1); }
file_put_contents($file, $src);
