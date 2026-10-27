<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * دسترسی «تنظیمات اطلاعات تماس» (settings.contact) — سوپرکاربرها همیشه دسترسی دارند،
 * برای سایر کارکنان از «نقش‌ها» یا «دسترسی‌ها» در پنل اعطا می‌شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'settings.contact'],
            ['name' => 'تنظیمات اطلاعات تماس', 'group' => 'settings', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('slug', 'settings.contact')->delete();
        }
    }
};
