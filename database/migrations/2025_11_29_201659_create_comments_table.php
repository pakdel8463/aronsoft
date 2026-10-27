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
        // NOTE: superseded by 2025_12_17_071705_create_comments_table (post_id/body schema). Kept as no-op to avoid duplicate-table errors on fresh installs.
        return;
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // متن کامنت
            $table->unsignedBigInteger('user_id')->nullable(); // آیدی کاربر
            $table->string('guest_name')->nullable(); // نام مهمان
            $table->string('guest_email')->nullable(); // ایمیل مهمان
            $table->morphs('commentable'); // برای رابطه چندشکلی
            $table->unsignedBigInteger('parent_id')->nullable(); // برای کامنت‌های پاسخ‌دهی
            $table->boolean('is_approved')->default(false); // وضعیت تأیید
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
