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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            // اطلاعات کاربر
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();

            // محتوای پیام
            $table->string('subject')->nullable(); // موضوع پیام
            $table->text('message');

            // اطلاعات پروژه (هوشمند)
            $table->string('budget')->nullable();
            $table->string('preferred_contact_method')->nullable(); // call, whatsapp, email
            $table->string('preferred_contact_time')->nullable();

            // وضعیت پیگیری
            $table->enum('status', ['new', 'reviewed', 'contacted', 'closed'])
                ->default('new');

            // متادیتا (خیلی مهم)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('page_url')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
