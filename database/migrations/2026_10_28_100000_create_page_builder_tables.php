<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * صفحه‌ساز بلوکی: هر صفحه از چند بلوک ساخته می‌شود (اسکیما و پیش‌فرض‌ها در config/blocks.php).
 * صفحه‌هایی با is_template = true «قالب» هستند و صفحه‌ی جدید از روی آن‌ها ساخته می‌شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_home')->default(false);
            $table->boolean('is_template')->default(false);
            $table->string('description', 300)->nullable(); // توضیح قالب در پنل
            $table->json('settings')->nullable();           // سئو و تنظیمات نمایش
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_template', 'is_active']);
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('anchor', 80)->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['page_id', 'sort']);
        });

        // قالب‌های آماده (config/page_templates.php)؛ بعداً از پنل قابل ویرایش‌اند.
        \App\Pages\PageBuilder::seedTemplates();

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => 'pages.manage'],
                ['name' => 'صفحه‌ساز: مدیریت صفحه‌ها و قالب‌ها', 'group' => 'pages', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('pages');
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('slug', 'pages.manage')->delete();
        }
    }
};
