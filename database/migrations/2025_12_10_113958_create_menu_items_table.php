<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menu_items')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('display_title');
            $table->string('icon')->nullable();
            $table->boolean('status')->default(1);

            $table->string('lang')->nullable();

            // لینک دلخواه
            $table->string('url')->nullable();

            // اتصال به مدل داخلی (Page, Post, Category...)
            $table->string('model')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            // ترتیب و عمق
            $table->integer('sort')->default(0);
            $table->integer('depth')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
