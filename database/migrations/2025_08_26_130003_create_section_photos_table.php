<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('section_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('path', 500)->comment('Caminho relativo público (ex.: sections/{sectionId}/fotos/<uuid>.jpg)');
            $table->boolean('principal')->default(false)->comment('Se true, deve ser única por seção');
            $table->integer('ordem')->default(0);
            $table->string('alt_text', 255)->nullable();
            $table->timestamps();

            $table->index(['section_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_photos');
    }
};
