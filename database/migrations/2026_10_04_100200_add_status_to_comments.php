<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** وضعیت دیدگاه: pending | approved | rejected | spam (is_approved همچنان برای فرانت همگام می‌ماند). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comments')) {
            return;
        }
        Schema::table('comments', function (Blueprint $table) {
            if (! Schema::hasColumn('comments', 'status')) {
                $table->string('status', 16)->default('pending');
            }
            if (! Schema::hasColumn('comments', 'user_agent')) {
                $table->string('user_agent', 255)->nullable();
            }
        });

        DB::table('comments')->where('is_approved', true)->update(['status' => 'approved']);
        DB::table('comments')->where('is_approved', false)->update(['status' => 'pending']);

        Schema::table('comments', function (Blueprint $table) {
            $table->index('status', 'comments_status_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('comments')) {
            return;
        }
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasIndex('comments', 'comments_status_index')) {
                $table->dropIndex('comments_status_index');
            }
        });
        Schema::table('comments', function (Blueprint $table) {
            foreach (['status', 'user_agent'] as $c) {
                if (Schema::hasColumn('comments', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
