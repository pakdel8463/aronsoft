<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * کتابخانه رسانه: نسخه‌های تولیدشده (thumb/medium/large/webp)، آپلودکننده، پسوند و هش فایل.
 * ستون type همان enum قدیمی می‌ماند (image|video|audio|file) — «سند» = file.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'variants')) {
                $table->json('variants')->nullable();
            }
            if (! Schema::hasColumn('media', 'extension')) {
                $table->string('extension', 12)->nullable();
            }
            if (! Schema::hasColumn('media', 'uploaded_by')) {
                $table->unsignedBigInteger('uploaded_by')->nullable();
            }
            if (! Schema::hasColumn('media', 'hash')) {
                $table->string('hash', 64)->nullable();
            }
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index(['type', 'created_at'], 'media_type_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasIndex('media', 'media_type_created_at_index')) {
                $table->dropIndex('media_type_created_at_index');
            }
        });
        Schema::table('media', function (Blueprint $table) {
            foreach (['variants', 'extension', 'uploaded_by', 'hash'] as $c) {
                if (Schema::hasColumn('media', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
