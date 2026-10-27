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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();

            // آدرس URL
            $table->string('slug')->unique();

            // عنوان اصلی صفحه
            $table->string('title');

            // انتخاب تمپلیت لندینگ
            $table->string('template'); // مثل: webdev, wordpress, seo, devops

            // سئو
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            // فعال / غیرفعال بودن
            $table->boolean('is_active')->default(true);

            // ایجاد کننده لندینگ
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
