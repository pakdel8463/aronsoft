<?php
/** Install Aron Soft product routes without overwriting existing project routes. */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);
$web = $root.'/routes/web.php';
$admin = $root.'/routes/admin.php';
$routes = [
    $web => [
        '// ARON SOFTWARE PRODUCTS ROUTES — installed automatically',
        "Route::get('/products', [\\App\\Http\\Controllers\\SoftwareProductController::class, 'index'])->name('software-products.index');",
        "Route::get('/products/{slug}', [\\App\\Http\\Controllers\\SoftwareProductController::class, 'show'])->name('software-products.show');",
    ],
    $admin => [
        '// ARON SOFTWARE PRODUCTS ADMIN ROUTES — installed automatically',
        "Route::get('software-products', [\\App\\Http\\Controllers\\Admin\\SoftwareProductController::class, 'index'])->name('software-products.index');",
        "Route::get('software-products/{softwareProduct}/edit', [\\App\\Http\\Controllers\\Admin\\SoftwareProductController::class, 'edit'])->name('software-products.edit');",
        "Route::put('software-products/{softwareProduct}', [\\App\\Http\\Controllers\\Admin\\SoftwareProductController::class, 'update'])->name('software-products.update');",
    ],
];
function fail(string $message): never { fwrite(STDERR, "ERROR: {$message}\n"); exit(1); }
function run(array $cmd, string $label): void {
    echo "\n== {$label} ==\n";
    $command = implode(' ', array_map('escapeshellarg', $cmd));
    passthru($command, $exit);
    if ($exit !== 0) fail("{$label} failed (exit {$exit})");
}
if (!is_file($root.'/artisan')) fail('Run this script within the Aron Laravel project.');
foreach (['app/Http/Controllers/SoftwareProductController.php', 'app/Http/Controllers/Admin/SoftwareProductController.php', 'app/Models/SoftwareProduct.php', 'resources/views/front/products/show.blade.php'] as $required) {
    if (!is_file($root.'/'.$required)) fail("Missing {$required}. Extract the product ZIP first.");
}
$stamp = date('Ymd-His');
foreach ($routes as $file => $lines) {
    if (!is_file($file) || !is_writable($file)) fail("Missing or non-writable route file: {$file}");
    $source = file_get_contents($file);
    if ($source === false) fail("Cannot read {$file}");
    $name = $file === $web ? 'public' : 'admin';
    if (str_contains($source, $lines[1])) { echo "{$name} routes already installed; skipping.\n"; continue; }
    if (preg_match("/->name\(['\"]software-products\.(index|show|edit|update)['\"]\)/", $source)) {
        fail("Existing software-products route names found in {$file}; please review to avoid duplicate routes.");
    }
    // Avoid accidental collisions with previously installed routes in another notation.
    if (str_contains($source, 'SoftwareProductController::class')) fail("Existing product controller routes in {$file}; skipping automatic modification for safety.");
    $backup = $file.'.backup-products-'.$stamp;
    if (!copy($file, $backup)) fail("Cannot back up {$file}");
    $updated = rtrim($source)."\n\n".implode("\n", $lines)."\n";
    if (file_put_contents($file, $updated, LOCK_EX) === false) { copy($backup,$file); fail("Cannot update {$file}"); }
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $output, $code);
    if ($code !== 0) { copy($backup, $file); fail("PHP syntax error; restored {$file}: ".implode(' ', $output)); }
    echo "Registered {$name} routes; backup: ".basename($backup)."\n";
}
run([PHP_BINARY, 'artisan', 'migrate', '--force'], 'Database migration');
$storageTarget = $root.'/storage/app/public';
if (!is_dir($storageTarget)) mkdir($storageTarget, 0775, true);
foreach (['public', 'public_html'] as $directory) {
    $publicRoot = $root.'/'.$directory;
    if (!is_dir($publicRoot)) continue;
    $link = $publicRoot.'/storage';
    if (is_link($link)) {
        echo "{$directory}/storage already symlinked to ".readlink($link)."\n";
    } elseif (file_exists($link)) {
        echo "WARNING: {$directory}/storage exists and was left unchanged. Check that uploads are reachable.\n";
    } elseif (@symlink($storageTarget, $link)) {
        echo "Created {$directory}/storage link.\n";
    } else {
        echo "WARNING: Could not create {$directory}/storage link. Check permissions.\n";
    }
}
run([PHP_BINARY, 'artisan', 'optimize:clear'], 'Clear Laravel caches');
run([PHP_BINARY, 'artisan', 'route:list', '--path=products'], 'Public product routes');
run([PHP_BINARY, 'artisan', 'route:list', '--path=software-products'], 'Admin product routes');
echo "\nINSTALL COMPLETE\nAdmin: /panel/software-products\nPublic: /products/attendance, /products/moneyiar, /products/calorieto\n";
echo "If PHP-FPM OPcache caches old PHP scripts, reload php8.3-fpm after checking its service configuration.\n";
