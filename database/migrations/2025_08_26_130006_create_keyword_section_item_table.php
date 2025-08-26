<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keyword_section_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->foreignId('section_item_id')->constrained('section_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['keyword_id', 'section_item_id']);
            $table->index(['section_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_section_item');
    }
};
