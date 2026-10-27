<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->string('number', 20)->unique();                       // TKT-100001
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('ticket_departments')->nullOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->string('subject', 190);
                $table->string('status', 20)->default('open')->index();       // open | waiting_admin | waiting_customer | closed
                $table->string('priority', 10)->default('normal')->index();   // low | normal | high | urgent
                $table->string('order_code', 30)->nullable()->index();        // orders.code (اختیاری)
                $table->boolean('client_unread')->default(false);             // پاسخ تازهٔ پشتیبان که مشتری ندیده
                $table->boolean('staff_unread')->default(true);               // پیام تازهٔ مشتری که پشتیبان ندیده
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('last_reply_at')->nullable()->index();
                $table->string('last_reply_by', 10)->nullable();              // customer | staff
                $table->timestamp('closed_at')->nullable();
                $table->unsignedTinyInteger('rating')->nullable();            // 1..5
                $table->string('rating_comment', 500)->nullable();
                $table->timestamp('rated_at')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();
                $table->index(['status', 'department_id']);
                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('ticket_messages')) {
            Schema::create('ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 10)->default('client');   // client | staff | note (note = یادداشت داخلی)
                $table->text('body');
                $table->timestamps();
                $table->index(['ticket_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('ticket_attachments')) {
            Schema::create('ticket_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ticket_message_id')->constrained('ticket_messages')->cascadeOnDelete();
                $table->string('original_name', 150);
                $table->string('path', 255);
                $table->string('mime', 100);
                $table->unsignedInteger('size');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket_events')) {
            Schema::create('ticket_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 20);     // created|replied|note|assigned|status|priority|department|rated|reopened
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['ticket_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_events');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
    }
};
