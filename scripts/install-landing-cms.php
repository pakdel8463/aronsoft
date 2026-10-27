<?php
declare(strict_types=1);
$root=dirname(__DIR__); chdir($root);
function addRoutes(string $file,string $marker,string $text): void {
 $original=file_get_contents($file); if(str_contains($original,$marker)){echo "Already installed: $file\n";return;}
 $backup=$file.'.backup-landing-cms-'.date('Ymd-His'); if(!copy($file,$backup))throw new RuntimeException('Backup failed: '.$file);
 if(file_put_contents($file,"\n// $marker\n".$text."\n",FILE_APPEND)===false)throw new RuntimeException('Cannot update '.$file);
 echo "Updated $file (backup: $backup)\n";
}
$admin=<<<'ROUTES'
\Illuminate\Support\Facades\Route::post('landing/upload-image', [\App\Http\Controllers\Admin\LandingPageController::class, 'upload'])->name('landing.upload');
\Illuminate\Support\Facades\Route::get('landing/{landing}/preview', [\App\Http\Controllers\Admin\LandingPageController::class, 'preview'])->name('landing.preview');
\Illuminate\Support\Facades\Route::post('landing/{landing}/publish', [\App\Http\Controllers\Admin\LandingPageController::class, 'publish'])->name('landing.publish');
ROUTES;
$web=<<<'ROUTES'
\Illuminate\Support\Facades\Route::get('/products/attendance', fn()=>redirect('/services/attendance',302));
\Illuminate\Support\Facades\Route::get('/products/moneyiar', fn()=>redirect('/services/moneyiar',302));
\Illuminate\Support\Facades\Route::get('/products/calorieto', fn()=>redirect('/services/calorieto',302));
ROUTES;
// Product URLs may already be occupied by the previously installed independent system.
// Do not override them automatically: this transition needs explicit coordination.
addRoutes($root.'/routes/admin.php','ARON_LANDING_CMS_V1',$admin);
$run=function(string $cmd) use($root){echo "\n$cmd\n";passthru('cd '.escapeshellarg($root).' && '.$cmd,$exit);if($exit!==0)throw new RuntimeException("Command failed: $cmd");};
$run('php artisan migrate --force');$run('php scripts/seed-product-landings.php');$run('php artisan optimize:clear');$run('php artisan route:list --path=panel/landing');
echo "\nCMS INSTALLED. Existing /products routes untouched. Manage: /panel/landing\n";
