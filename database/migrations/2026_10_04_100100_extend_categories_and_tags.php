<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** دسته‌بندی‌ها: ترتیب، تصویر، سئو — برچسب‌ها: توضیح. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }
            if (! Schema::hasColumn('categories', 'image')) {
                $table->string('image', 500)->nullable();
            }
            if (! Schema::hasColumn('categories', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }
            if (! Schema::hasColumn('categories', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
        });

        Schema::table('tags', function (Blueprint $table) {
            if (! Schema::hasColumn('tags', 'description')) {
                $table->text('description')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            foreach (['sort_order', 'image', 'meta_title', 'meta_description'] as $c) {
                if (Schema::hasColumn('categories', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
        Schema::table('tags', function (Blueprint $table) {
            if (Schema::hasColumn('tags', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
