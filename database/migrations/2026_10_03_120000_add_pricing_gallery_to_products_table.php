<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'discount_price')) {
                $table->decimal('discount_price', 15, 0)->nullable()->after('price');
            }
            if (! Schema::hasColumn('products', 'gallery')) {
                $table->json('gallery')->nullable()->after('featured_image');
            }
            if (! Schema::hasColumn('products', 'download_url')) {
                $table->string('download_url', 500)->nullable()->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['discount_price', 'gallery', 'download_url'] as $c) {
                if (Schema::hasColumn('products', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
