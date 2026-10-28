<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** فرم‌ساز: فرم‌ها (فیلدها و تنظیمات به‌صورت JSON) و ورودی‌های ثبت‌شده. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('fields')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('form_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('data');
            $table->string('status', 20)->default('new');
            $table->boolean('is_starred')->default(false);
            $table->text('admin_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_url', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->index(['form_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        if (Schema::hasTable('permissions')) {
            foreach ([
                'forms.manage' => 'فرم‌ساز: ساخت و ویرایش فرم‌ها',
                'forms.entries' => 'فرم‌ساز: مشاهده و پیگیری ورودی‌ها',
            ] as $slug => $name) {
                DB::table('permissions')->updateOrInsert(['slug' => $slug], ['name' => $name, 'group' => 'forms', 'updated_at' => now(), 'created_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('form_entries');
        Schema::dropIfExists('forms');
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('slug', ['forms.manage', 'forms.entries'])->delete();
        }
    }
};
