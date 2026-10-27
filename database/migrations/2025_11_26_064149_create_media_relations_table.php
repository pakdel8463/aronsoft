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
        Schema::create('media_relations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');

            // polymorphic relation
            $table->morphs('model');    // model_id + model_type

            // نقش فایل: cover, gallery, attachment
            $table->string('role')->nullable();

            $table->unsignedInteger('sort')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_relations');
    }
};
