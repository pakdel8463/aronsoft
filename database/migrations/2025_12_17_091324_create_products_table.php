<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('title'); // نام محصول
                $table->string('slug')->unique(); // برای سئو و لینک‌ها
                $table->string('type'); // wordpress_theme, laravel_package, linux_script, etc.
                $table->text('short_description')->nullable(); // خلاصه برای کارت‌ها
                $table->longText('description'); // توضیحات کامل

                // بخش مالی و فایل
                $table->decimal('price', 15, 0)->default(0); // قیمت به تومان
                $table->string('featured_image')->nullable(); // تصویر اصلی
                $table->string('file_path')->nullable(); // مسیر فایل برای دانلود (در صورت فروش)
                $table->string('demo_link')->nullable(); // لینک دمو یا گیت‌هاب

                // وضعیت‌ها
                $table->boolean('is_for_sale')->default(false); // قابل خرید است یا فقط معرفی؟
                $table->boolean('is_active')->default(true); // نمایش داده شود یا خیر؟
                $table->integer('download_count')->default(0); // تعداد دانلود/فروش

                $table->string('seo_title')->nullable(); // عنوان سئو (اگر خالی بود از title استفاده شود)
                $table->text('seo_description')->nullable(); // توضیحات متا (Meta Description)
                $table->string('seo_keywords')->nullable(); // کلمات کلیدی (Meta Keywords)
                $table->string('canonical_url')->nullable();

                $table->string('version')->default('1.0.0'); // نسخه محصول
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
