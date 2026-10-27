<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * صفحات لندینگ محصولات آرون سافت (کالریتو، مانیار، هوماواچ و ...) — قابل‌مدیریت از پنل.
 * محتوای بخش‌ها در JSON (ساختار: config/product_pages.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('name', 120);
            $table->string('name_en', 120)->nullable();
            $table->string('tagline', 220)->nullable();
            $table->string('category_label', 80)->nullable();
            $table->string('status', 16)->default('draft')->index();   // draft | published
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('accent', 9)->default('#2F4FF5');
            $table->string('accent_2', 9)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('icon', 40)->nullable();
            $table->string('card_image', 500)->nullable();
            $table->json('links')->nullable();
            $table->json('layout')->nullable();
            $table->json('sections')->nullable();
            $table->string('seo_title', 190)->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('seo_keywords', 300)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('og_image', 500)->nullable();
            $table->boolean('noindex')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => 'product-pages.manage'],
                ['name' => 'مدیریت صفحات محصول (لندینگ)', 'group' => 'content', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pages');
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('slug', 'product-pages.manage')->delete();
        }
    }
};
