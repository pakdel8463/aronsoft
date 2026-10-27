<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // اتصال به جدول کاربران (اگر ثبت نام کرده باشد)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // اطلاعات مشتری (چه عضو باشد چه نباشد، اینجا ذخیره می‌شود)
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();

            $table->string('project_title');
            $table->json('items');
            $table->decimal('total_amount', 15, 0);
            $table->enum('status', ['draft', 'sent', 'accepted', 'paid'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proformas');
    }
};
