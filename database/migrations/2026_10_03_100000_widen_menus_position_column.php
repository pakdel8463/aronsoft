<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ستون position در جدول menus به‌صورت enum فقط سه مقدار (header/top/footer) را می‌پذیرفت،
 * در حالی‌که قالب فرانت برای فوتر از موقعیت‌های footer-col-1 و footer-col-2 استفاده می‌کند.
 * این مایگریشن ستون را به string تبدیل می‌کند (داده‌های موجود حفظ می‌شوند).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menus', 'position')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->string('position', 50)->default('header')->change();
        });
    }

    public function down(): void
    {
        // بازگرداندن به enum ممکن است روی داده‌های جدید خطا بدهد؛ عمداً بدون عملیات.
    }
};
