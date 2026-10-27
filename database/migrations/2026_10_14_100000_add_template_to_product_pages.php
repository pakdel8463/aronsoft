<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_pages', 'template')) {
            Schema::table('product_pages', function (Blueprint $t) {
                $t->string('template', 40)->default('ink')->after('slug');
            });
        }
        // سه صفحه‌ی پیش‌فرض → قالب متناظر
        foreach (['humawatch' => 'ink', 'moneyiar' => 'violet', 'calorieto' => 'citrus'] as $slug => $tpl) {
            DB::table('product_pages')->where('slug', $slug)->update(['template' => $tpl]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_pages', 'template')) {
            Schema::table('product_pages', fn (Blueprint $t) => $t->dropColumn('template'));
        }
    }
};
