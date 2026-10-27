<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** table => [index name => columns] */
    private function indexes(): array
    {
        return [
            'posts'            => ['posts_status_published_at_index' => ['status', 'published_at']],
            'portfolios'       => ['portfolios_status_created_at_index' => ['status', 'created_at']],
            'leads'            => ['leads_created_at_index' => ['created_at'], 'leads_is_contacted_index' => ['is_contacted']],
            'contact_messages' => ['contact_messages_status_created_at_index' => ['status', 'created_at']],
            'comments'         => ['comments_post_id_is_approved_index' => ['post_id', 'is_approved']],
            'products'         => ['products_is_active_type_index' => ['is_active', 'type']],
            'landing_pages'    => ['landing_pages_is_active_index' => ['is_active']],
            'proformas'        => ['proformas_created_at_index' => ['created_at']],
            'newsletters'      => ['newsletters_token_index' => ['token']],
        ];
    }

    public function up(): void
    {
        foreach ($this->indexes() as $table => $defs) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($defs as $name => $columns) {
                if (! Schema::hasColumns($table, $columns) || Schema::hasIndex($table, $name)) {
                    continue;
                }
                try {
                    Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
                } catch (\Throwable $e) {
                    // index creation is best-effort
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes() as $table => $defs) {
            foreach (array_keys($defs) as $name) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
