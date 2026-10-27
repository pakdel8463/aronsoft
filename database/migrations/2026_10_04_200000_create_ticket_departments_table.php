<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_departments')) {
            return;
        }

        Schema::create('ticket_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190)->nullable();       // اعلان تیکت‌های این دپارتمان
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        foreach (['پشتیبانی فنی', 'فروش و سفارش', 'مالی', 'پیشنهاد و انتقاد'] as $i => $name) {
            DB::table('ticket_departments')->insert([
                'name' => $name,
                'is_active' => true,
                'sort_order' => ($i + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_departments');
    }
};
