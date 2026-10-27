<?php
/**
 * Aron Soft — one-time web installer for hosts WITHOUT terminal/SSH.
 *
 * HOW TO USE
 *  1) Edit the line below: put a long random secret in $TOKEN (at least 16 chars).
 *  2) Upload/move THIS file into public_html/  (next to index.php).
 *  3) Take a DB backup in phpMyAdmin first (Export).
 *  4) Open  https://YOUR-SITE/web-install.php?token=YOUR_SECRET            -> shows a checklist
 *     Then   https://YOUR-SITE/web-install.php?token=YOUR_SECRET&run=1       -> runs the update
 *     Optional: add  &admin=you@mail.com  to create/promote a superuser (password is shown once).
 *  5) The file DELETES ITSELF after a successful run. If it is still there, delete it manually.
 *
 * Requires: vendor/ and .env already on the host (it does NOT run composer and never touches .env).
 */
$TOKEN = 'CHANGE_ME';
// ---------------------------------------------------------------------------------------------
header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

if ($TOKEN === 'CHANGE_ME' || strlen($TOKEN) < 16) { http_response_code(403); exit("Edit \$TOKEN in this file first (min 16 characters).\n"); }
if (!hash_equals($TOKEN, (string)($_GET['token'] ?? ''))) { http_response_code(403); exit("Forbidden\n"); }

$pub  = __DIR__;                 // public_html
$root = dirname(__DIR__);        // project root
$say  = function ($m) { echo $m, "\n"; @flush(); };
set_time_limit(300);

$say('PHP '.PHP_VERSION.'   root: '.$root);
$errors = [];
if (version_compare(PHP_VERSION, '8.2.0', '<')) $errors[] = 'PHP >= 8.2 required (cPanel > MultiPHP Manager).';
foreach (['mbstring','openssl','pdo','fileinfo','tokenizer','xml','ctype','curl'] as $e) if (!extension_loaded($e)) $errors[] = "Missing PHP extension: $e";
if (!extension_loaded('pdo_mysql') && !extension_loaded('pdo_sqlite')) $errors[] = 'Missing pdo_mysql';
if (!is_file("$root/vendor/autoload.php")) $errors[] = 'vendor/ not found in project root (upload vendor/ built with: composer install --no-dev).';
if (!is_file("$root/.env")) $errors[] = '.env not found in project root (copy .env.example to .env and fill it).';
foreach (['storage','bootstrap/cache'] as $d) if (!is_writable("$root/$d")) $errors[] = "$d is not writable (set permission 755/775).";
if (!is_dir("$root/public")) $say('note: no project-root/public folder (assets already in public_html?)');
if ($errors) { foreach ($errors as $e) $say("[FAIL] $e"); exit("\nFix the items above and reload.\n"); }
$say('[ok] environment checks passed');

if (empty($_GET['run'])) { $say("\nReady. Add &run=1 to the URL to execute. (Make a DB backup first!)"); exit; }

// -------- run -----------------------------------------------------------------
$log = fn($m) => $say("[..] $m");

// 1) sync public/ -> public_html/ (keeps existing .htaccess)
if (is_dir("$root/public") && realpath("$root/public") !== realpath($pub)) {
    $n = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/public", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $f) {
        $rel = substr($f->getPathname(), strlen("$root/public") + 1);
        if ($rel === '.htaccess' || $rel === 'error_log' || $rel === 'hot' || str_starts_with($rel, 'storage')) continue;
        $dst = "$pub/$rel";
        if ($f->isDir()) { @mkdir($dst, 0755, true); continue; }
        @mkdir(dirname($dst), 0755, true);
        if (@copy($f->getPathname(), $dst)) $n++;
    }
    if (!is_file("$pub/.htaccess") && is_file("$root/public/.htaccess")) @copy("$root/public/.htaccess", "$pub/.htaccess");
    $say("[ok] copied $n files from public/ to public_html/");
}

// 2) boot Laravel
require "$root/vendor/autoload.php";
$app = require_once "$root/bootstrap/app.php";
$app->usePublicPath($pub);
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$run = function (string $cmd, array $args = []) use ($kernel, $say) {
    $code = $kernel->call($cmd, $args);
    $out = trim($kernel->output());
    $say(($code === 0 ? '[ok] ' : '[FAIL] ')."artisan $cmd".($out ? "\n      ".str_replace("\n", "\n      ", substr($out, -1200)) : ''));
    return $code === 0;
};

foreach (['storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs','storage/app/public','storage/backups'] as $d) @mkdir("$root/$d", 0775, true);

$run('config:clear'); $run('cache:clear');
if (!$run('migrate', ['--force' => true])) { $say("\nMigration failed — nothing else was changed. Restore your DB backup if needed."); exit(1); }

// storage link (symlink may be disabled; fall back to a real folder)
if (!file_exists("$pub/storage")) {
    if (!function_exists('symlink') || !@symlink("$root/storage/app/public", "$pub/storage")) { @mkdir("$pub/storage", 0755, true); $say('[warn] symlink not allowed: created real folder public_html/storage'); }
    else $say('[ok] storage symlink created');
}
@mkdir("$pub/uploads", 0755, true);

if (!empty($_GET['admin']) && filter_var($_GET['admin'], FILTER_VALIDATE_EMAIL)) $run('aron:make-admin', ['email' => $_GET['admin']]);

$run('optimize:clear'); $run('config:cache'); $run('view:cache'); $run('event:cache');
@$kernel->call('route:cache'); $say('[ok] route cache attempted');
$run('sitemap:generate', ['--output' => "$pub/sitemap.xml"]);

$say("\nDONE. Next: set the cron job (every minute):  php $root/artisan schedule:run");
$say('Then open /panel, go to Permissions and press "ساخت دسترسی‌های استاندارد" once.');
if (@unlink(__FILE__)) $say('[ok] this installer deleted itself.'); else $say('[warn] could not delete web-install.php — delete it NOW via File Manager.');
