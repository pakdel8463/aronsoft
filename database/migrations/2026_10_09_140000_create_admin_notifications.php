<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('event', 60);
            $table->string('channel', 20);
            $table->string('recipient', 255);
            $table->string('status', 20)->default('queued');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('admin_notification_deliveries');
        Schema::dropIfExists('notifications');
    }
};
