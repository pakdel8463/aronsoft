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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // اطلاعات پایه
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();

            // نوع درخواست: مشاوره سئو، طراحی سایت، دواپس، تماس، لندینگ خاص...
            $table->string('request_type')->nullable();

            // پیام کاربر
            $table->text('message')->nullable();

            // صفحه‌ای که فرم در آن ارسال شده
            $table->string('page')->nullable();

            // IP کاربر
            $table->string('ip')->nullable();

            // اگر اپراتور با کاربر تماس گرفت
            $table->boolean('is_contacted')->default(false);

            // اگر نیاز به پیگیری دارد
            $table->boolean('needs_followup')->default(true);
            $table->text('referrer')->nullable()->comment('منبع ورود کاربر (UTM, لینک لندینگ, گوگل, تبلیغات)');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
