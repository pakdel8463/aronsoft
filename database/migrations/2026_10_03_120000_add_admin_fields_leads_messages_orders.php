<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // یادداشت داخلی ادمین روی لید
        if (Schema::hasTable('leads') && ! Schema::hasColumn('leads', 'admin_note')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->text('admin_note')->nullable();
            });
        }

        // علامت‌گذاری پیام تماس به‌عنوان اسپم (ستون status یک enum است و تغییرش در sqlite دردسر دارد)
        if (Schema::hasTable('contact_messages') && ! Schema::hasColumn('contact_messages', 'is_spam')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->boolean('is_spam')->default(false)->index();
            });
        }

        // تاریخچه (timeline) سفارش‌ها
        if (! Schema::hasTable('order_events')) {
            Schema::create('order_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 32);            // status | note
                $table->string('from', 64)->nullable();
                $table->string('to', 64)->nullable();
                $table->text('note')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
        if (Schema::hasColumn('contact_messages', 'is_spam')) {
            Schema::table('contact_messages', fn (Blueprint $t) => $t->dropColumn('is_spam'));
        }
        if (Schema::hasColumn('leads', 'admin_note')) {
            Schema::table('leads', fn (Blueprint $t) => $t->dropColumn('admin_note'));
        }
    }
};
