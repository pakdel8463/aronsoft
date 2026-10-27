<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_canned_responses')) {
            return;
        }

        Schema::create('ticket_canned_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('body');
            $table->foreignId('department_id')->nullable()->constrained('ticket_departments')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['سلام و خوش‌آمد', "سلام و وقت بخیر،\nاز تماس شما سپاسگزاریم. درخواست شما در حال بررسی است و در اولین فرصت نتیجه را اطلاع می‌دهیم."],
            ['درخواست اطلاعات بیشتر', "سلام و وقت بخیر،\nبرای بررسی دقیق‌تر لطفاً توضیحات بیشتر (آدرس صفحه، تصویر خطا و مراحل تکرار مشکل) را ارسال کنید."],
            ['مشکل برطرف شد', "سلام،\nمشکل گزارش‌شده برطرف شد. لطفاً بررسی کنید و در صورت ادامهٔ مشکل همین تیکت را پاسخ دهید.\nموفق باشید."],
        ];
        foreach ($rows as [$title, $body]) {
            DB::table('ticket_canned_responses')->insert(['title' => $title, 'body' => $body, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_canned_responses');
    }
};
