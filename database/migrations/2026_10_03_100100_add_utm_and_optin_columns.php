<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leads: UTM capture
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $col) {
            if (! Schema::hasColumn('leads', $col)) {
                Schema::table('leads', fn (Blueprint $t) => $t->string($col, 100)->nullable());
            }
        }

        // Newsletter: double opt-in bookkeeping
        foreach (['confirmed_at', 'unsubscribed_at'] as $col) {
            if (! Schema::hasColumn('newsletters', $col)) {
                Schema::table('newsletters', fn (Blueprint $t) => $t->timestamp($col)->nullable());
            }
        }
        // Subscribers that were already activated before this migration count as confirmed.
        \DB::table('newsletters')->where('is_active', true)->whereNull('confirmed_at')
            ->update(['confirmed_at' => now()]);
    }

    public function down(): void
    {
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $col) {
            if (Schema::hasColumn('leads', $col)) {
                Schema::table('leads', fn (Blueprint $t) => $t->dropColumn($col));
            }
        }
        foreach (['confirmed_at', 'unsubscribed_at'] as $col) {
            if (Schema::hasColumn('newsletters', $col)) {
                Schema::table('newsletters', fn (Blueprint $t) => $t->dropColumn($col));
            }
        }
    }
};
