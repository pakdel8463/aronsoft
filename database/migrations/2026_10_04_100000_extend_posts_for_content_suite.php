<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ستون‌های جدید نوشته‌ها: خلاصه، canonical، تصویر OG، noindex و آخرین ویرایشگر + جدول نسخه‌های قبلی (revision-lite).
 * وضعیت «زمان‌بندی‌شده» ستون جدا ندارد: status=published و published_at در آینده (scope published آن را پنهان می‌کند).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'excerpt')) {
                $table->text('excerpt')->nullable();
            }
            if (! Schema::hasColumn('posts', 'canonical_url')) {
                $table->string('canonical_url', 500)->nullable();
            }
            if (! Schema::hasColumn('posts', 'og_image')) {
                $table->string('og_image', 500)->nullable();
            }
            if (! Schema::hasColumn('posts', 'noindex')) {
                $table->boolean('noindex')->default(false);
            }
            if (! Schema::hasColumn('posts', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
        });

        if (! Schema::hasTable('post_revisions')) {
            Schema::create('post_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('title');
                $table->longText('body')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['post_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
        Schema::table('posts', function (Blueprint $table) {
            foreach (['excerpt', 'canonical_url', 'og_image', 'noindex', 'updated_by'] as $c) {
                if (Schema::hasColumn('posts', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
