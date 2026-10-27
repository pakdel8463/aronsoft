<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('landing_pages',function(Blueprint $t){$t->foreignId('portfolio_id')->nullable()->constrained('portfolios')->nullOnDelete();$t->json('sections')->nullable();$t->json('draft_sections')->nullable();$t->json('appearance')->nullable();$t->timestamp('published_at')->nullable();}); }
 public function down(): void { Schema::table('landing_pages',function(Blueprint $t){$t->dropConstrainedForeignId('portfolio_id');$t->dropColumn(['sections','draft_sections','appearance','published_at']);}); }
};
