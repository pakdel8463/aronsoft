<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();          // ARN-XXXXXX
            $table->string('access_token', 40);            // با code برای صفحه پیگیری عمومی
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('lead_id')->nullable()->index();

            $table->string('type', 30)->index();           // website, shop, software, seo, devops, consult
            $table->string('budget', 30)->nullable();
            $table->string('deadline', 30)->nullable();
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();

            $table->string('name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->string('contact_time', 30)->nullable();
            $table->timestamp('consented_at')->nullable();

            $table->string('status', 20)->default('new')->index(); // new/contacted/quoted/won/lost
            $table->text('admin_note')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();

            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
