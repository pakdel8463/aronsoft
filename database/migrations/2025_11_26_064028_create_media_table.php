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
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->string('file_name');        // نام فایل ذخیره‌شده
            $table->string('original_name')->nullable(); // نام اصلی فایل
            $table->string('mime_type')->nullable();     // نوع فنی MIME
            $table->unsignedBigInteger('size')->nullable(); // سایز فایل (بایت)
            $table->enum('type', ['image','video','audio','file'])->default('image'); // نوع رسانه

            // اطلاعات مربوط به عکس
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // اطلاعات سئو / مدیریت
            $table->string('alt')->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();

            // مسیر — اگر خواستی برای مدل‌های مختلف جدا بشه
            $table->string('disk')->default('public');
            $table->string('path'); // media/2025/11/image.jpg

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
